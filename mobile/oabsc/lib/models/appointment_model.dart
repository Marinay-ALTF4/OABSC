/// Appointment model for the clinic appointment system
class AppointmentModel {
  final int? id;
  final String patientName;
  final String doctorName;
  final String date;
  final String time;
  final String status;
  final String? notes;

  AppointmentModel({
    this.id,
    required this.patientName,
    required this.doctorName,
    required this.date,
    required this.time,
    required this.status,
    this.notes,
  });

  factory AppointmentModel.fromJson(Map<String, dynamic> json) {
    return AppointmentModel(
      id: json['id'] as int?,
      patientName: json['patient_name'] as String? ?? '',
      doctorName: json['doctor_name'] as String? ?? '',
      date: json['date'] as String? ?? '',
      time: json['time'] as String? ?? '',
      status: json['status'] as String? ?? '',
      notes: json['notes'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'patient_name': patientName,
      'doctor_name': doctorName,
      'date': date,
      'time': time,
      'status': status,
      'notes': notes,
    };
  }
}
