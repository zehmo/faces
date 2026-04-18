package com.faces.app.data;

import androidx.lifecycle.LiveData;
import androidx.room.Dao;
import androidx.room.Insert;
import androidx.room.OnConflictStrategy;
import androidx.room.Query;

import java.util.List;

@Dao
public interface StudentDao {

    @Query("SELECT * FROM students WHERE reg_number LIKE :query || '%' ORDER BY reg_number ASC")
    LiveData<List<StudentEntity>> searchByRegNumber(String query);

    @Query("SELECT * FROM students WHERE reg_number LIKE '%' || :query || '%' OR full_name LIKE '%' || :query || '%' ORDER BY reg_number ASC")
    LiveData<List<StudentEntity>> search(String query);

    @Query("SELECT * FROM students WHERE server_id = :serverId LIMIT 1")
    StudentEntity findByServerId(int serverId);

    @Query("SELECT * FROM students WHERE localId = :localId LIMIT 1")
    StudentEntity findByLocalId(int localId);

    @Query("SELECT * FROM students WHERE reg_number > :current ORDER BY reg_number ASC LIMIT 1")
    StudentEntity getNext(String current);

    @Query("SELECT * FROM students WHERE reg_number < :current ORDER BY reg_number DESC LIMIT 1")
    StudentEntity getPrevious(String current);

    @Query("SELECT COUNT(*) FROM students")
    int getCount();

    // Department + Level filtered queries
    @Query("SELECT DISTINCT department FROM students WHERE department IS NOT NULL ORDER BY department ASC")
    LiveData<List<String>> getAllDepartments();

    @Query("SELECT DISTINCT department FROM students WHERE department IS NOT NULL ORDER BY department ASC")
    List<String> getAllDepartmentsSync();

    @Query("SELECT COUNT(*) FROM students WHERE department = :dept")
    int getCountByDepartment(String dept);

    @Query("SELECT COUNT(*) FROM students WHERE department = :dept AND level = :level")
    int getCountByDepartmentAndLevel(String dept, String level);

    @Query("SELECT * FROM students WHERE department = :dept AND level = :level " +
           "AND (reg_number LIKE '%' || :query || '%' OR full_name LIKE '%' || :query || '%') " +
           "ORDER BY reg_number ASC")
    LiveData<List<StudentEntity>> searchByDeptAndLevel(String dept, String level, String query);

    @Query("SELECT * FROM students WHERE department = :dept AND level = :level " +
           "ORDER BY reg_number ASC")
    LiveData<List<StudentEntity>> getByDeptAndLevel(String dept, String level);

    @Query("SELECT * FROM students WHERE department = :dept AND level = :level " +
           "AND reg_number > :current ORDER BY reg_number ASC LIMIT 1")
    StudentEntity getNextInDeptLevel(String dept, String level, String current);

    @Query("SELECT * FROM students WHERE department = :dept AND level = :level " +
           "AND reg_number < :current ORDER BY reg_number DESC LIMIT 1")
    StudentEntity getPreviousInDeptLevel(String dept, String level, String current);

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    void insertOrReplace(StudentEntity student);

    @Query("DELETE FROM students WHERE server_id = :serverId")
    void deleteByServerId(int serverId);
}
