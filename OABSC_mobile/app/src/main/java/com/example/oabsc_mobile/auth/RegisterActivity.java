package com.example.oabsc_mobile.auth;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import com.example.oabsc_mobile.R;
import org.json.JSONObject;
import java.util.HashMap;
import java.util.Map;

public class RegisterActivity extends AppCompatActivity {

    EditText etFullName, etEmail, etPassword, etConfirmPassword;
    Button btnRegister;
    TextView tvLogin;

    // 🔴 Replace with your PC's IPv4 address (run ipconfig in CMD)
    String BASE_URL = "http://192.168.1.145:8080/OABSC/api/register";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        etFullName        = findViewById(R.id.etFullName);
        etEmail           = findViewById(R.id.etEmail);
        etPassword        = findViewById(R.id.etPassword);
        etConfirmPassword = findViewById(R.id.etConfirmPassword);
        btnRegister       = findViewById(R.id.btnRegister);
        tvLogin           = findViewById(R.id.tvLogin);

        btnRegister.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                String fullName       = etFullName.getText().toString().trim();
                String email          = etEmail.getText().toString().trim();
                String password       = etPassword.getText().toString().trim();
                String confirmPassword = etConfirmPassword.getText().toString().trim();

                if (fullName.isEmpty() || email.isEmpty() ||
                        password.isEmpty() || confirmPassword.isEmpty()) {
                    Toast.makeText(RegisterActivity.this,
                            "Please fill in all fields", Toast.LENGTH_SHORT).show();

                } else if (password.length() < 8) {
                    Toast.makeText(RegisterActivity.this,
                            "Password must be at least 8 characters", Toast.LENGTH_SHORT).show();

                } else if (!password.equals(confirmPassword)) {
                    Toast.makeText(RegisterActivity.this,
                            "Passwords do not match", Toast.LENGTH_SHORT).show();

                } else {
                    registerUser(fullName, email, password, confirmPassword);
                }
            }
        });

        tvLogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                startActivity(new Intent(RegisterActivity.this, LoginActivity.class));
                finish();
            }
        });
    }

    private void registerUser(String fullName, String email,
                              String password, String confirmPassword) {

        btnRegister.setEnabled(false);
        btnRegister.setText("Registering...");

        RequestQueue queue = Volley.newRequestQueue(this);

        StringRequest request = new StringRequest(Request.Method.POST, BASE_URL,
                new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        btnRegister.setEnabled(true);
                        btnRegister.setText("REGISTER FOR CLINIC PORTAL");

                        try {
                            JSONObject json = new JSONObject(response);
                            String status   = json.getString("status");

                            if (status.equals("success")) {
                                Toast.makeText(RegisterActivity.this,
                                        "Registration successful! Please login.",
                                        Toast.LENGTH_LONG).show();

                                // Go to Login after successful registration
                                startActivity(new Intent(RegisterActivity.this,
                                        LoginActivity.class));
                                finish();

                            } else {
                                String message = json.getString("message");
                                Toast.makeText(RegisterActivity.this,
                                        message, Toast.LENGTH_SHORT).show();
                            }

                        } catch (Exception e) {
                            Toast.makeText(RegisterActivity.this,
                                    "Error: " + e.getMessage(), Toast.LENGTH_LONG).show();
                        }
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        btnRegister.setEnabled(true);
                        btnRegister.setText("REGISTER FOR CLINIC PORTAL");
                        Toast.makeText(RegisterActivity.this,
                                "Connection failed. Check your IP address.",
                                Toast.LENGTH_LONG).show();
                    }
                }) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("name",             fullName);
                params.put("email",            email);
                params.put("password",         password);
                params.put("confirm_password", confirmPassword);
                return params;
            }
        };

        queue.add(request);
    }
}