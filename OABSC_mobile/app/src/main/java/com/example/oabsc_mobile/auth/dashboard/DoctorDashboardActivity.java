package com.example.oabsc_mobile.auth.dashboard;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatActivity;
import com.example.oabsc_mobile.R;
import com.example.oabsc_mobile.auth.LoginActivity;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

public class DoctorDashboardActivity extends AppCompatActivity {

    TextView tvWelcome, tvDate;
    Button btnLogout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_doctor_dashboard);

        tvWelcome = findViewById(R.id.tvWelcome);
        tvDate    = findViewById(R.id.tvDate);
        btnLogout = findViewById(R.id.btnLogout);

        // Set today's date
        String date = new SimpleDateFormat("EEEE, MMMM d, yyyy",
                Locale.getDefault()).format(new Date());
        tvDate.setText("📅 " + date);

        // Logout
        btnLogout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(DoctorDashboardActivity.this,
                        LoginActivity.class);
                intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK |
                        Intent.FLAG_ACTIVITY_CLEAR_TASK);
                startActivity(intent);
            }
        });
    }
}