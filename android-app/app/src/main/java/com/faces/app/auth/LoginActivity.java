package com.faces.app.auth;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;

import androidx.appcompat.app.AppCompatActivity;

import com.faces.app.api.ApiClient;
import com.faces.app.api.ApiService;
import com.faces.app.api.models.LoginRequest;
import com.faces.app.api.models.LoginResponse;
import com.faces.app.databinding.ActivityLoginBinding;
import com.faces.app.ui.MainActivity;

import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

import retrofit2.Call;
import retrofit2.Response;

public class LoginActivity extends AppCompatActivity {

    private ActivityLoginBinding binding;
    private AuthManager authManager;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityLoginBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        try {
            authManager = new AuthManager(this);
        } catch (Exception e) {
            binding.tvError.setText("Security initialization failed.");
            binding.tvError.setVisibility(View.VISIBLE);
            return;
        }

        // If already logged in, skip to main
        if (authManager.isLoggedIn()) {
            navigateToMain();
            return;
        }

        binding.btnLogin.setOnClickListener(v -> attemptLogin());
    }

    private void attemptLogin() {
        String username = binding.etUsername.getText().toString().trim();
        String password = binding.etPassword.getText().toString().trim();

        if (username.isEmpty() || password.isEmpty()) {
            showError("Please enter username and password.");
            return;
        }

        setLoading(true);
        binding.tvError.setVisibility(View.GONE);

        ApiService api = ApiClient.getInstance();
        LoginRequest request = new LoginRequest(username, password);

        executor.execute(() -> {
            try {
                Call<LoginResponse> call = api.login(request);
                Response<LoginResponse> response = call.execute();

                runOnUiThread(() -> {
                    setLoading(false);

                    if (response.isSuccessful() && response.body() != null) {
                        LoginResponse body = response.body();
                        authManager.saveLogin(
                                body.token,
                                body.officer.fullName,
                                body.officer.username
                        );
                        navigateToMain();
                    } else if (response.code() == 401) {
                        showError("Incorrect username or password.");
                    } else if (response.code() == 403) {
                        showError("Account is disabled. Contact admin.");
                    } else {
                        showError("Server error. Please try again.");
                    }
                });
            } catch (Exception e) {
                runOnUiThread(() -> {
                    setLoading(false);
                    showError("Could not reach server. Check your connection.");
                });
            }
        });
    }

    private void showError(String msg) {
        binding.tvError.setText(msg);
        binding.tvError.setVisibility(View.VISIBLE);
    }

    private void setLoading(boolean loading) {
        binding.progressBar.setVisibility(loading ? View.VISIBLE : View.GONE);
        binding.btnLogin.setEnabled(!loading);
        binding.etUsername.setEnabled(!loading);
        binding.etPassword.setEnabled(!loading);
    }

    private void navigateToMain() {
        Intent intent = new Intent(this, MainActivity.class);
        startActivity(intent);
        finish();
    }
}
