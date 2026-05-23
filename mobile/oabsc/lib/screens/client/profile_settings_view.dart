import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/auth_service.dart';
import '../../services/api_service.dart';
import 'package:shared_preferences/shared_preferences.dart';

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
  bool _obscureCurrent = true;
  bool _obscureNew = true;
  bool _obscureConfirm = true;

  String _selectedLanguage = 'US';
  List<Map<String, String>> _activityHistory = [];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadProfile();
    _loadHistory();
    _addHistory('Visited settings', 'Opened profile settings page');
  }

  Future<void> _loadHistory() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final historyList = prefs.getStringList('activity_history') ?? [];
      if (mounted) {
        setState(() {
          _activityHistory = historyList.map((item) {
            final parts = item.split('|');
            return {
              'title': parts.isNotEmpty ? parts[0] : 'Action',
              'subtitle': parts.length > 1 ? parts[1] : '',
              'date': parts.length > 2 ? parts[2] : '',
            };
          }).toList();
        });
      }
    } catch (_) {}
  }

  Future<void> _addHistory(String title, String subtitle) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final historyList = prefs.getStringList('activity_history') ?? [];
      
      final now = DateTime.now();
      final hour = now.hour > 12 ? now.hour - 12 : (now.hour == 0 ? 12 : now.hour);
      final ampm = now.hour >= 12 ? 'PM' : 'AM';
      final min = now.minute.toString().padLeft(2, '0');
      final sec = now.second.toString().padLeft(2, '0');
      final dateStr = '${now.month}/${now.day}/${now.year}, $hour:$min:$sec $ampm';
      
      historyList.insert(0, '$title|$subtitle|$dateStr');
      if (historyList.length > 20) historyList.removeLast();
      
      await prefs.setStringList('activity_history', historyList);
      _loadHistory();
    } catch (_) {}
  }

  Future<void> _clearHistory() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('activity_history');
      setState(() => _activityHistory = []);
    } catch (_) {}
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
      final response = await _apiService.post('profile/update', {
        'user_id': userId,
        'name': _nameController.text.trim(),
        'phone': _phoneController.text.trim(),
        'city': _cityController.text.trim(),
        'address': _addressController.text.trim(),
      });

      if (mounted) {
        if (response['success'] == true) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Profile updated successfully'), backgroundColor: AppColors.success),
          );
          _addHistory('Updated profile', 'Saved personal information');
          _loadProfile();
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(response['message'] ?? 'Failed to update profile'), backgroundColor: AppColors.error),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _updatePassword() async {
    if (_currentPasswordController.text.isEmpty || _newPasswordController.text.isEmpty || _confirmPasswordController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please fill all password fields'), backgroundColor: AppColors.error));
      return;
    }
    if (_newPasswordController.text != _confirmPasswordController.text) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('New passwords do not match'), backgroundColor: AppColors.error));
      return;
    }

    setState(() => _isLoading = true);
    
    try {
      final userId = await _authService.getSavedUserId();
      final response = await _apiService.post('profile/update', {
        'user_id': userId,
        'current_password': _currentPasswordController.text,
        'new_password': _newPasswordController.text,
      });

      if (mounted) {
        if (response['success'] == true || response['message'] == 'Profile updated successfully.') {
          setState(() {
            _currentPasswordController.clear();
            _newPasswordController.clear();
            _confirmPasswordController.clear();
          });
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Password updated successfully!'), backgroundColor: AppColors.success),
          );
          _addHistory('Security updated', 'Changed account password');
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(response['message'] ?? 'Failed to update password'), backgroundColor: AppColors.error),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: AppColors.error),
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
              // Header
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Profile Settings', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                        const SizedBox(height: 4),
                        Text('Manage your personal information and account security.', style: TextStyle(fontSize: 13, color: AppColors.textSecondary.withValues(alpha: 0.8))),
                      ],
                    ),
                  ),
                  OutlinedButton.icon(
                    onPressed: widget.onBack,
                    icon: const Icon(Icons.arrow_back, size: 13),
                    label: const Text('Back to Dashboard', style: TextStyle(fontSize: 11)),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF6366F1), // Matching web link color
                      side: const BorderSide(color: Color(0xFFE0E7FF)),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      backgroundColor: Colors.white,
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
                      SizedBox(width: 280, child: _buildSidebarCard()),
                      const SizedBox(width: AppSpacing.xl),
                      Expanded(child: _buildMainContent()),
                    ],
                  )
                : Column(
                    children: [
                      _buildSidebarCard(),
                      const SizedBox(height: AppSpacing.xl),
                      _buildMainContent(),
                    ],
                  ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildSidebarCard() {
    final initials = _userData['name'] != null && _userData['name'].toString().isNotEmpty
        ? _userData['name'].split(' ').take(2).map((s) => s[0]).join('').toUpperCase()
        : 'C';

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        children: [
          // Gradient Header
          Container(
            height: 120,
            decoration: const BoxDecoration(
              borderRadius: BorderRadius.only(topLeft: Radius.circular(16), topRight: Radius.circular(16)),
              gradient: LinearGradient(
                colors: [Color(0xFF8B5CF6), Color(0xFF6366F1)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Stack(
              children: [
                Positioned(
                  top: 12,
                  right: 12,
                  child: Row(
                    children: List.generate(3, (i) => const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 2),
                      child: Icon(Icons.circle, size: 4, color: Colors.white54),
                    )),
                  ),
                ),
                Align(
                  alignment: Alignment.bottomCenter,
                  child: FractionalTranslation(
                    translation: const Offset(0, 0.5),
                    child: Container(
                      padding: const EdgeInsets.all(4),
                      decoration: const BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                      ),
                      child: CircleAvatar(
                        radius: 36,
                        backgroundColor: const Color(0xFF6366F1),
                        child: Text(
                          initials,
                          style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w700, color: Colors.white),
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 48), // Space for avatar overlap
          Text(
            _userData['name'] ?? 'Client',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.textPrimary),
          ),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFFEEF2FF),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFC7D2FE)),
            ),
            child: Text(
              (_userData['role'] ?? 'CLIENT').toString().toUpperCase(),
              style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF6366F1), letterSpacing: 1.0),
            ),
          ),
          const SizedBox(height: 24),
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 24),
            child: Divider(color: AppColors.border),
          ),
          const SizedBox(height: 16),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: Column(
              children: [
                _buildSidebarInfoRow(Icons.email_outlined, _userData['email'] ?? '—'),
                const SizedBox(height: 16),
                _buildSidebarInfoRow(Icons.phone_outlined, _userData['phone'] ?? '—'),
                const SizedBox(height: 16),
                _buildSidebarInfoRow(Icons.location_on_outlined, _userData['city'] ?? '—'),
              ],
            ),
          ),
          const SizedBox(height: 24),
          // Profile Completion Card
          Builder(
            builder: (context) {
              int totalFields = 5; // name, email, phone, city, address
              int filledFields = 0;
              
              if (_userData['name'] != null && _userData['name'].toString().trim().isNotEmpty) filledFields++;
              if (_userData['email'] != null && _userData['email'].toString().trim().isNotEmpty) filledFields++;
              if (_userData['phone'] != null && _userData['phone'].toString().trim().isNotEmpty) filledFields++;
              if (_userData['city'] != null && _userData['city'].toString().trim().isNotEmpty) filledFields++;
              if (_userData['address'] != null && _userData['address'].toString().trim().isNotEmpty) filledFields++;
              
              if (_userData['role'] == 'doctor') {
                totalFields += 4;
                if (_userData['specialization'] != null && _userData['specialization'].toString().trim().isNotEmpty) filledFields++;
                if (_userData['experience'] != null && _userData['experience'].toString().trim().isNotEmpty) filledFields++;
                if (_userData['degree'] != null && _userData['degree'].toString().trim().isNotEmpty) filledFields++;
                if (_userData['bio'] != null && _userData['bio'].toString().trim().isNotEmpty) filledFields++;
              }

              final int completionPct = ((filledFields / totalFields) * 100).round();
              final double completionVal = filledFields / totalFields;

              return Container(
                margin: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFFF5F3FF),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    SizedBox(
                      width: 40,
                      height: 40,
                      child: Stack(
                        fit: StackFit.expand,
                        children: [
                          CircularProgressIndicator(
                            value: completionVal,
                            backgroundColor: const Color(0xFFE0E7FF),
                            color: const Color(0xFF6366F1),
                            strokeWidth: 4,
                          ),
                          Center(
                            child: Text('$completionPct%', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF6366F1))),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Text('Profile Completion', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFF6366F1))),
                          SizedBox(height: 2),
                          Text('Complete your profile for better experience.', style: TextStyle(fontSize: 9, color: AppColors.textSecondary)),
                        ],
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildSidebarInfoRow(IconData icon, String text) {
    return Row(
      children: [
        Icon(icon, size: 16, color: const Color(0xFF6366F1)),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }

  Widget _buildMainContent() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Tab Bar
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.border),
          ),
          child: TabBar(
            controller: _tabController,
            isScrollable: true,
            tabAlignment: TabAlignment.start,
            labelColor: Colors.white,
            unselectedLabelColor: AppColors.textSecondary,
            indicator: BoxDecoration(
              color: const Color(0xFF6366F1),
              borderRadius: BorderRadius.circular(8),
            ),
            indicatorPadding: const EdgeInsets.all(4),
            indicatorSize: TabBarIndicatorSize.tab,
            labelPadding: const EdgeInsets.symmetric(horizontal: 16),
            labelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
            tabs: [
              Tab(
                child: Row(
                  children: const [
                    Icon(Icons.person_outline, size: 16),
                    SizedBox(width: 8),
                    Text('Personal Info'),
                  ],
                ),
              ),
              Tab(
                child: Row(
                  children: const [
                    Icon(Icons.security_outlined, size: 16),
                    SizedBox(width: 8),
                    Text('Security'),
                  ],
                ),
              ),
              Tab(
                child: Row(
                  children: const [
                    Icon(Icons.language_outlined, size: 16),
                    SizedBox(width: 8),
                    Text('Language'),
                  ],
                ),
              ),
              Tab(
                child: Row(
                  children: const [
                    Icon(Icons.history_outlined, size: 16),
                    SizedBox(width: 8),
                    Text('Activity History'),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        // Tab Content
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.border),
          ),
          child: SizedBox(
            height: 550, // Fixed height for tab content area
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
        ),
      ],
    );
  }

  Widget _buildPersonalInfoTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Personal Information', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
          const SizedBox(height: 4),
          const Text('Update your name, contact details, and address.', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          const SizedBox(height: 24),
          const Divider(color: AppColors.border),
          const SizedBox(height: 24),
          
          const Text('Profile Photo', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              border: Border.all(color: AppColors.border),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF3F4F6),
                    border: Border.all(color: const Color(0xFFE5E7EB)),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: const Text('Choose File', style: TextStyle(fontSize: 12, color: AppColors.textPrimary)),
                ),
                const SizedBox(width: 12),
                const Text('No file chosen', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
              ],
            ),
          ),
          const SizedBox(height: 24),
          
          Row(
            children: [
              Expanded(child: _buildTextField('Full Name', _nameController, Icons.person_outline)),
              const SizedBox(width: 24),
              Expanded(child: _buildTextField('Email Address', _emailController, Icons.email_outlined, enabled: false, helperText: 'Email cannot be changed.')),
            ],
          ),
          const SizedBox(height: 24),
          
          Row(
            children: [
              Expanded(child: _buildTextField('Phone Number', _phoneController, Icons.phone_outlined, hintText: '+63 9XX XXX XXXX')),
              const SizedBox(width: 24),
              Expanded(child: _buildTextField('City / Municipality', _cityController, Icons.location_city_outlined, hintText: 'e.g. General Santos City')),
            ],
          ),
          const SizedBox(height: 24),
          
          _buildTextField('Home Address', _addressController, Icons.home_outlined, hintText: 'Street, Barangay, City'),
          
          const SizedBox(height: 32),
          const Divider(color: AppColors.border),
          const SizedBox(height: 16),
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            crossAxisAlignment: WrapCrossAlignment.center,
            runSpacing: 16,
            children: [
              Row(
                mainAxisSize: MainAxisSize.min,
                children: const [
                  Icon(Icons.info_outline, size: 14, color: AppColors.textSecondary),
                  SizedBox(width: 6),
                  Text('Last updated: Just now', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                ],
              ),
              ElevatedButton.icon(
                onPressed: _isLoading ? null : _saveChanges,
                icon: const Icon(Icons.check, size: 16, color: Colors.white),
                label: _isLoading 
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('Save Changes', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF6366F1),
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSecurityTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Change Password', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
          const SizedBox(height: 4),
          const Text('Keep your account secure by using a strong password.', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          const SizedBox(height: 24),
          const Divider(color: AppColors.border),
          const SizedBox(height: 24),
          
          _buildPasswordField('Current Password', _currentPasswordController, 'Enter current password', _obscureCurrent, () => setState(() => _obscureCurrent = !_obscureCurrent)),
          const SizedBox(height: 24),
          _buildPasswordField('New Password', _newPasswordController, 'At least 8 characters', _obscureNew, () => setState(() => _obscureNew = !_obscureNew)),
          const SizedBox(height: 24),
          _buildPasswordField('Confirm New Password', _confirmPasswordController, 'Re-enter new password', _obscureConfirm, () => setState(() => _obscureConfirm = !_obscureConfirm)),
          const SizedBox(height: 16),
          
          // Password Requirements
          Wrap(
            spacing: 16,
            runSpacing: 8,
            children: [
              _buildRequirementItem('At least 8 characters'),
              _buildRequirementItem('One uppercase letter'),
              _buildRequirementItem('One number'),
              _buildRequirementItem('One special character'),
            ],
          ),
          
          const SizedBox(height: 32),
          ElevatedButton(
            onPressed: _isLoading ? null : _updatePassword,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF6366F1),
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: _isLoading 
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Text('Update Password', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }

  Widget _buildRequirementItem(String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        const Icon(Icons.circle_outlined, size: 10, color: AppColors.textSecondary),
        const SizedBox(width: 6),
        Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
      ],
    );
  }

  Widget _buildLanguageTab() {
    return Padding(
      padding: const EdgeInsets.all(32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Language Preference', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
          const SizedBox(height: 4),
          const Text('Choose the language used across the portal.', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          const SizedBox(height: 24),
          
          _buildLanguageOption('English', 'Use the portal in English', 'US', _selectedLanguage == 'US', () {
            setState(() => _selectedLanguage = 'US');
            _addHistory('Language changed', 'Updated portal language to English');
          }),
          const SizedBox(height: 16),
          _buildLanguageOption('Filipino', 'Gamitin ang portal sa Filipino', 'PH', _selectedLanguage == 'PH', () {
            setState(() => _selectedLanguage = 'PH');
            _addHistory('Language changed', 'Updated portal language to Filipino');
          }),
          
          const SizedBox(height: 32),
          const Text('Changes apply immediately across all portal pages.', style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
        ],
      ),
    );
  }

  Widget _buildLanguageOption(String title, String subtitle, String code, bool isSelected, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFFEEF2FF) : const Color(0xFFF8FAFC),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: isSelected ? const Color(0xFF6366F1) : const Color(0xFFE2E8F0)),
        ),
        child: Row(
          children: [
            Container(
              width: 48,
              height: 36,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(4),
                border: Border.all(color: AppColors.border),
                boxShadow: [
                  BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 2, offset: const Offset(0, 1)),
                ],
              ),
              child: Text(code, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
            ),
            const SizedBox(width: 24),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                  const SizedBox(height: 2),
                  Text(subtitle, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                ],
              ),
            ),
            if (isSelected) 
              Container(
                padding: const EdgeInsets.all(4),
                decoration: const BoxDecoration(color: Color(0xFF6366F1), shape: BoxShape.circle),
                child: const Icon(Icons.check, size: 14, color: Colors.white),
              )
            else
              Container(
                width: 22,
                height: 22,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: Colors.white,
                  border: Border.all(color: AppColors.border),
                ),
                child: const Icon(Icons.check, size: 14, color: Color(0xFFE2E8F0)),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildHistoryTab() {
    return Padding(
      padding: const EdgeInsets.all(32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text('Activity History', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                  SizedBox(height: 4),
                  Text('Recent actions stored locally in your browser.', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                ],
              ),
            ],
          ),
          const SizedBox(height: 24),
          const Divider(color: AppColors.border),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Expanded(
                child: Text('Recent actions are stored locally in your browser.', style: TextStyle(fontSize: 13, color: AppColors.textPrimary)),
              ),
              const SizedBox(width: 8),
              OutlinedButton.icon(
                onPressed: _clearHistory,
                icon: const Icon(Icons.delete_outline, size: 14),
                label: const Text('Clear History', style: TextStyle(fontSize: 12)),
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFFEF4444),
                  side: const BorderSide(color: Color(0xFFFEE2E2)),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  backgroundColor: const Color(0xFFFEF2F2),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Expanded(
            child: _activityHistory.isEmpty
                ? const Center(child: Text('No recent activity.', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)))
                : ListView.separated(
                    itemCount: _activityHistory.length,
                    separatorBuilder: (context, index) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final item = _activityHistory[index];
                      return Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(item['title']!, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                                  const SizedBox(height: 4),
                                  Text(item['subtitle']!, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                                ],
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(item['date']!, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
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

  Widget _buildTextField(String label, TextEditingController controller, IconData icon, {bool enabled = true, String? hintText, String? helperText}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
        const SizedBox(height: 8),
        TextField(
          controller: controller,
          enabled: enabled,
          decoration: InputDecoration(
            prefixIcon: Icon(icon, size: 18, color: const Color(0xFF94A3B8)),
            hintText: hintText,
            hintStyle: const TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
            fillColor: enabled ? Colors.white : const Color(0xFFF8FAFC),
            filled: true,
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(8),
              borderSide: BorderSide(color: const Color(0xFFE2E8F0)),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(8),
              borderSide: BorderSide(color: const Color(0xFFE2E8F0)),
            ),
          ),
          style: TextStyle(fontSize: 14, color: enabled ? AppColors.textPrimary : AppColors.textSecondary),
        ),
        if (helperText != null) ...[
          const SizedBox(height: 6),
          Text(helperText, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
        ]
      ],
    );
  }

  Widget _buildPasswordField(String label, TextEditingController controller, String hintText, bool obscureText, VoidCallback onToggleVisibility) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
        const SizedBox(height: 8),
        TextField(
          controller: controller,
          obscureText: obscureText,
          decoration: InputDecoration(
            prefixIcon: const Icon(Icons.lock_outline, size: 18, color: Color(0xFF94A3B8)),
            suffixIcon: IconButton(
              icon: Icon(obscureText ? Icons.visibility_outlined : Icons.visibility_off_outlined, size: 18, color: const Color(0xFF94A3B8)),
              onPressed: onToggleVisibility,
            ),
            hintText: hintText,
            hintStyle: const TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
            fillColor: Colors.white,
            filled: true,
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(8),
              borderSide: BorderSide(color: const Color(0xFFE2E8F0)),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(8),
              borderSide: BorderSide(color: const Color(0xFFE2E8F0)),
            ),
          ),
          style: const TextStyle(fontSize: 14, color: AppColors.textPrimary),
        ),
      ],
    );
  }
}
