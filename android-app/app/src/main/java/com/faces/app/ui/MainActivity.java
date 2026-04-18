package com.faces.app.ui;

import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.faces.app.R;
import com.faces.app.api.ApiClient;
import com.faces.app.auth.AuthManager;
import com.faces.app.auth.LoginActivity;
import com.faces.app.data.AppDatabase;
import com.faces.app.data.StudentDao;
import com.faces.app.data.SyncMeta;
import com.faces.app.databinding.ActivityMainBinding;
import com.faces.app.sync.SyncManager;
import com.faces.app.sync.SyncResult;

import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class MainActivity extends AppCompatActivity {

    private ActivityMainBinding binding;
    private AuthManager authManager;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    private DepartmentAdapter deptAdapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityMainBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        try {
            authManager = new AuthManager(this);
        } catch (Exception e) {
            Toast.makeText(this, "Security init failed", Toast.LENGTH_SHORT).show();
            finish();
            return;
        }

        if (!authManager.isLoggedIn()) {
            navigateToLogin();
            return;
        }

        setupToolbar();
        setupDepartmentGrid();
        setupSyncBar();

        // Auto-sync on launch
        triggerSync();
    }

    @Override
    protected void onResume() {
        super.onResume();
        loadDepartments();
    }

    private void setupToolbar() {
        binding.toolbar.setTitle("FACES");
        binding.toolbar.setSubtitle("Welcome, " + authManager.getOfficerName());
        setSupportActionBar(binding.toolbar);

        binding.btnLogout.setOnClickListener(v -> {
            authManager.clearToken();
            navigateToLogin();
        });
    }

    private void setupDepartmentGrid() {
        deptAdapter = new DepartmentAdapter(dept -> {
            Intent intent = new Intent(this, LevelSelectActivity.class);
            intent.putExtra("department", dept);
            startActivity(intent);
        });

        binding.recyclerView.setLayoutManager(new GridLayoutManager(this, 2));
        binding.recyclerView.setAdapter(deptAdapter);
    }

    private void loadDepartments() {
        StudentDao dao = AppDatabase.getInstance(this).studentDao();
        dao.getAllDepartments().observe(this, departments -> {
            if (departments == null || departments.isEmpty()) {
                binding.tvEmpty.setVisibility(View.VISIBLE);
                binding.tvEmpty.setText("No departments found. Tap Sync to download records.");
                deptAdapter.setData(new ArrayList<>());
            } else {
                binding.tvEmpty.setVisibility(View.GONE);
                // Load student counts in background
                executor.execute(() -> {
                    List<DepartmentItem> items = new ArrayList<>();
                    for (String dept : departments) {
                        int count = dao.getCountByDepartment(dept);
                        items.add(new DepartmentItem(dept, count));
                    }
                    runOnUiThread(() -> deptAdapter.setData(items));
                });
            }
        });
    }

    private void setupSyncBar() {
        binding.btnSync.setOnClickListener(v -> triggerSync());

        executor.execute(() -> {
            SyncMeta meta = AppDatabase.getInstance(this).syncMetaDao().get();
            String lastSync = (meta != null && meta.lastSyncAt != null)
                    ? meta.lastSyncAt : "Never";
            runOnUiThread(() -> binding.tvSyncStatus.setText("Last sync: " + lastSync));
        });
    }

    private void triggerSync() {
        binding.btnSync.setEnabled(false);
        binding.syncProgress.setVisibility(View.VISIBLE);

        executor.execute(() -> {
            SyncManager syncManager = new SyncManager(
                    AppDatabase.getInstance(this),
                    ApiClient.getInstance(),
                    this,
                    authManager
            );

            SyncResult result = syncManager.sync();

            runOnUiThread(() -> {
                binding.btnSync.setEnabled(true);
                binding.syncProgress.setVisibility(View.GONE);
                binding.tvSyncStatus.setText(result.message);

                if (!result.success && (result.message.contains("expired") || result.message.contains("log in"))) {
                    navigateToLogin();
                }
            });

            SyncMeta meta = AppDatabase.getInstance(this).syncMetaDao().get();
            if (meta != null && meta.lastSyncAt != null) {
                runOnUiThread(() ->
                        binding.tvSyncStatus.setText("Last sync: " + meta.lastSyncAt)
                );
            }
        });
    }

    private void navigateToLogin() {
        Intent intent = new Intent(this, LoginActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        finish();
    }

    // --- Inner classes ---

    static class DepartmentItem {
        final String name;
        final int studentCount;
        DepartmentItem(String name, int studentCount) {
            this.name = name;
            this.studentCount = studentCount;
        }
    }

    interface OnDeptClickListener {
        void onClick(String department);
    }

    static class DepartmentAdapter extends RecyclerView.Adapter<DepartmentAdapter.VH> {
        private List<DepartmentItem> items = new ArrayList<>();
        private final OnDeptClickListener listener;

        DepartmentAdapter(OnDeptClickListener listener) {
            this.listener = listener;
        }

        void setData(List<DepartmentItem> data) {
            this.items = data;
            notifyDataSetChanged();
        }

        @NonNull
        @Override
        public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext())
                    .inflate(R.layout.item_department, parent, false);
            return new VH(view);
        }

        @Override
        public void onBindViewHolder(@NonNull VH holder, int position) {
            DepartmentItem item = items.get(position);
            holder.tvDeptName.setText(item.name);
            holder.tvStudentCount.setText(item.studentCount + " students");
            holder.itemView.setOnClickListener(v -> listener.onClick(item.name));
        }

        @Override
        public int getItemCount() {
            return items.size();
        }

        static class VH extends RecyclerView.ViewHolder {
            final TextView tvDeptName, tvStudentCount;
            VH(@NonNull View itemView) {
                super(itemView);
                tvDeptName = itemView.findViewById(R.id.tvDeptName);
                tvStudentCount = itemView.findViewById(R.id.tvStudentCount);
            }
        }
    }
}
