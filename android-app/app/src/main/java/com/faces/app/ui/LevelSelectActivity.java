package com.faces.app.ui;

import android.content.Intent;
import android.os.Bundle;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.faces.app.R;
import com.faces.app.data.AppDatabase;
import com.faces.app.data.StudentDao;
import com.faces.app.databinding.ActivityLevelSelectBinding;

import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class LevelSelectActivity extends AppCompatActivity {

    private ActivityLevelSelectBinding binding;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    private String department;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityLevelSelectBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        department = getIntent().getStringExtra("department");
        if (department == null) {
            finish();
            return;
        }

        binding.toolbar.setTitle(department);
        binding.toolbar.setSubtitle("Faculty of Computing");
        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        }
        binding.toolbar.setNavigationOnClickListener(v -> finish());

        binding.card100.setOnClickListener(v -> openStudentList("100"));
        binding.card200.setOnClickListener(v -> openStudentList("200"));
        binding.card300.setOnClickListener(v -> openStudentList("300"));
        binding.card400.setOnClickListener(v -> openStudentList("400"));
    }

    @Override
    protected void onResume() {
        super.onResume();
        loadCounts();
    }

    private void loadCounts() {
        StudentDao dao = AppDatabase.getInstance(this).studentDao();
        executor.execute(() -> {
            int c100 = dao.getCountByDepartmentAndLevel(department, "100");
            int c200 = dao.getCountByDepartmentAndLevel(department, "200");
            int c300 = dao.getCountByDepartmentAndLevel(department, "300");
            int c400 = dao.getCountByDepartmentAndLevel(department, "400");

            runOnUiThread(() -> {
                binding.tvCount100.setText(c100 + " students");
                binding.tvCount200.setText(c200 + " students");
                binding.tvCount300.setText(c300 + " students");
                binding.tvCount400.setText(c400 + " students");
            });
        });
    }

    private void openStudentList(String level) {
        Intent intent = new Intent(this, StudentListActivity.class);
        intent.putExtra("department", department);
        intent.putExtra("level", level);
        startActivity(intent);
    }
}
