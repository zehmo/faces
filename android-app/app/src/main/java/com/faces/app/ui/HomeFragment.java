package com.faces.app.ui;

import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.faces.app.R;
import com.faces.app.api.ApiClient;
import com.faces.app.auth.AuthManager;
import com.faces.app.data.AppDatabase;
import com.faces.app.data.StudentDao;
import com.faces.app.sync.SyncManager;
import com.faces.app.sync.SyncResult;

import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class HomeFragment extends Fragment {

    private RecyclerView recyclerView;
    private TextView tvEmpty;
    private androidx.swiperefreshlayout.widget.SwipeRefreshLayout swipeRefresh;
    private DepartmentAdapter deptAdapter;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_home, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        recyclerView = view.findViewById(R.id.recyclerView);
        tvEmpty = view.findViewById(R.id.tvEmpty);
        swipeRefresh = view.findViewById(R.id.swipeRefresh);

        setupDepartmentGrid();

        swipeRefresh.setColorSchemeResources(R.color.faces_green);
        swipeRefresh.setOnRefreshListener(this::triggerSync);
    }

    @Override
    public void onResume() {
        super.onResume();
        loadDepartments();
    }

    private void setupDepartmentGrid() {
        deptAdapter = new DepartmentAdapter(dept -> {
            Intent intent = new Intent(requireContext(), LevelSelectActivity.class);
            intent.putExtra("department", dept);
            startActivity(intent);
        });

        recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        recyclerView.setAdapter(deptAdapter);
    }

    private void loadDepartments() {
        StudentDao dao = AppDatabase.getInstance(requireContext()).studentDao();
        dao.getAllDepartments().observe(getViewLifecycleOwner(), departments -> {
            if (departments == null || departments.isEmpty()) {
                tvEmpty.setVisibility(View.VISIBLE);
                recyclerView.setVisibility(View.GONE);
                deptAdapter.setData(new ArrayList<>());
            } else {
                tvEmpty.setVisibility(View.GONE);
                recyclerView.setVisibility(View.VISIBLE);
                executor.execute(() -> {
                    List<DepartmentItem> items = new ArrayList<>();
                    for (String dept : departments) {
                        int count = dao.getCountByDepartment(dept);
                        items.add(new DepartmentItem(dept, count));
                    }
                    if (isAdded()) {
                        requireActivity().runOnUiThread(() -> deptAdapter.setData(items));
                    }
                });
            }
        });
    }

    private void triggerSync() {
        try {
            AuthManager authManager = new AuthManager(requireContext());
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
                        swipeRefresh.setRefreshing(false);
                        // Departments will auto-refresh via LiveData observer
                    });
                }
            });
        } catch (Exception e) {
            swipeRefresh.setRefreshing(false);
        }
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
            final ImageView imgIcon;
            VH(@NonNull View itemView) {
                super(itemView);
                tvDeptName = itemView.findViewById(R.id.tvDeptName);
                tvStudentCount = itemView.findViewById(R.id.tvStudentCount);
                imgIcon = itemView.findViewById(R.id.imgIcon);
            }
        }
    }
}
