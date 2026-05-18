import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';

class PatientRecordsView extends StatefulWidget {
  const PatientRecordsView({super.key});

  @override
  State<PatientRecordsView> createState() => _PatientRecordsViewState();
}

class _PatientRecordsViewState extends State<PatientRecordsView> {
  final _api = ApiService();
  final _searchController = TextEditingController();
  bool _loading = true;
  List _patients = [];
  List _filteredPatients = [];

  @override
  void initState() {
    super.initState();
    _fetchPatients();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchPatients() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('patients');
      if (mounted && r['patients'] != null) {
        setState(() {
          _patients = List.from(r['patients']);
          _filteredPatients = List.from(_patients);
        });
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  void _filter(String q) {
    setState(() {
      if (q.trim().isEmpty) {
        _filteredPatients = List.from(_patients);
      } else {
        final query = q.toLowerCase();
        _filteredPatients = _patients.where((p) {
          final name = (p['name'] ?? '').toString().toLowerCase();
          final email = (p['email'] ?? '').toString().toLowerCase();
          return name.contains(query) || email.contains(query);
        }).toList();
      }
    });
  }

  String _fmtDate(String? d) {
    if (d == null || d.isEmpty) return '—';
    try {
      final dt = DateTime.parse(d);
      return DateFormat('MMM d, yyyy').format(dt);
    } catch (_) {
      return d;
    }
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header section
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE6F7EE),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.folder_open_outlined,
                      color: Color(0xFF166534),
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Text(
                      'Patient Records',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF166534),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.md),
              // Search Bar Row
              Row(
                children: [
                  Expanded(
                    child: Container(
                      height: 40,
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: TextField(
                        controller: _searchController,
                        onChanged: _filter,
                        decoration: const InputDecoration(
                          hintText: 'Search name or email...',
                          hintStyle: TextStyle(fontSize: 12),
                          contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          border: InputBorder.none,
                          enabledBorder: InputBorder.none,
                          focusedBorder: InputBorder.none,
                          fillColor: Colors.transparent,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: () => _filter(_searchController.text),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF166534),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      minimumSize: const Size(0, 40),
                    ),
                    child: const Text('Search', style: TextStyle(fontSize: 12, color: Colors.white)),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xl),

          if (_loading)
            const Center(child: Padding(padding: EdgeInsets.symmetric(vertical: 40), child: CircularProgressIndicator()))
          else ...[
            // Table container
            Container(
              width: double.infinity,
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.02),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                children: [
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Container(
                      width: 600,
                      decoration: const BoxDecoration(
                        borderRadius: BorderRadius.vertical(top: Radius.circular(12)),
                      ),
                      child: Column(
                        children: [
                          DataTable(
                            headingRowHeight: 50,
                            dataRowMaxHeight: 60,
                            headingRowColor: WidgetStateProperty.all(const Color(0xFFF0FDF4)),
                            headingTextStyle: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF166534),
                              letterSpacing: 1.0,
                            ),
                            columns: const [
                              DataColumn(label: Text('#')),
                              DataColumn(label: Text('NAME')),
                              DataColumn(label: Text('EMAIL')),
                              DataColumn(label: Text('PHONE')),
                              DataColumn(label: Text('REGISTERED')),
                            ],
                            rows: _filteredPatients.asMap().entries.map((e) {
                              final idx = e.key;
                              final p = e.value as Map;
                              return DataRow(cells: [
                                DataCell(Text('${idx + 1}', style: const TextStyle(fontWeight: FontWeight.w600))),
                                DataCell(Text((p['name'] ?? '—').toString())),
                                DataCell(Text((p['email'] ?? '—').toString())),
                                DataCell(Text((p['phone'] ?? '—').toString())),
                                DataCell(Text(_fmtDate(p['created_at']?.toString()))),
                              ]);
                            }).toList(),
                          ),
                          
                          if (_filteredPatients.isEmpty)
                            Container(
                              width: 600,
                              padding: const EdgeInsets.symmetric(vertical: 40),
                              child: const Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.folder_off_outlined, size: 40, color: Colors.grey),
                                  SizedBox(height: 12),
                                  Text(
                                    'No patient records found.',
                                    style: TextStyle(fontSize: 13, color: AppColors.textSecondary, fontWeight: FontWeight.w500),
                                  ),
                                ],
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}
