package com.faces.app.ui;

import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import com.faces.app.R;
import com.faces.app.api.ApiClient;
import com.faces.app.auth.AuthManager;
import com.faces.app.auth.LoginActivity;
import com.faces.app.data.AppDatabase;
import com.faces.app.data.SyncMeta;
import com.faces.app.sync.SyncManager;
import com.faces.app.sync.SyncResult;
import com.google.android.material.button.MaterialButton;

import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class ProfileFragment extends Fragment {

    private TextView tvOfficerName;
    private TextView tvSyncStatus;
    private TextView tvStudentCount;
    private TextView tvSyncResult;
    private ProgressBar syncProgress;
    private MaterialButton btnSync;
    private MaterialButton btnLogout;
    private AuthManager authManager;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_profile, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        tvOfficerName = view.findViewById(R.id.tvOfficerName);
        tvSyncStatus = view.findViewById(R.id.tvSyncStatus);
        tvStudentCount = view.findViewById(R.id.tvStudentCount);
        tvSyncResult = view.findViewById(R.id.tvSyncResult);
        syncProgress = view.findViewById(R.id.syncProgress);
        btnSync = view.findViewById(R.id.btnSync);
        btnLogout = view.findViewById(R.id.btnLogout);

        try {
            authManager = new AuthManager(requireContext());
        } catch (Exception e) {
            return;
        }

        tvOfficerName.setText(authManager.getOfficerName());

        btnSync.setOnClickListener(v -> triggerSync());
        btnLogout.setOnClickListener(v -> logout());

        loadSyncInfo();
    }

    @Override
    public void onResume() {
        super.onResume();
        loadSyncInfo();
    }

    private void loadSyncInfo() {
        executor.execute(() -> {
            AppDatabase db = AppDatabase.getInstance(requireContext());
            SyncMeta meta = db.syncMetaDao().get();
            int count = db.studentDao().getCount();
            String lastSync = (meta != null && meta.lastSyncAt != null)
                    ? meta.lastSyncAt : "Never";

            if (isAdded()) {
                requireActivity().runOnUiThread(() -> {
                    tvSyncStatus.setText("Last sync: " + lastSync);
                    tvStudentCount.setText(count + " students synced");
                });
            }
        });
    }

    private void triggerSync() {
        btnSync.setEnabled(false);
        syncProgress.setVisibility(View.VISIBLE);
        tvSyncResult.setVisibility(View.GONE);

        executor.execute(() -> {
            SyncManager syncManager = new SyncManager(
                    AppDatabase.getInstance(requireContext()),
                    ApiClient.getInstance(),
                    requireContext(),
                    authManager
            );

            SyncResult result = syncManager.sync();

            if (isAdded()) {
                requireActivity().runOnUiThread(() -> {
                    btnSync.setEnabled(true);
                    syncProgress.setVisibility(View.GONE);
                    tvSyncResult.setText(result.message);
                    tvSyncResult.setVisibility(View.VISIBLE);

                    if (!result.success && (result.message.contains("expired") || result.message.contains("log in"))) {
                        logout();
                        return;
                    }

                    loadSyncInfo();
                });
            }
        });
    }

    private void logout() {
        if (authManager != null) {
            authManager.clearToken();
        }
        Intent intent = new Intent(requireContext(), LoginActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        requireActivity().finish();
    }
}
