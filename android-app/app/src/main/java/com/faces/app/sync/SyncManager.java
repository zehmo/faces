package com.faces.app.sync;

import android.content.Context;
import android.util.Log;

import com.faces.app.api.ApiService;
import com.faces.app.api.models.SyncRecord;
import com.faces.app.api.models.SyncResponse;
import com.faces.app.auth.AuthManager;
import com.faces.app.data.AppDatabase;
import com.faces.app.data.StudentDao;
import com.faces.app.data.StudentEntity;
import com.faces.app.data.StudentFeeDao;
import com.faces.app.data.StudentFeeEntity;
import com.faces.app.data.SyncMeta;
import com.faces.app.data.SyncMetaDao;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.OutputStream;

import okhttp3.ResponseBody;
import retrofit2.Response;

public class SyncManager {

    private static final String TAG = "SyncManager";

    private final AppDatabase db;
    private final ApiService api;
    private final Context context;
    private final AuthManager authManager;

    public SyncManager(AppDatabase db, ApiService api, Context context, AuthManager authManager) {
        this.db = db;
        this.api = api;
        this.context = context;
        this.authManager = authManager;
    }

    public SyncResult sync() {
        StudentDao studentDao = db.studentDao();
        StudentFeeDao feeDao = db.studentFeeDao();
        SyncMetaDao syncMetaDao = db.syncMetaDao();

        // 1. Get last sync timestamp
        SyncMeta meta = syncMetaDao.get();
        String since = (meta != null && meta.lastSyncAt != null) ? meta.lastSyncAt : "";

        String bearer = authManager.getBearerToken();
        if (bearer == null) {
            return SyncResult.failure("Not logged in.");
        }

        // 2. Call API
        Response<SyncResponse> response;
        try {
            response = api.sync(bearer, since).execute();
        } catch (IOException e) {
            Log.e(TAG, "Network error during sync", e);
            return SyncResult.failure("Network error: " + e.getMessage());
        }

        if (!response.isSuccessful()) {
            if (response.code() == 401) {
                authManager.clearToken();
                return SyncResult.failure("Session expired. Please log in again.");
            }
            return SyncResult.failure("Server error: HTTP " + response.code());
        }

        SyncResponse body = response.body();
        if (body == null || body.students == null) {
            return SyncResult.failure("Empty response from server.");
        }

        if (body.students.isEmpty()) {
            // Still update the sync timestamp
            SyncMeta newMeta = new SyncMeta();
            newMeta.lastSyncAt = body.serverTime;
            syncMetaDao.save(newMeta);
            return SyncResult.noChanges();
        }

        // 3. Process each record
        int added = 0, updated = 0, deleted = 0;

        for (SyncRecord record : body.students) {
            try {
                if (record.deleted) {
                    // Soft-deleted on server → remove from device
                    studentDao.deleteByServerId(record.id);
                    feeDao.deleteByStudentId(record.id);
                    deleteLocalPhoto(record.id);
                    deleted++;
                } else {
                    boolean isNew = studentDao.findByServerId(record.id) == null;

                    StudentEntity entity = toEntity(record);

                    // Download photo if URL provided
                    if (record.photoUrl != null && !record.photoUrl.isEmpty()) {
                        String localPath = downloadPhoto(record.photoUrl, record.id, bearer);
                        if (localPath != null) {
                            entity.photoLocalPath = localPath;
                        }
                    }

                    studentDao.insertOrReplace(entity);

                    // Upsert fee records
                    feeDao.deleteByStudentId(record.id);
                    if (record.fees != null) {
                        for (SyncRecord.FeeRecord fee : record.fees) {
                            StudentFeeEntity feeEntity = new StudentFeeEntity();
                            feeEntity.serverStudentId = record.id;
                            feeEntity.session = fee.session;
                            feeEntity.schoolFeesPaid = fee.schoolFeesPaid;
                            feeEntity.schoolFeesDatePaid = fee.schoolFeesDatePaid;
                            feeEntity.departmentalDuesPaid = fee.departmentalDuesPaid;
                            feeEntity.facultyDuesPaid = fee.facultyDuesPaid;
                            feeDao.insertOrReplace(feeEntity);
                        }
                    }

                    if (isNew) added++;
                    else updated++;
                }
            } catch (Exception e) {
                Log.e(TAG, "Error processing record " + record.id, e);
                // Continue with remaining records
            }
        }

        // 4. Save new sync timestamp
        SyncMeta newMeta = new SyncMeta();
        newMeta.lastSyncAt = body.serverTime;
        syncMetaDao.save(newMeta);

        return SyncResult.success(added, updated, deleted);
    }

    private StudentEntity toEntity(SyncRecord record) {
        StudentEntity e = new StudentEntity();
        e.serverId = record.id;
        e.regNumber = record.regNumber;
        e.jambRegNumber = record.jambRegNumber;
        e.fullName = record.fullName;
        e.dateOfBirth = record.dateOfBirth;
        e.sex = record.sex;
        e.maritalStatus = record.maritalStatus;
        e.state = record.state;
        e.lga = record.lga;
        e.town = record.town;
        e.phoneNumber = record.phoneNumber;
        e.email = record.email;
        e.department = record.department;
        e.level = record.level;
        e.photoUrl = record.photoUrl;
        e.thumbUrl = record.thumbUrl;
        e.serverUpdatedAt = record.updatedAt;

        // Preserve existing local photo path if we already have it
        StudentEntity existing = db.studentDao().findByServerId(record.id);
        if (existing != null && existing.photoLocalPath != null) {
            e.photoLocalPath = existing.photoLocalPath;
            e.localId = existing.localId; // keep same local ID for Room REPLACE
        }

        return e;
    }

    private String downloadPhoto(String url, int serverId, String bearer) {
        try {
            Response<ResponseBody> res = api.downloadPhoto(url, bearer).execute();
            if (!res.isSuccessful() || res.body() == null) return null;

            File dir = new File(context.getFilesDir(), "photos");
            if (!dir.exists()) dir.mkdirs();

            File dest = new File(dir, "student_" + serverId + ".jpg");
            try (OutputStream out = new FileOutputStream(dest)) {
                out.write(res.body().bytes());
            }
            return dest.getAbsolutePath();
        } catch (IOException e) {
            Log.e(TAG, "Failed to download photo for student " + serverId, e);
            return null;
        }
    }

    private void deleteLocalPhoto(int serverId) {
        File photo = new File(context.getFilesDir(), "photos/student_" + serverId + ".jpg");
        if (photo.exists()) {
            photo.delete();
        }
    }
}
