package com.faces.app.api.models;

import com.google.gson.annotations.SerializedName;

import java.util.List;

public class SyncResponse {
    public List<SyncRecord> students;

    @SerializedName("current_session")
    public String currentSession;

    @SerializedName("server_time")
    public String serverTime;
}
