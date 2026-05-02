import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/auth_service.dart';
import '../../services/api_service.dart';

class ProfileSettingsView extends StatefulWidget {
  final VoidCallback onBack;

  const ProfileSettingsView({super.key, required this.onBack});

  @override
  State<ProfileSettingsView> createState() => _ProfileSettingsViewState();
}

class _ProfileSettingsViewState extends State<ProfileSettingsView> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final ApiService _apiService = ApiService();
  final AuthService _authService = AuthService();
  
  Map<String, dynamic> _userData = {};
  bool _isLoading = true;

  // Controllers for personal info
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _cityController = TextEditingController();
  final _addressController = TextEditingController();

  // Controllers for security
  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    setState(() => _isLoading = true);
    try {
      final userId = await _authService.getSavedUserId();
      if (userId == null) {
        setState(() => _isLoading = false);
        return;
      }

      final response = await _apiService.get('profile?user_id=$userId');
      if (response['success'] == true) {
        setState(() {
          _userData = response['user'] ?? {};
          _nameController.text = _userData['name'] ?? '';
          _emailController.text = _userData['email'] ?? '';
          _phoneController.text = _userData['phone'] ?? '';
          _cityController.text = _userData['city'] ?? '';
          _addressController.text = _userData['address'] ?? '';
          _isLoading = false;
        });
      }
    } catch (e) {
      debugPrint('Error loading profile: $e');
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _saveChanges() async {
    final userId = await _authService.getSavedUserId();
    if (userId == null) return;

    setState(() => _isLoading = true);
    try {
      final response = await _apiService.post('profile/update', body: {
        'user_id': userId,
        'name': _nameController.text.trim(),
        'phone': _phoneController.text.trim(),
        'city': _cityController.text.trim(),
        'address': _addressController.text.trim(),
      });

      if (mounted) {
        if (response['success'] == true) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Profile updated successfully')),
          );
          _loadProfile();
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(response['message'] ?? 'Failed to update profile')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  void dispose() {
    _tabController.dispose();
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _cityController.dispose();
    _addressController.dispose();
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isWide = constraints.maxWidth > 800;
        
        return SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.lg),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Responsive Header
              Wrap(
                alignment: WrapAlignment.spaceBetween,
                crossAxisAlignment: WrapCrossAlignment.center,
                spacing: AppSpacing.md,
                runSpacing: AppSpacing.md,
                children: [
                  ConstrainedBox(
                    constraints: BoxConstraints(maxWidth: constraints.maxWidth * 0.7),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Profile Settings',
                          style: TextStyle(
                            fontSize: 22,
                            fontWeight: FontWeight.w700,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Manage your personal information and account security.',
                          style: TextStyle(
                            fontSize: 13,
                            color: AppColors.textSecondary.withValues(alpha: 0.8),
                          ),
                        ),
                      ],
                    ),
                  ),
                  OutlinedButton.icon(
                    onPressed: widget.onBack,
                    icon: const Icon(Icons.arrow_back, size: 16),
                    label: const Text('Dashboard'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.textPrimary,
                      side: const BorderSide(color: AppColors.border),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Main Content Layout
              isWide 
                ? Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(width: 280, child: _buildUserCard()),
                      const SizedBox(width: AppSpacing.xl),
                      Expanded(child: _buildSettingsContent()),
                    ],
                  )
                : Column(
                    children: [
                      _buildUserCard(),
                      const SizedBox(height: AppSpacing.xl),
                      _buildSettingsContent(),
                    ],
                  ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildUserCard() {
    final initials = _userData['name'] != null 
        ? _userData['name'].split(' ').take(2).map((s) => s[0]).join('').toUpperCase()
        : 'C';

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 24),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
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
          CircleAvatar(
            radius: 40,
            backgroundColor: const Color(0xFF3B82F6),
            child: Text(
              initials,
              style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.w700,
                color: Colors.white,
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text(
            _userData['name'] ?? 'Client',
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            (_userData['role'] ?? 'CLIENT').toString().toUpperCase(),
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: Color(0xFF3B82F6),
              letterSpacing: 1.0,
            ),
          ),
          const SizedBox(height: 24),
          const Divider(color: AppColors.border),
          const SizedBox(height: 24),
          _buildInfoRow(Icons.email_outlined, _userData['email'] ?? '—'),
          const SizedBox(height: 12),
          _buildInfoRow(Icons.phone_outlined, _userData['phone'] ?? '—'),
          const SizedBox(height: 12),
          _buildInfoRow(Icons.location_on_outlined, _userData['city'] ?? '—'),
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String text) {
    return Row(
      children: [
        Icon(icon, size: 18, color: AppColors.textSecondary),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(
              fontSize: 13,
              color: AppColors.textSecondary,
            ),
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }

  Widget _buildSettingsContent() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        children: [
          TabBar(
            controller: _tabController,
            isScrollable: true,
            tabAlignment: TabAlignment.start,
            labelColor: AppColors.primary,
            unselectedLabelColor: AppColors.textSecondary,
            indicatorColor: AppColors.primary,
            indicatorWeight: 3,
            labelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
            tabs: const [
              Tab(icon: Icon(Icons.person_outline, size: 20), text: 'Personal Info'),
              Tab(icon: Icon(Icons.security_outlined, size: 20), text: 'Security'),
              Tab(icon: Icon(Icons.language_outlined, size: 20), text: 'Language'),
              Tab(icon: Icon(Icons.history_outlined, size: 20), text: 'Activity History'),
            ],
          ),
          SizedBox(
            height: 500, // Fixed height for tab content
            child: TabBarView(
              controller: _tabController,
              children: [
                _buildPersonalInfoTab(),
                _buildSecurityTab(),
                _buildLanguageTab(),
                _buildHistoryTab(),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPersonalInfoTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Personal Information',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 4),
          const Text(
            'Update your name, contact details, and address.',
            style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
          ),
          const SizedBox(height: 24),
          _buildTextField('Full Name', _nameController, Icons.person_outline),
          const SizedBox(height: 16),
          _buildTextField('Email Address', _emailController, Icons.email_outlined, enabled: false),
          const Text('Email cannot be changed.', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(child: _buildTextField('Phone Number', _phoneController, Icons.phone_outlined)),
              const SizedBox(width: 16),
              Expanded(child: _buildTextField('City / Municipality', _cityController, Icons.location_city_outlined)),
            ],
          ),
          const SizedBox(height: 16),
          _buildTextField('Home Address', _addressController, Icons.home_outlined),
          const SizedBox(height: 32),
          ElevatedButton(
            onPressed: _isLoading ? null : _saveChanges,
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
            ),
            child: _isLoading 
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Text('Save Changes'),
          ),
        ],
      ),
    );
  }

  Widget _buildSecurityTab() {
    return Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Change Password', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          const Text('Keep your account secure by using a strong password.', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          const SizedBox(height: 24),
          _buildTextField('Current Password', _currentPasswordController, Icons.lock_outline, obscureText: true),
          const SizedBox(height: 16),
          _buildTextField('New Password', _newPasswordController, Icons.lock_outline, obscureText: true),
          const SizedBox(height: 16),
          _buildTextField('Confirm New Password', _confirmPasswordController, Icons.lock_outline, obscureText: true),
          const SizedBox(height: 32),
          ElevatedButton(
            onPressed: () {},
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
            ),
            child: const Text('Update Password'),
          ),
        ],
      ),
    );
  }

  Widget _buildLanguageTab() {
    return Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Language Preference', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
          const SizedBox(height: 24),
          _buildLanguageOption('English', 'Use the portal in English', 'US', true),
          const SizedBox(height: 12),
          _buildLanguageOption('Filipino', 'Gamitin ang portal sa Filipino', 'PH', false),
        ],
      ),
    );
  }

  Widget _buildLanguageOption(String title, String subtitle, String code, bool isSelected) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isSelected ? const Color(0xFFF0F7FF) : Colors.transparent,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: isSelected ? const Color(0xFF3B82F6) : AppColors.border),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 30,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(4),
              border: Border.all(color: AppColors.border),
            ),
            child: Text(code, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
                Text(subtitle, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
              ],
            ),
          ),
          if (isSelected) const Icon(Icons.check_circle, color: Color(0xFF3B82F6)),
        ],
      ),
    );
  }

  Widget _buildHistoryTab() {
    return Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Activity History', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
              TextButton.icon(
                onPressed: () {},
                icon: const Icon(Icons.delete_outline, size: 18),
                label: const Text('Clear History'),
              ),
            ],
          ),
          const SizedBox(height: 24),
          Expanded(
            child: ListView.separated(
              itemCount: 4,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                return Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.background,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Visited settings', style: TextStyle(fontWeight: FontWeight.w600)),
                          Text('Opened profile settings page', style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                        ],
                      ),
                      Text('5/3/2026', style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTextField(String label, TextEditingController controller, IconData icon, {bool enabled = true, bool obscureText = false}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        TextField(
          controller: controller,
          enabled: enabled,
          obscureText: obscureText,
          decoration: InputDecoration(
            prefixIcon: Icon(icon, size: 18),
            fillColor: enabled ? Colors.transparent : AppColors.background,
            filled: !enabled,
          ),
        ),
      ],
    );
  }
}
