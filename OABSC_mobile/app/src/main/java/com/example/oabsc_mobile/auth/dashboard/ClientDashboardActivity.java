package com.example.oabsc_mobile.auth.dashboard;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import com.example.oabsc_mobile.R;
import com.example.oabsc_mobile.auth.LoginActivity;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

public class ClientDashboardActivity extends AppCompatActivity {

    TextView tvWelcome, tvDate;
    Button btnLogout, btnBookAppointment, btnViewAppointments;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_client_dashboard);

        tvWelcome           = findViewById(R.id.tvWelcome);
        tvDate              = findViewById(R.id.tvDate);
        btnLogout           = findViewById(R.id.btnLogout);
        btnBookAppointment  = findViewById(R.id.btnBookAppointment);
        btnViewAppointments = findViewById(R.id.btnViewAppointments);

        // Set today's date
        String date = new SimpleDateFormat("EEEE, MMMM d, yyyy",
                Locale.getDefault()).format(new Date());
        tvDate.setText("📅 " + date);

        // Book Appointment
        btnBookAppointment.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // TODO: navigate to BookAppointmentActivity
                Toast.makeText(ClientDashboardActivity.this,
                        "Book Appointment - Coming Soon!", Toast.LENGTH_SHORT).show();
            }
        });

        // View Appointments
        btnViewAppointments.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // TODO: navigate to MyAppointmentsActivity
                Toast.makeText(ClientDashboardActivity.this,
                        "My Appointments - Coming Soon!", Toast.LENGTH_SHORT).show();
            }
        });

        // Logout
        btnLogout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(ClientDashboardActivity.this,
                        LoginActivity.class);
                intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK |
                        Intent.FLAG_ACTIVITY_CLEAR_TASK);
                startActivity(intent);
            }
        });
    }
}