import 'api_service.dart';

class DoctorService {
  final ApiService _apiService;

  DoctorService(this._apiService);

  // 1. Dashboard
  Future<Map<String, dynamic>> getDashboardStats(int doctorId) async {
    return await _apiService.get('doctor/$doctorId/dashboard');
  }

  // 2. Appointments
  Future<Map<String, dynamic>> getAppointments(int doctorId) async {
    return await _apiService.get('doctor/$doctorId/appointments');
  }

  Future<Map<String, dynamic>> updateAppointmentStatus(int appointmentId, String status) async {
    return await _apiService.post('doctor/appointments/$appointmentId/status', body: {'status': status});
  }

  // 3. Queue
  Future<Map<String, dynamic>> getQueue(int doctorId) async {
    return await _apiService.get('doctor/$doctorId/queue');
  }

  Future<Map<String, dynamic>> callNextPatient(int doctorId) async {
    return await _apiService.post('doctor/$doctorId/queue/call-next');
  }

  // 4. Patient Records
  Future<Map<String, dynamic>> getPatientRecords(int doctorId) async {
    return await _apiService.get('doctor/$doctorId/records');
  }

  // 5. Notes
  Future<Map<String, dynamic>> getNotes(int doctorId) async {
    return await _apiService.get('doctor/$doctorId/notes');
  }

  Future<Map<String, dynamic>> saveNote(int doctorId, Map<String, dynamic> noteData) async {
    return await _apiService.post('doctor/$doctorId/notes', body: noteData);
  }

  Future<Map<String, dynamic>> deleteNote(int doctorId, String noteId) async {
    return await _apiService.delete('doctor/$doctorId/notes/$noteId');
  }

  // 6. Prescriptions
  Future<Map<String, dynamic>> getPrescriptions(int doctorId) async {
    return await _apiService.get('doctor/$doctorId/prescriptions');
  }

  Future<Map<String, dynamic>> savePrescription(int doctorId, Map<String, dynamic> prescriptionData) async {
    return await _apiService.post('doctor/$doctorId/prescriptions', body: prescriptionData);
  }

  Future<Map<String, dynamic>> deletePrescription(int doctorId, String prescriptionId) async {
    return await _apiService.delete('doctor/$doctorId/prescriptions/$prescriptionId');
  }

  // 7. Schedule Settings
  Future<Map<String, dynamic>> getSchedule(int doctorId) async {
    return await _apiService.get('doctor/$doctorId/schedule');
  }

  Future<Map<String, dynamic>> saveSchedule(int doctorId, List<dynamic> schedule) async {
    return await _apiService.post('doctor/$doctorId/schedule/save', body: {'schedule': schedule});
  }
}
