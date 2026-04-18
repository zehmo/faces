package com.faces.app.data;

import androidx.room.ColumnInfo;
import androidx.room.Entity;
import androidx.room.Index;
import androidx.room.PrimaryKey;

@Entity(tableName = "students",
        indices = {
            @Index(value = "reg_number", unique = true),
            @Index("server_id")
        })
public class StudentEntity {

    @PrimaryKey(autoGenerate = true)
    public int localId;

    @ColumnInfo(name = "server_id")
    public int serverId;

    @ColumnInfo(name = "reg_number")
    public String regNumber;

    @ColumnInfo(name = "jamb_reg_number")
    public String jambRegNumber;

    @ColumnInfo(name = "full_name")
    public String fullName;

    @ColumnInfo(name = "date_of_birth")
    public String dateOfBirth;

    public String sex;

    @ColumnInfo(name = "marital_status")
    public String maritalStatus;

    public String state;

    public String lga;

    public String town;

    @ColumnInfo(name = "phone_number")
    public String phoneNumber;

    public String email;

    public String department;

    public String level;

    @ColumnInfo(name = "photo_url")
    public String photoUrl;

    @ColumnInfo(name = "thumb_url")
    public String thumbUrl;

    @ColumnInfo(name = "photo_local_path")
    public String photoLocalPath;

    @ColumnInfo(name = "server_updated_at")
    public String serverUpdatedAt;
}
