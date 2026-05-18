import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';

class RegisterPatientView extends StatefulWidget {
  const RegisterPatientView({super.key});

  @override
  State<RegisterPatientView> createState() => _RegisterPatientViewState();
}

class _RegisterPatientViewState extends State<RegisterPatientView> {
  final _api = ApiService();
  final _formKey = GlobalKey<FormState>();
  
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();

  bool _isLoading = false;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final response = await _api.post('admin/users/add', {
        'name': _nameController.text.trim(),
        'email': _emailController.text.trim().toLowerCase(),
        'phone': _phoneController.text.trim(),
        'role': 'client', // Automatically registered as a patient/client!
        'password': _passwordController.text,
        'password_confirm': _passwordController.text,
      });

      if (mounted) {
        if (response['success'] == true || response['user_id'] != null) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Patient registered successfully!'),
              backgroundColor: Color(0xFF059669),
            ),
          );
          // Clear inputs
          _nameController.clear();
          _emailController.clear();
          _phoneController.clear();
          _passwordController.clear();
        } else {
          final errorMsg = response['message'] ?? 'Failed to register patient';
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(errorMsg),
              backgroundColor: AppColors.error,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Connection error: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header section
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE6F7EE),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(
                    Icons.person_add_outlined,
                    color: Color(0xFF166534),
                    size: 20,
                  ),
                ),
                const SizedBox(width: 12),
                const Text(
                  'Register New Patient',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF166534),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.xxl),

            // Form Card
            Container(
              padding: const EdgeInsets.all(AppSpacing.xl),
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
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildLabel('FULL NAME'),
                  TextFormField(
                    controller: _nameController,
                    validator: (val) => val == null || val.trim().isEmpty ? 'Full name is required' : null,
                    decoration: const InputDecoration(
                      hintText: 'Enter full name',
                      fillColor: Color(0xFFF8FAFC),
                      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  _buildLabel('EMAIL ADDRESS'),
                  TextFormField(
                    controller: _emailController,
                    validator: (val) {
                      if (val == null || val.trim().isEmpty) return 'Email is required';
                      if (!RegExp(r'^[^@]+@[^@]+\.[^@]+$').hasMatch(val.trim())) return 'Invalid email format';
                      return null;
                    },
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(
                      hintText: 'Enter email address',
                      fillColor: Color(0xFFF8FAFC),
                      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  _buildLabel('PHONE NUMBER'),
                  TextFormField(
                    controller: _phoneController,
                    validator: (val) {
                      if (val == null || val.trim().isEmpty) return 'Phone number is required';
                      if (!RegExp(r'^(09|\+639)\d{9}$').hasMatch(val.trim())) return 'Use format: 09xxxxxxxxx';
                      return null;
                    },
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(
                      hintText: '09xxxxxxxxxx',
                      fillColor: Color(0xFFF8FAFC),
                      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  _buildLabel('TEMPORARY PASSWORD'),
                  TextFormField(
                    controller: _passwordController,
                    obscureText: true,
                    validator: (val) => val == null || val.length < 8 ? 'Password must be at least 8 characters' : null,
                    decoration: const InputDecoration(
                      hintText: 'Min. 8 characters',
                      fillColor: Color(0xFFF8FAFC),
                      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Register Button
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _isLoading ? null : _register,
                      icon: _isLoading
                          ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                          : const Icon(Icons.person_add_alt_1_rounded, size: 18, color: Colors.white),
                      label: Text(_isLoading ? 'Registering...' : 'Register Patient', style: const TextStyle(color: Colors.white)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF166534),
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLabel(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(
        text,
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          color: Color(0xFF4B5563),
          letterSpacing: 0.5,
        ),
      ),
    );
  }
}
