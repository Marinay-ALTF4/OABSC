package com.example.oabsc_mobile.auth;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;
import android.widget.EditText;
import android.widget.ProgressBar;
import androidx.appcompat.app.AppCompatActivity;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import com.example.oabsc_mobile.R;
import com.example.oabsc_mobile.auth.dashboard.ClientDashboardActivity;
import com.example.oabsc_mobile.auth.dashboard.DoctorDashboardActivity;
import org.json.JSONObject;
import java.util.HashMap;
import java.util.Map;

public class LoginActivity extends AppCompatActivity {

    EditText etEmail, etPassword;
    Button btnLogin;
    TextView tvRegister;

    // 🔴 Replace with your PC's IPv4 address (run ipconfig in CMD)
    String BASE_URL = "http://192.168.1.145:8080/OABSC/api/login";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        etEmail    = findViewById(R.id.etEmail);
        etPassword = findViewById(R.id.etPassword);
        btnLogin   = findViewById(R.id.btnLogin);
        tvRegister = findViewById(R.id.tvRegister);

        btnLogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                String email    = etEmail.getText().toString().trim();
                String password = etPassword.getText().toString().trim();

                if (email.isEmpty() || password.isEmpty()) {
                    Toast.makeText(LoginActivity.this,
                            "Please fill in all fields", Toast.LENGTH_SHORT).show();
                } else {
                    loginUser(email, password);
                }
            }
        });

        tvRegister.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                startActivity(new Intent(LoginActivity.this, RegisterActivity.class));
            }
        });
    }

    private void loginUser(String email, String password) {

        btnLogin.setEnabled(false);
        btnLogin.setText("Signing in...");

        RequestQueue queue = Volley.newRequestQueue(this);

        StringRequest request = new StringRequest(Request.Method.POST, BASE_URL,
                new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        btnLogin.setEnabled(true);
                        btnLogin.setText("SIGN IN TO CLINIC PORTAL");

                        try {
                            JSONObject json = new JSONObject(response);
                            String status   = json.getString("status");

                            if (status.equals("success")) {
                                String role = json.getString("role");
                                String name = json.getString("name");

                                Toast.makeText(LoginActivity.this,
                                        "Welcome, " + name + "!", Toast.LENGTH_SHORT).show();

                                if (role.equals("client")) {
                                    Intent intent = new Intent(LoginActivity.this,
                                            ClientDashboardActivity.class);
                                    intent.putExtra("name", name);
                                    startActivity(intent);

                                } else if (role.equals("doctor")) {
                                    Intent intent = new Intent(LoginActivity.this,
                                            DoctorDashboardActivity.class);
                                    intent.putExtra("name", name);
                                    startActivity(intent);
                                }
                                finish();

                            } else {
                                String message = json.getString("message");
                                Toast.makeText(LoginActivity.this,
                                        message, Toast.LENGTH_SHORT).show();
                            }

                        } catch (Exception e) {
                            Toast.makeText(LoginActivity.this,
                                    "Error: " + e.getMessage(), Toast.LENGTH_LONG).show();
                        }
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        btnLogin.setEnabled(true);
                        btnLogin.setText("SIGN IN TO CLINIC PORTAL");
                        Toast.makeText(LoginActivity.this,
                                "Connection failed. Check your IP address.",
                                Toast.LENGTH_LONG).show();
                    }
                }) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("email",    email);
                params.put("password", password);
                return params;
            }
        };

        queue.add(request);
    }
}