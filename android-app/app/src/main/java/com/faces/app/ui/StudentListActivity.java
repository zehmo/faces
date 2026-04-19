package com.faces.app.ui;

import android.content.Intent;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;

import androidx.appcompat.app.AppCompatActivity;
import androidx.lifecycle.LiveData;
import androidx.recyclerview.widget.DividerItemDecoration;
import androidx.recyclerview.widget.LinearLayoutManager;

import com.faces.app.R;
import com.faces.app.data.AppDatabase;
import com.faces.app.data.StudentDao;
import com.faces.app.data.StudentEntity;
import com.faces.app.databinding.ActivityStudentListBinding;

import java.util.List;

public class StudentListActivity extends AppCompatActivity {

    private ActivityStudentListBinding binding;
    private StudentAdapter adapter;
    private String department;
    private String level;
    private StudentDao dao;
    private LiveData<List<StudentEntity>> currentLiveData;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityStudentListBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        department = getIntent().getStringExtra("department");
        level = getIntent().getStringExtra("level");

        if (department == null || level == null) {
            finish();
            return;
        }

        binding.toolbar.setTitle(department);
        binding.toolbar.setSubtitle(level + " Level");
        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        }
        binding.toolbar.setNavigationOnClickListener(v -> finish());

        dao = AppDatabase.getInstance(this).studentDao();

        setupRecyclerView();
        setupSearch();

        // Initial load — all students in dept+level
        observeStudents("");

        setupBottomNav();
    }

    private void setupBottomNav() {
        binding.bottomNav.setSelectedItemId(R.id.nav_home);
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

    private void setupRecyclerView() {
        adapter = new StudentAdapter(student -> {
            Intent intent = new Intent(this, StudentDetailActivity.class);
            intent.putExtra("localId", student.localId);
            intent.putExtra("department", department);
            intent.putExtra("level", level);
            startActivity(intent);
        });

        binding.recyclerView.setLayoutManager(new LinearLayoutManager(this));
        binding.recyclerView.addItemDecoration(
                new DividerItemDecoration(this, DividerItemDecoration.VERTICAL)
        );
        binding.recyclerView.setAdapter(adapter);
    }

    private void setupSearch() {
        binding.etSearch.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {}

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
                observeStudents(s.toString().trim());
            }

            @Override
            public void afterTextChanged(Editable s) {}
        });
    }

    private void observeStudents(String query) {
        // Remove previous observer
        if (currentLiveData != null) {
            currentLiveData.removeObservers(this);
        }

        if (query.isEmpty()) {
            currentLiveData = dao.getByDeptAndLevel(department, level);
        } else {
            currentLiveData = dao.searchByDeptAndLevel(department, level, query);
        }

        currentLiveData.observe(this, students -> {
            adapter.submitList(students);
            binding.tvEmpty.setVisibility(
                    students.isEmpty() ? View.VISIBLE : View.GONE
            );
        });
    }
}
