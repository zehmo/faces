package com.faces.app.api.models;

import com.google.gson.annotations.SerializedName;

import java.util.List;

public class SyncRecord {
    public int id;

    @SerializedName("reg_number")
    public String regNumber;

    @SerializedName("jamb_reg_number")
    public String jambRegNumber;

    @SerializedName("full_name")
    public String fullName;

    @SerializedName("date_of_birth")
    public String dateOfBirth;

    public String sex;

    @SerializedName("marital_status")
    public String maritalStatus;

    public String state;
    public String lga;
    public String town;

    @SerializedName("phone_number")
    public String phoneNumber;

    public String email;
    public String department;
    public String level;

    @SerializedName("photo_url")
    public String photoUrl;

    @SerializedName("thumb_url")
    public String thumbUrl;

    public List<FeeRecord> fees;

    public boolean deleted;

    @SerializedName("updated_at")
    public String updatedAt;

    public static class FeeRecord {
        public String session;

        @SerializedName("school_fees_paid")
        public boolean schoolFeesPaid;

        @SerializedName("school_fees_date_paid")
        public String schoolFeesDatePaid;

        @SerializedName("departmental_dues_paid")
        public boolean departmentalDuesPaid;

        @SerializedName("faculty_dues_paid")
        public boolean facultyDuesPaid;
    }
}
