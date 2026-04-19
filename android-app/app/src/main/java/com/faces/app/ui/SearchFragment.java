package com.faces.app.ui;

import android.content.Intent;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.lifecycle.LiveData;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.faces.app.R;
import com.faces.app.data.AppDatabase;
import com.faces.app.data.StudentDao;
import com.faces.app.data.StudentEntity;

import java.util.List;

public class SearchFragment extends Fragment {

    private EditText etSearch;
    private RecyclerView recyclerView;
    private TextView tvEmpty;
    private TextView tvResultCount;
    private StudentAdapter adapter;
    private StudentDao dao;
    private LiveData<List<StudentEntity>> currentLiveData;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_search, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        etSearch = view.findViewById(R.id.etSearch);
        recyclerView = view.findViewById(R.id.recyclerView);
        tvEmpty = view.findViewById(R.id.tvEmpty);
        tvResultCount = view.findViewById(R.id.tvResultCount);

        dao = AppDatabase.getInstance(requireContext()).studentDao();

        adapter = new StudentAdapter(student -> {
            Intent intent = new Intent(requireContext(), StudentDetailActivity.class);
            intent.putExtra("localId", student.localId);
            startActivity(intent);
        });

        recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        recyclerView.setAdapter(adapter);

        etSearch.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {}

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
                String query = s.toString().trim();
                if (query.length() >= 2) {
                    observeResults(query);
                } else {
                    clearResults();
                }
            }

            @Override
            public void afterTextChanged(Editable s) {}
        });
    }

    private void observeResults(String query) {
        if (currentLiveData != null) {
            currentLiveData.removeObservers(getViewLifecycleOwner());
        }

        currentLiveData = dao.search(query);
        currentLiveData.observe(getViewLifecycleOwner(), students -> {
            adapter.submitList(students);
            if (students.isEmpty()) {
                tvEmpty.setText("No students found");
                tvEmpty.setVisibility(View.VISIBLE);
                tvResultCount.setVisibility(View.GONE);
            } else {
                tvEmpty.setVisibility(View.GONE);
                tvResultCount.setText(students.size() + " results");
                tvResultCount.setVisibility(View.VISIBLE);
            }
            recyclerView.setVisibility(students.isEmpty() ? View.GONE : View.VISIBLE);
        });
    }

    private void clearResults() {
        if (currentLiveData != null) {
            currentLiveData.removeObservers(getViewLifecycleOwner());
            currentLiveData = null;
        }
        adapter.submitList(null);
        tvEmpty.setText("Search by name or reg number");
        tvEmpty.setVisibility(View.VISIBLE);
        tvResultCount.setVisibility(View.GONE);
        recyclerView.setVisibility(View.GONE);
    }
}
