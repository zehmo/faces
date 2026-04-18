package com.faces.app.ui;

import android.os.Bundle;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.bumptech.glide.Glide;
import com.faces.app.R;
import com.faces.app.data.AppDatabase;
import com.faces.app.data.StudentEntity;
import com.faces.app.data.StudentFeeEntity;
import com.faces.app.databinding.ActivityStudentDetailBinding;

import java.io.File;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class StudentDetailActivity extends AppCompatActivity {

    private ActivityStudentDetailBinding binding;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    private AppDatabase db;
    private StudentEntity currentStudent;
    private String filterDepartment;
    private String filterLevel;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityStudentDetailBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        binding.toolbar.setTitle("Student Details");
        binding.toolbar.setNavigationOnClickListener(v -> finish());
        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        }

        db = AppDatabase.getInstance(this);
        int localId = getIntent().getIntExtra("localId", -1);
        filterDepartment = getIntent().getStringExtra("department");
        filterLevel = getIntent().getStringExtra("level");

        if (localId == -1) {
            finish();
            return;
        }

        executor.execute(() -> {
            currentStudent = db.studentDao().findByLocalId(localId);
            if (currentStudent == null) {
                runOnUiThread(this::finish);
                return;
            }
            List<StudentFeeEntity> fees = db.studentFeeDao().getFeesForStudent(currentStudent.serverId);
            runOnUiThread(() -> displayStudent(currentStudent, fees));
        });

        binding.btnPrevious.setOnClickListener(v -> navigatePrevious());
        binding.btnNext.setOnClickListener(v -> navigateNext());
    }

    private void displayStudent(StudentEntity s, List<StudentFeeEntity> fees) {
        // Photo
        if (s.photoLocalPath != null && new File(s.photoLocalPath).exists()) {
            Glide.with(this)
                    .load(new File(s.photoLocalPath))
                    .placeholder(R.drawable.ic_person)
                    .error(R.drawable.ic_person)
                    .circleCrop()
                    .into(binding.imgPhoto);
        } else {
            binding.imgPhoto.setImageResource(R.drawable.ic_person);
        }

        // Basic info
        binding.tvRegNumber.setText(s.regNumber);
        binding.tvFullName.setText(s.fullName);
        setFieldOrHide(binding.tvJambReg, "JAMB Reg", s.jambRegNumber);
        setFieldOrHide(binding.tvDob, "Date of Birth", s.dateOfBirth);
        setFieldOrHide(binding.tvSex, "Sex", s.sex);
        setFieldOrHide(binding.tvMaritalStatus, "Marital Status", s.maritalStatus);
        setFieldOrHide(binding.tvState, "State", s.state);
        setFieldOrHide(binding.tvLga, "LGA", s.lga);
        setFieldOrHide(binding.tvTown, "Town", s.town);
        setFieldOrHide(binding.tvPhone, "Phone", s.phoneNumber);
        setFieldOrHide(binding.tvEmail, "Email", s.email);
        setFieldOrHide(binding.tvDepartment, "Department", s.department);
        binding.tvLevel.setText("Level: " + (s.level != null ? s.level + "L" : "—"));

        // Fee records
        binding.feesContainer.removeAllViews();
        if (fees != null && !fees.isEmpty()) {
            for (StudentFeeEntity fee : fees) {
                addFeeRow(fee);
            }
        } else {
            TextView tv = new TextView(this);
            tv.setText("No fee records available.");
            tv.setTextColor(getResources().getColor(android.R.color.darker_gray, null));
            tv.setPadding(0, 8, 0, 8);
            binding.feesContainer.addView(tv);
        }
    }

    private void setFieldOrHide(TextView tv, String label, String value) {
        if (value != null && !value.isEmpty()) {
            tv.setText(label + ": " + value);
            tv.setVisibility(View.VISIBLE);
        } else {
            tv.setVisibility(View.GONE);
        }
    }

    private void addFeeRow(StudentFeeEntity fee) {
        LinearLayout row = new LinearLayout(this);
        row.setOrientation(LinearLayout.VERTICAL);
        row.setPadding(0, 12, 0, 12);

        // Session header
        TextView sessionHeader = new TextView(this);
        sessionHeader.setText(fee.session);
        sessionHeader.setTextSize(14);
        sessionHeader.setTextColor(getResources().getColor(R.color.faces_green_dark, null));
        sessionHeader.setTypeface(null, android.graphics.Typeface.BOLD);
        row.addView(sessionHeader);

        // Fee badges
        addFeeBadge(row, "School Fees", fee.schoolFeesPaid);
        addFeeBadge(row, "Departmental Dues", fee.departmentalDuesPaid);
        addFeeBadge(row, "Faculty Dues", fee.facultyDuesPaid);

        binding.feesContainer.addView(row);

        // Divider
        View divider = new View(this);
        divider.setLayoutParams(new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT, 1));
        divider.setBackgroundColor(getResources().getColor(android.R.color.darker_gray, null));
        binding.feesContainer.addView(divider);
    }

    private void addFeeBadge(LinearLayout parent, String label, boolean paid) {
        TextView tv = new TextView(this);
        tv.setPadding(0, 4, 0, 4);
        tv.setTextSize(13);

        if (paid) {
            tv.setText("  ✓  " + label + ": PAID");
            tv.setTextColor(getResources().getColor(R.color.faces_green, null));
        } else {
            tv.setText("  ✗  " + label + ": NOT PAID");
            tv.setTextColor(getResources().getColor(android.R.color.holo_red_dark, null));
        }

        parent.addView(tv);
    }

    private void navigateNext() {
        if (currentStudent == null) return;
        executor.execute(() -> {
            StudentEntity next;
            if (filterDepartment != null && filterLevel != null) {
                next = db.studentDao().getNextInDeptLevel(filterDepartment, filterLevel, currentStudent.regNumber);
            } else {
                next = db.studentDao().getNext(currentStudent.regNumber);
            }
            if (next != null) {
                currentStudent = next;
                List<StudentFeeEntity> fees = db.studentFeeDao().getFeesForStudent(next.serverId);
                runOnUiThread(() -> displayStudent(next, fees));
            }
        });
    }

    private void navigatePrevious() {
        if (currentStudent == null) return;
        executor.execute(() -> {
            StudentEntity prev;
            if (filterDepartment != null && filterLevel != null) {
                prev = db.studentDao().getPreviousInDeptLevel(filterDepartment, filterLevel, currentStudent.regNumber);
            } else {
                prev = db.studentDao().getPrevious(currentStudent.regNumber);
            }
            if (prev != null) {
                currentStudent = prev;
                List<StudentFeeEntity> fees = db.studentFeeDao().getFeesForStudent(prev.serverId);
                runOnUiThread(() -> displayStudent(prev, fees));
            }
        });
    }
}
