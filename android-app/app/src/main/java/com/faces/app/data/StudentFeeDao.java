package com.faces.app.data;

import androidx.room.Dao;
import androidx.room.Insert;
import androidx.room.OnConflictStrategy;
import androidx.room.Query;

import java.util.List;

@Dao
public interface StudentFeeDao {

    @Query("SELECT * FROM student_fees WHERE server_student_id = :serverStudentId ORDER BY session DESC")
    List<StudentFeeEntity> getFeesForStudent(int serverStudentId);

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    void insertOrReplace(StudentFeeEntity fee);

    @Query("DELETE FROM student_fees WHERE server_student_id = :serverStudentId")
    void deleteByStudentId(int serverStudentId);
}
