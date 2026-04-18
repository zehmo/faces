package com.faces.app.api.models;

import com.google.gson.annotations.SerializedName;

public class LoginRequest {
    public String username;
    public String password;

    public LoginRequest(String username, String password) {
        this.username = username;
        this.password = password;
    }
}
