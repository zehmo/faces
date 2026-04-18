package com.faces.app.auth;

import android.content.Context;
import android.content.SharedPreferences;

import androidx.security.crypto.EncryptedSharedPreferences;
import androidx.security.crypto.MasterKey;

import java.io.IOException;
import java.security.GeneralSecurityException;

public class AuthManager {

    private static final String PREFS = "faces_secure_prefs";
    private static final String KEY_TOKEN = "jwt_token";
    private static final String KEY_OFFICER_NAME = "officer_name";
    private static final String KEY_OFFICER_USERNAME = "officer_username";

    private final SharedPreferences prefs;

    public AuthManager(Context ctx) throws GeneralSecurityException, IOException {
        MasterKey masterKey = new MasterKey.Builder(ctx)
                .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
                .build();

        prefs = EncryptedSharedPreferences.create(
                ctx, PREFS, masterKey,
                EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
                EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
        );
    }

    public void saveLogin(String token, String officerName, String officerUsername) {
        prefs.edit()
                .putString(KEY_TOKEN, token)
                .putString(KEY_OFFICER_NAME, officerName)
                .putString(KEY_OFFICER_USERNAME, officerUsername)
                .apply();
    }

    public String getToken() {
        return prefs.getString(KEY_TOKEN, null);
    }

    public String getBearerToken() {
        String token = getToken();
        return token != null ? "Bearer " + token : null;
    }

    public String getOfficerName() {
        return prefs.getString(KEY_OFFICER_NAME, "");
    }

    public boolean isLoggedIn() {
        return getToken() != null;
    }

    public void clearToken() {
        prefs.edit()
                .remove(KEY_TOKEN)
                .remove(KEY_OFFICER_NAME)
                .remove(KEY_OFFICER_USERNAME)
                .apply();
    }
}
