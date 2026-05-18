import 'dart:async';
import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import '../utils/constants.dart';
import '../utils/responsive_helper.dart';
import '../services/api_service.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen>
    with SingleTickerProviderStateMixin {
  final _api = ApiService();
  final _formKey = GlobalKey<FormState>();
  final _otpFormKey = GlobalKey<FormState>();

  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _otpController = TextEditingController();

  bool _isLoading = false;
  bool _showOtpField = false;

  Timer? _resendTimer;
  int _secondsRemaining = 0;

  late AnimationController _animController;
  late Animation<double> _fadeAnim;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    );
    _fadeAnim = CurvedAnimation(parent: _animController, curve: Curves.easeOut);
    _animController.forward();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _otpController.dispose();
    _animController.dispose();
    _resendTimer?.cancel();
    super.dispose();
  }

  void _startTimer() {
    _secondsRemaining = 60;
    _resendTimer?.cancel();
    _resendTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() {
          if (_secondsRemaining > 0) {
            _secondsRemaining--;
          } else {
            _resendTimer?.cancel();
          }
        });
      }
    });
  }

  Future<void> _sendCode() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);
    try {
      final r = await _api.post('register/send-code', {
        'name': _nameController.text.trim(),
        'email': _emailController.text.trim().toLowerCase(),
        'password': _passwordController.text,
        'password_confirm': _confirmPasswordController.text,
      });

      if (mounted) {
        if (r['success'] == true) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Verification code sent to your email!'),
              backgroundColor: Color(0xFF059669),
            ),
          );
          setState(() => _showOtpField = true);
          _startTimer();
        } else {
          final err = r['message'] ?? 'Failed to send verification code';
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(err), backgroundColor: AppColors.error),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Connection error: $e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _verifyCode() async {
    if (!_otpFormKey.currentState!.validate()) return;

    setState(() => _isLoading = true);
    try {
      final r = await _api.post('register/verify-code', {
        'email': _emailController.text.trim().toLowerCase(),
        'code': _otpController.text.trim(),
      });

      if (mounted) {
        if (r['success'] == true) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Account verified and created successfully!'),
              backgroundColor: Color(0xFF059669),
            ),
          );
          Navigator.pop(context); // Pop back to LoginScreen!
        } else {
          final err = r['message'] ?? 'Invalid code. Please try again.';
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(err), backgroundColor: AppColors.error),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Connection error: $e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _resendCode() async {
    if (_secondsRemaining > 0) return;

    setState(() => _isLoading = true);
    try {
      final r = await _api.post('register/send-code', {
        'name': _nameController.text.trim(),
        'email': _emailController.text.trim().toLowerCase(),
        'password': _passwordController.text,
        'password_confirm': _confirmPasswordController.text,
      });

      if (mounted) {
        if (r['success'] == true) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('A new verification code has been sent!'),
              backgroundColor: Color(0xFF059669),
            ),
          );
          _startTimer();
        } else {
          final err = r['message'] ?? 'Failed to resend code';
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(err), backgroundColor: AppColors.error),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Connection error: $e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cardWidth = ResponsiveHelper.authCardWidth(context);

    return Scaffold(
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          gradient: RadialGradient(
            center: Alignment.topLeft,
            radius: 1.5,
            colors: [AppColors.backgroundLight, AppColors.background],
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(
                horizontal: AppSpacing.lg,
                vertical: AppSpacing.xxl,
              ),
              child: FadeTransition(
                opacity: _fadeAnim,
                child: Container(
                  width: cardWidth,
                  padding: const EdgeInsets.symmetric(
                    horizontal: AppSpacing.xxl,
                    vertical: AppSpacing.xxxl,
                  ),
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.06),
                        blurRadius: 20,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: AnimatedSwitcher(
                    duration: const Duration(milliseconds: 300),
                    child: _showOtpField ? _buildOtpForm() : _buildRegisterForm(),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildRegisterForm() {
    return Form(
      key: _formKey,
      child: Column(
        key: const ValueKey('RegisterDetailsForm'),
        mainAxisSize: MainAxisSize.min,
        children: [
          // Logo and title badge
          Container(
            padding: const EdgeInsets.symmetric(
              horizontal: 12,
              vertical: 6,
            ),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                ClipOval(
                  child: Image.asset(
                    AppConstants.logoPath,
                    width: 24,
                    height: 24,
                    fit: BoxFit.cover,
                  ),
                ),
                const SizedBox(width: 8),
                const Text(
                  'CLINIC APPOINTMENT PORTAL',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF2563EB),
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.xl),

          // Register heading
          const Text(
            'Register',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: AppSpacing.xl),

          // Full name field
          _buildFieldLabel('Full name'),
          const SizedBox(height: AppSpacing.xs),
          TextFormField(
            controller: _nameController,
            decoration: const InputDecoration(
              hintText: 'Enter your full name',
            ),
            validator: (value) =>
                value!.isEmpty ? 'Please enter your name' : null,
          ),
          const SizedBox(height: AppSpacing.lg),

          // Email field
          _buildFieldLabel('Email'),
          const SizedBox(height: AppSpacing.xs),
          TextFormField(
            controller: _emailController,
            keyboardType: TextInputType.emailAddress,
            decoration: const InputDecoration(
              hintText: 'Enter your email',
            ),
            validator: (value) {
              if (value == null || value.isEmpty) return 'Please enter your email';
              if (!RegExp(r'^[^@]+@[^@]+\.[^@]+$').hasMatch(value)) return 'Invalid email format';
              return null;
            },
          ),
          _buildHelpText(
            'We will send a 6-digit verification code to this email before creating the account.',
          ),
          const SizedBox(height: AppSpacing.lg),

          // Password field
          _buildFieldLabel('Password'),
          const SizedBox(height: AppSpacing.xs),
          TextFormField(
            controller: _passwordController,
            obscureText: true,
            decoration: const InputDecoration(
              hintText: 'Enter your password',
            ),
            validator: (value) => value!.length < 8
                ? 'Password must be at least 8 characters'
                : null,
          ),
          _buildHelpText(
            'Reminder: Password must be at least 8 characters.',
          ),
          const SizedBox(height: AppSpacing.lg),

          // Confirm password field
          _buildFieldLabel('Confirm password'),
          const SizedBox(height: AppSpacing.xs),
          TextFormField(
            controller: _confirmPasswordController,
            obscureText: true,
            decoration: const InputDecoration(
              hintText: 'Confirm your password',
            ),
            validator: (value) =>
                value != _passwordController.text
                ? 'Passwords do not match'
                : null,
          ),
          const SizedBox(height: AppSpacing.xl),

          // Submit button
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: _isLoading ? null : _sendCode,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(999),
                ),
              ),
              child: _isLoading
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                    )
                  : const Text(
                      'SEND VERIFICATION CODE',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 0.8,
                      ),
                    ),
            ),
          ),
          const SizedBox(height: AppSpacing.lg),

          // Login link
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text(
                "Already have an account? ",
                style: TextStyle(
                  fontSize: 13,
                  color: AppColors.textSecondary,
                ),
              ),
              GestureDetector(
                onTap: () => Navigator.pop(context),
                child: const Text(
                  'Login',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF2563EB),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildOtpForm() {
    return Form(
      key: _otpFormKey,
      child: Column(
        key: const ValueKey('OtpVerificationForm'),
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFF0FDF4),
              shape: BoxShape.circle,
              border: Border.all(color: const Color(0xFFDCFCE7)),
            ),
            child: const Icon(
              Icons.mark_email_read_outlined,
              color: Color(0xFF16A34A),
              size: 32,
            ),
          ),
          const SizedBox(height: AppSpacing.lg),

          const Text(
            'Verify Email',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: AppSpacing.md),

          Text(
            'We have sent a 6-digit verification code to:\n${_emailController.text}',
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 13,
              color: AppColors.textSecondary,
              height: 1.4,
            ),
          ),
          const SizedBox(height: AppSpacing.xl),

          _buildFieldLabel('Verification Code'),
          const SizedBox(height: AppSpacing.xs),
          TextFormField(
            controller: _otpController,
            keyboardType: TextInputType.number,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              letterSpacing: 8,
            ),
            decoration: const InputDecoration(
              hintText: '000000',
              hintStyle: TextStyle(
                fontSize: 20,
                color: AppColors.textHint,
                letterSpacing: 8,
              ),
              contentPadding: EdgeInsets.symmetric(vertical: 14),
            ),
            validator: (value) {
              if (value == null || value.isEmpty) return 'Enter the 6-digit OTP code';
              if (value.length != 6) return 'Verification code must be 6 digits';
              return null;
            },
          ),
          const SizedBox(height: AppSpacing.xl),

          // Verify Button
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: _isLoading ? null : _verifyCode,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF16A34A), // Distinct green for validation success
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(999),
                ),
              ),
              child: _isLoading
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                    )
                  : const Text(
                      'VERIFY & REGISTER',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 0.8,
                      ),
                    ),
            ),
          ),
          const SizedBox(height: AppSpacing.lg),

          // Resend or Cooldown Text
          if (_secondsRemaining > 0)
            Text(
              'Resend code in $_secondsRemaining seconds',
              style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
            )
          else
            TextButton(
              onPressed: _isLoading ? null : _resendCode,
              child: const Text(
                'Resend Code',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF16A34A),
                ),
              ),
            ),

          const SizedBox(height: AppSpacing.sm),

          // Back link
          TextButton.icon(
            onPressed: () {
              setState(() {
                _showOtpField = false;
                _otpController.clear();
              });
            },
            icon: const Icon(Icons.arrow_back_rounded, size: 16, color: AppColors.textSecondary),
            label: const Text(
              'Back to Register Details',
              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFieldLabel(String label) {
    return Align(
      alignment: Alignment.centerLeft,
      child: Text(
        label,
        style: const TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w600,
          color: AppColors.textPrimary,
        ),
      ),
    );
  }

  Widget _buildHelpText(String text) {
    return Padding(
      padding: const EdgeInsets.only(top: 4.0),
      child: Align(
        alignment: Alignment.centerLeft,
        child: Text(
          text,
          style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
        ),
      ),
    );
  }
}
