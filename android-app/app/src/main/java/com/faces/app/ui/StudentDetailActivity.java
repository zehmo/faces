
package com.faces.app.ui;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;

import android.content.Intent;
import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.GestureDetector;
import android.view.MotionEvent;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.ContextCompat;

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
    private GestureDetector gestureDetector;

    @SuppressLint("ClickableViewAccessibility")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityStudentDetailBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        binding.toolbar.setTitle("Student Details");
        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        }
        binding.toolbar.setNavigationOnClickListener(v -> finish());

        db = AppDatabase.getInstance(this);
        int localId = getIntent().getIntExtra("localId", -1);
        filterDepartment = getIntent().getStringExtra("department");
        filterLevel = getIntent().getStringExtra("level");

        if (localId == -1) {
            finish();
            return;
        }

        // Swipe gesture detector
        gestureDetector = new GestureDetector(this, new GestureDetector.SimpleOnGestureListener() {
            private static final int SWIPE_THRESHOLD = 100;
            private static final int SWIPE_VELOCITY_THRESHOLD = 100;

            @Override
            public boolean onFling(MotionEvent e1, MotionEvent e2, float velocityX, float velocityY) {
                if (e1 == null || e2 == null) return false;
                float diffX = e2.getX() - e1.getX();
                float diffY = e2.getY() - e1.getY();
                if (Math.abs(diffX) > Math.abs(diffY)
                        && Math.abs(diffX) > SWIPE_THRESHOLD
                        && Math.abs(velocityX) > SWIPE_VELOCITY_THRESHOLD) {
                    if (diffX > 0) {
                        navigatePrevious();
                    } else {
                        navigateNext();
                    }
                    return true;
                }
                return false;
            }
        });

        binding.scrollView.setOnTouchListener((v, event) -> {
            gestureDetector.onTouchEvent(event);
            return false;
        });

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

        setupBottomNav();
    }

    private void setupBottomNav() {
        binding.bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();
            if (id == R.id.nav_home) {
                Intent intent = new Intent(this, MainActivity.class);
                intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_SINGLE_TOP);
                startActivity(intent);
                finish();
                return true;
            } else if (id == R.id.nav_search) {
                Intent intent = new Intent(this, MainActivity.class);
                intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_SINGLE_TOP);
                intent.putExtra("tab", 1);
                startActivity(intent);
                finish();
                return true;
            } else if (id == R.id.nav_profile) {
                Intent intent = new Intent(this, MainActivity.class);
                intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_SINGLE_TOP);
                intent.putExtra("tab", 2);
                startActivity(intent);
                finish();
                return true;
            }
            return false;
        });
    }

    private void displayStudent(StudentEntity s, List<StudentFeeEntity> fees) {
        // Photo
        if (s.photoLocalPath != null && new File(s.photoLocalPath).exists()) {
            Glide.with(this)
                    .load(new File(s.photoLocalPath))
                    .placeholder(R.drawable.ic_person)
                    .error(R.drawable.ic_person)
                    .centerCrop()
                    .into(binding.imgPhoto);
        } else {
            binding.imgPhoto.setImageResource(R.drawable.ic_person);
        }

        // Basic info
        binding.tvFullName.setText(s.fullName);
        binding.tvRegNumber.setText(s.regNumber);
        binding.tvLevel.setText(s.department + "  •  " + (s.level != null ? s.level + " Level" : "—"));

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

        // Fee records
        binding.feesContainer.removeAllViews();
        if (fees != null && !fees.isEmpty()) {
            for (StudentFeeEntity fee : fees) {
                addFeeRow(fee);
            }
        } else {
            TextView tv = new TextView(this);
            tv.setText("No fee records available.");
            tv.setTextColor(ContextCompat.getColor(this, R.color.md_theme_onSurfaceVariant));
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
            // Also hide the divider below this field
            if (tv.getParent() instanceof LinearLayout) {
                LinearLayout parent = (LinearLayout) tv.getParent();
                int idx = parent.indexOfChild(tv);
                if (idx >= 0 && idx + 1 < parent.getChildCount()) {
                    View next = parent.getChildAt(idx + 1);
                    if (next.getLayoutParams().height <= 2) {
                        next.setVisibility(View.GONE);
                    }
                }
            }
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
        sessionHeader.setTextColor(ContextCompat.getColor(this, R.color.md_theme_primary));
        sessionHeader.setTypeface(null, android.graphics.Typeface.BOLD);
        row.addView(sessionHeader);

        // School Fees chip with date if present
        addFeeChip(row, "School Fees", fee.schoolFeesPaid, fee.schoolFeesDatePaid);
        addFeeChip(row, "Dept. Dues", fee.departmentalDuesPaid, null);
        addFeeChip(row, "Faculty Dues", fee.facultyDuesPaid, null);

        binding.feesContainer.addView(row);

        // Divider
        View divider = new View(this);
        divider.setLayoutParams(new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT, 1));
        divider.setBackgroundColor(0xFFE0E0E0);
        binding.feesContainer.addView(divider);
    }

    private void addFeeChip(LinearLayout parent, String label, boolean paid, String date) {
        TextView tv = new TextView(this);
        tv.setPadding(0, 6, 0, 6);
        tv.setTextSize(13);

        String text;
        if (paid) {
            text = "  ✓  " + label + ": PAID";
            if (date != null && !date.isEmpty()) {
                String formatted = formatDateForDisplay(date);
                if (formatted != null) {
                    text += " (" + formatted + ")";
                }
            }
            tv.setTextColor(ContextCompat.getColor(this, R.color.paid_green));
        } else {
            text = "  ✗  " + label + ": NOT PAID";
            tv.setTextColor(ContextCompat.getColor(this, R.color.unpaid_red));
        }
        tv.setText(text);
        parent.addView(tv);
    }

    // Format date as 'August 9, 2026' from '2026-08-09' or similar
    private String formatDateForDisplay(String raw) {
        try {
            // Try ISO format first
            SimpleDateFormat iso = new SimpleDateFormat("yyyy-MM-dd");
            Date d = iso.parse(raw);
            SimpleDateFormat out = new SimpleDateFormat("MMMM d, yyyy");
            return out.format(d);
        } catch (ParseException e) {
            // Fallback: just return as is
            return raw;
        }
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
