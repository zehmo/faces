package com.faces.app.data;

import androidx.room.ColumnInfo;
import androidx.room.Entity;
import androidx.room.Index;
import androidx.room.PrimaryKey;

@Entity(tableName = "student_fees",
        indices = {
            @Index("server_student_id")
        })
public class StudentFeeEntity {

    @PrimaryKey(autoGenerate = true)
    public int localId;

    @ColumnInfo(name = "server_student_id")
    public int serverStudentId;

    public String session;

    @ColumnInfo(name = "school_fees_paid")
    public boolean schoolFeesPaid;

    @ColumnInfo(name = "school_fees_date_paid")
    public String schoolFeesDatePaid;

    @ColumnInfo(name = "departmental_dues_paid")
    public boolean departmentalDuesPaid;

    @ColumnInfo(name = "faculty_dues_paid")
    public boolean facultyDuesPaid;
}
