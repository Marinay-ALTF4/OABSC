import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../widgets/stat_card.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';

class DoctorPatientRecordsView extends StatefulWidget {
  final VoidCallback onBack;

  const DoctorPatientRecordsView({super.key, required this.onBack});

  @override
  State<DoctorPatientRecordsView> createState() => _DoctorPatientRecordsViewState();
}

class _DoctorPatientRecordsViewState extends State<DoctorPatientRecordsView> {
  final ApiService _apiService = ApiService();
  final AuthService _authService = AuthService();
  final _searchController = TextEditingController();
  List<Map<String, dynamic>> _allPatients = [];
  List<Map<String, dynamic>> _filteredPatients = [];
  bool _isLoading = true;
  int _totalAppointments = 0;
  int _todayAppointments = 0;

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    setState(() => _isLoading = true);
    try {
      final userId = await _authService.getSavedUserId();
      
      // Fetch patients
      final patientResponse = await _apiService.get('patients');
      if (patientResponse['success'] == true || patientResponse['patients'] != null) {
        _allPatients = List<Map<String, dynamic>>.from(patientResponse['patients'] ?? []);
        _filteredPatients = _allPatients;
      }

      // Fetch dashboard stats for appointment counts
      final dashResponse = await _apiService.get('dashboard?user_id=$userId&role=doctor');
      if (dashResponse['success'] == true || dashResponse['total_consultations'] != null) {
        _totalAppointments = dashResponse['total_consultations'] ?? 0;
        _todayAppointments = dashResponse['today_patients'] ?? 0;
      }
    } catch (e) {
      debugPrint('Error fetching patient records: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _search() {
    final query = _searchController.text.toLowerCase();
    setState(() {
      _filteredPatients = _allPatients.where((p) {
        final name = (p['name'] ?? '').toLowerCase();
        final email = (p['email'] ?? '').toLowerCase();
        final phone = (p['phone'] ?? '').toLowerCase();
        return name.contains(query) || email.contains(query) || phone.contains(query);
      }).toList();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildHeader(),
          const SizedBox(height: AppSpacing.xl),
          _buildStatGrid(),
          const SizedBox(height: AppSpacing.xxl),
          _buildPatientListSection(),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Patient Records',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'View patients and the appointment history linked to your account.',
                style: TextStyle(
                  fontSize: 13,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ),
        OutlinedButton(
          onPressed: widget.onBack,
          style: OutlinedButton.styleFrom(
            side: const BorderSide(color: AppColors.border),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          ),
          child: const Text(
            'Back to Dashboard',
            style: TextStyle(fontSize: 11, color: AppColors.textPrimary, fontWeight: FontWeight.w600),
          ),
        ),
      ],
    );
  }

  Widget _buildStatGrid() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final cols = constraints.maxWidth > 600 ? 3 : 1;
        final w = (constraints.maxWidth - (cols - 1) * 12) / cols;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            SizedBox(
              width: w,
              child: StatCard(
                icon: Icons.people_outline_rounded,
                iconColor: const Color(0xFF3B82F6),
                iconBgColor: AppColors.iconBlueBg,
                count: _allPatients.length.toString(),
                label: 'PATIENTS',
              ),
            ),
            SizedBox(
              width: w,
              child: StatCard(
                icon: Icons.assignment_outlined,
                iconColor: const Color(0xFF0D9488),
                iconBgColor: const Color(0xFFF0FDFA),
                count: _totalAppointments.toString(),
                label: 'APPOINTMENTS',
              ),
            ),
            SizedBox(
              width: w,
              child: StatCard(
                icon: Icons.calendar_today_outlined,
                iconColor: const Color(0xFF10B981),
                iconBgColor: AppColors.iconGreenBg,
                count: _todayAppointments.toString(),
                label: 'TODAY',
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildPatientListSection() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: Wrap(
              alignment: WrapAlignment.spaceBetween,
              crossAxisAlignment: WrapCrossAlignment.start,
              spacing: AppSpacing.md,
              runSpacing: AppSpacing.md,
              children: [
                const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.folder_open_outlined, size: 20, color: AppColors.accentLight),
                    SizedBox(width: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Patient List',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
                        ),
                        Text(
                          'Select a patient to open their appointment history.',
                          style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                  ],
                ),
                SizedBox(
                        child: Column(
                          children: [
                            TextField(
                              controller: _searchController,
                              onChanged: (_) => _search(),
                              style: const TextStyle(fontSize: 13),
                              decoration: const InputDecoration(
                                hintText: 'Search name, email, or phone...',
                                contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                isDense: true,
                                fillColor: Color(0xFFF8FAFC),
                                prefixIcon: Icon(Icons.search, size: 16),
                              ),
                            ),
                          ],
                        ),
                ),
              ],
            ),
          ),
          
          // Table with horizontal scroll (Headers + Rows)
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: SizedBox(
              width: 850,
              child: Column(
                children: [
                  // Table Headers
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
                    color: AppColors.background.withOpacity(0.5),
                    child: const Row(
                      children: [
                        SizedBox(width: 40, child: Text('#', style: _tableHeaderStyle)),
                        SizedBox(width: 180, child: Text('PATIENT', style: _tableHeaderStyle)),
                        SizedBox(width: 200, child: Text('EMAIL', style: _tableHeaderStyle)),
                        SizedBox(width: 140, child: Text('PHONE', style: _tableHeaderStyle)),
                        SizedBox(width: 100, child: Text('APPOINTMENTS', style: _tableHeaderStyle)),
                        SizedBox(width: 140, child: Text('LATEST VISIT', style: _tableHeaderStyle)),
                      ],
                    ),
                  ),
                  
                  if (_isLoading)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 80),
                      child: Center(child: CircularProgressIndicator()),
                    )
                  else if (_filteredPatients.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 80),
                      child: Center(
                        child: Text(
                          'No patient records found.',
                          style: TextStyle(fontSize: 14, color: AppColors.textSecondary, fontWeight: FontWeight.w500),
                        ),
                      ),
                    )
                  else
                    Column(
                      children: List.generate(_filteredPatients.length, (index) {
                        final p = _filteredPatients[index];
                        return _buildPatientRow(index + 1, p);
                      }),
                    ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPatientRow(int index, Map<String, dynamic> patient) {
    return InkWell(
      onTap: () {
        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            title: Text(patient['name'] ?? 'Patient Details'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildDetailRow('Email', patient['email']),
                _buildDetailRow('Phone', patient['phone']),
                _buildDetailRow('City', patient['city']),
                _buildDetailRow('Address', patient['address']),
                _buildDetailRow('Registered', patient['created_at']),
              ],
            ),
            actions: [TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Close'))],
          ),
        );
      },
      child: Container(
        width: 850,
        padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: 12),
        decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: AppColors.border))),
        child: Row(
          children: [
            SizedBox(width: 40, child: Text(index.toString(), style: const TextStyle(fontSize: 12))),
            SizedBox(
              width: 180,
              child: Text(patient['name'] ?? '', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
            ),
            SizedBox(width: 200, child: Text(patient['email'] ?? '', style: const TextStyle(fontSize: 12))),
            SizedBox(width: 140, child: Text(patient['phone'] ?? '', style: const TextStyle(fontSize: 12))),
            const SizedBox(width: 100, child: Text('-', style: TextStyle(fontSize: 12))),
            SizedBox(width: 140, child: Text(patient['created_at']?.split(' ')[0] ?? '-', style: const TextStyle(fontSize: 12))),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, dynamic value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: RichText(
        text: TextSpan(
          style: const TextStyle(color: AppColors.textPrimary, fontSize: 13),
          children: [
            TextSpan(text: '$label: ', style: const TextStyle(fontWeight: FontWeight.bold)),
            TextSpan(text: value?.toString() ?? 'N/A'),
          ],
        ),
      ),
    );
  }

  static const _tableHeaderStyle = TextStyle(
    fontSize: 10,
    fontWeight: FontWeight.w700,
    color: AppColors.textSecondary,
    letterSpacing: 0.5,
  );
}
