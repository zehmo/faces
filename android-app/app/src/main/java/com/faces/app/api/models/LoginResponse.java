package com.faces.app.api.models;

import com.google.gson.annotations.SerializedName;

public class LoginResponse {
    public String token;
    public Officer officer;

    public static class Officer {
        public int id;
        public String username;
        @SerializedName("full_name")
        public String fullName;
    }
}
