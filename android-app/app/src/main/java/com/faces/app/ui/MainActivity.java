package com.faces.app.ui;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.viewpager2.widget.ViewPager2;

import com.faces.app.R;
import com.faces.app.api.ApiClient;
import com.faces.app.auth.AuthManager;
import com.faces.app.auth.LoginActivity;
import com.faces.app.data.AppDatabase;
import com.faces.app.databinding.ActivityMainBinding;
import com.faces.app.sync.SyncManager;
import com.faces.app.sync.SyncResult;

import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class MainActivity extends AppCompatActivity {

    private ActivityMainBinding binding;
    private AuthManager authManager;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityMainBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        try {
            authManager = new AuthManager(this);
        } catch (Exception e) {
            Toast.makeText(this, "Security init failed", Toast.LENGTH_SHORT).show();
            finish();
            return;
        }

        if (!authManager.isLoggedIn()) {
            navigateToLogin();
            return;
        }

        setupToolbar();
        setupViewPager();

        // Handle tab selection from other activities
        int tab = getIntent().getIntExtra("tab", 0);
        if (tab > 0) {
            binding.viewPager.setCurrentItem(tab, false);
        }

        // Auto-sync on launch
        triggerSync();
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        int tab = intent.getIntExtra("tab", 0);
        if (tab > 0 && binding != null) {
            binding.viewPager.setCurrentItem(tab, false);
        }
    }

    private void setupToolbar() {
        binding.toolbar.setTitle("FACES");
        binding.toolbar.setSubtitle("Welcome, " + authManager.getOfficerName());

        binding.toolbar.setOnMenuItemClickListener(item -> {
            int id = item.getItemId();
            if (id == R.id.action_sync) {
                triggerSync();
                return true;
            } else if (id == R.id.action_about) {
                showAboutDialog();
                return true;
            }
            return false;
        });
    }

    private void setupViewPager() {
        ViewPagerAdapter adapter = new ViewPagerAdapter(this);
        binding.viewPager.setAdapter(adapter);
        binding.viewPager.setOffscreenPageLimit(2);

        // Sync ViewPager with BottomNav
        binding.viewPager.registerOnPageChangeCallback(new ViewPager2.OnPageChangeCallback() {
            @Override
            public void onPageSelected(int position) {
                switch (position) {
                    case 0:
                        binding.bottomNav.setSelectedItemId(R.id.nav_home);
                        break;
                    case 1:
                        binding.bottomNav.setSelectedItemId(R.id.nav_search);
                        break;
                    case 2:
                        binding.bottomNav.setSelectedItemId(R.id.nav_profile);
                        break;
                }
            }
        });

        binding.bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();
            if (id == R.id.nav_home) {
                binding.viewPager.setCurrentItem(0, true);
                return true;
            } else if (id == R.id.nav_search) {
                binding.viewPager.setCurrentItem(1, true);
                return true;
            } else if (id == R.id.nav_profile) {
                binding.viewPager.setCurrentItem(2, true);
                return true;
            }
            return false;
        });
    }

    private void triggerSync() {
        executor.execute(() -> {
            SyncManager syncManager = new SyncManager(
                    AppDatabase.getInstance(this),
                    ApiClient.getInstance(),
                    this,
                    authManager
            );
            SyncResult result = syncManager.sync();

            runOnUiThread(() -> {
                if (!result.success && (result.message.contains("expired") || result.message.contains("log in"))) {
                    navigateToLogin();
                }
            });
        });
    }

    private void showAboutDialog() {
        new AlertDialog.Builder(this)
                .setTitle("FACES")
                .setMessage("Student Exam Verification System\nFaculty of Computing\n\nVersion 1.0")
                .setPositiveButton("OK", null)
                .show();
    }

    private void navigateToLogin() {
        Intent intent = new Intent(this, LoginActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        finish();
    }
}
