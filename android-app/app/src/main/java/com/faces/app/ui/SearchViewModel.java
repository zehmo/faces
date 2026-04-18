package com.faces.app.ui;

import android.app.Application;

import androidx.annotation.NonNull;
import androidx.lifecycle.AndroidViewModel;
import androidx.lifecycle.LiveData;
import androidx.lifecycle.MutableLiveData;
import androidx.lifecycle.Transformations;

import com.faces.app.data.AppDatabase;
import com.faces.app.data.StudentDao;
import com.faces.app.data.StudentEntity;

import java.util.List;

public class SearchViewModel extends AndroidViewModel {

    private final MutableLiveData<String> query = new MutableLiveData<>("");
    private final LiveData<List<StudentEntity>> results;

    public SearchViewModel(@NonNull Application app) {
        super(app);
        StudentDao dao = AppDatabase.getInstance(app).studentDao();
        results = Transformations.switchMap(query, q ->
                (q == null || q.isEmpty()) ? dao.search("") : dao.search(q)
        );
    }

    public void setQuery(String q) {
        query.setValue(q);
    }

    public LiveData<List<StudentEntity>> getResults() {
        return results;
    }
}
