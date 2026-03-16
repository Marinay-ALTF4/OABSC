import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'brand_pill.dart';
import 'gradient_button.dart';
import 'input_field.dart';

class RegisterScreen extends StatefulWidget {
	const RegisterScreen({super.key});

	@override
	State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
	static const String _registerUrl = 'http://10.0.2.2/OABSC/api/register';

	final _formKey = GlobalKey<FormState>();
	final _nameController = TextEditingController();
	final _phoneController = TextEditingController();
	final _passwordController = TextEditingController();
	final _passwordConfirmController = TextEditingController();

	bool _obscurePassword = true;
	bool _obscurePasswordConfirm = true;
	bool _submitting = false;

	@override
	void dispose() {
		_nameController.dispose();
		_phoneController.dispose();
		_passwordController.dispose();
		_passwordConfirmController.dispose();
		super.dispose();
	}

	String? _validateName(String? value) {
		final name = (value ?? '').trim();
		if (name.isEmpty) {
			return 'Full name is required';
		}

		if (name.length < 3) {
			return 'Full name must be at least 3 characters';
		}

		final pattern = RegExp(r'^[A-Za-z\u00D1\u00F1\s]+$');
		if (!pattern.hasMatch(name)) {
			return 'Name allows letters and spaces only';
		}

		return null;
	}

	String? _validatePhone(String? value) {
		final phone = (value ?? '').trim();
		if (phone.isEmpty) {
			return 'Phone is required';
		}

		final normalized = _normalizePhone(phone);
		if (normalized.length < 10 || normalized.length > 15) {
			return 'Phone number must be 10 to 15 digits';
		}

		return null;
	}

	String? _validatePassword(String? value) {
		final password = value ?? '';
		if (password.isEmpty) {
			return 'Password is required';
		}

		if (password.length < 8) {
			return 'Password must be at least 8 characters';
		}

		return null;
	}

	String? _validatePasswordConfirm(String? value) {
		if ((value ?? '').isEmpty) {
			return 'Please confirm your password';
		}

		if (value != _passwordController.text) {
			return 'Password confirmation does not match';
		}

		return null;
	}

	String _normalizePhone(String raw) {
		return raw.replaceAll(RegExp(r'[^0-9]'), '');
	}

	String _buildSyntheticEmailFromPhone(String phoneDigits) {
		return 'p@$phoneDigits.mobile';
	}

	Future<void> _handleRegister() async {
		if (!_formKey.currentState!.validate() || _submitting) {
			return;
		}

		setState(() => _submitting = true);

		final name = _nameController.text.trim();
		final phoneDigits = _normalizePhone(_phoneController.text);
		final password = _passwordController.text;
		final syntheticEmail = _buildSyntheticEmailFromPhone(phoneDigits);

		try {
			final response = await http.post(
				Uri.parse(_registerUrl),
				body: {
					'name': name,
					'email': syntheticEmail,
					'password': password,
					'password_confirm': _passwordConfirmController.text,
				},
			);

			if (!mounted) {
				return;
			}

			Map<String, dynamic> payload = <String, dynamic>{};
			if (response.body.isNotEmpty) {
				final parsed = jsonDecode(response.body);
				if (parsed is Map<String, dynamic>) {
					payload = parsed;
				}
			}

			if (response.statusCode == 201) {
				ScaffoldMessenger.of(context).showSnackBar(
					const SnackBar(content: Text('Registration successful. Please login.')),
				);
				Navigator.of(context).pop();
				return;
			}

			String errorMessage = 'Unable to register account right now.';
			final errors = payload['messages'];
			if (errors is Map<String, dynamic> && errors.isNotEmpty) {
				errorMessage = errors.values.first.toString();
			} else if (payload['message'] is String && (payload['message'] as String).isNotEmpty) {
				errorMessage = payload['message'] as String;
			}

			ScaffoldMessenger.of(context).showSnackBar(
				SnackBar(content: Text(errorMessage)),
			);
		} catch (_) {
			if (!mounted) {
				return;
			}

			ScaffoldMessenger.of(context).showSnackBar(
				const SnackBar(content: Text('Network error. Check API URL and server status.')),
			);
		} finally {
			if (mounted) {
				setState(() => _submitting = false);
			}
		}
	}

	@override
	Widget build(BuildContext context) {
		return Scaffold(
			body: Container(
				width: double.infinity,
				height: double.infinity,
				decoration: const BoxDecoration(
					gradient: RadialGradient(
						center: Alignment(-1.0, -1.0),
						radius: 1.8,
						colors: [Color(0xFFE0F2FF), Color(0xFFF9FBFF)],
					),
				),
				child: SafeArea(
					child: Center(
						child: SingleChildScrollView(
							padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
							child: ConstrainedBox(
								constraints: const BoxConstraints(maxWidth: 420),
								child: _buildCard(),
							),
						),
					),
				),
			),
		);
	}

	Widget _buildCard() {
		return Container(
			decoration: BoxDecoration(
				color: Colors.white,
				borderRadius: BorderRadius.circular(18),
				border: Border.all(color: const Color(0x4C94A3B8)),
				boxShadow: const [
					BoxShadow(color: Color(0x1E0F172A), blurRadius: 45, offset: Offset(0, 18)),
				],
			),
			padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 32),
			child: Form(
				key: _formKey,
				child: Column(
					mainAxisSize: MainAxisSize.min,
					crossAxisAlignment: CrossAxisAlignment.stretch,
					children: [
						const Center(
							child: Column(
								children: [
									BrandPill(),
									SizedBox(height: 14),
									Text(
										'Register',
										style: TextStyle(
											fontSize: 22,
											fontWeight: FontWeight.bold,
											color: Color(0xFF0F172A),
										),
									),
								],
							),
						),
						const SizedBox(height: 24),
						InputField(
							label: 'Full name',
							hint: 'Enter your full name',
							controller: _nameController,
							validator: _validateName,
						),
						const SizedBox(height: 16),
						InputField(
							label: 'Phone',
							hint: 'Enter your phone number',
							controller: _phoneController,
							keyboardType: TextInputType.phone,
							validator: _validatePhone,
						),
						const SizedBox(height: 16),
						InputField(
							label: 'Password',
							hint: 'Enter your password',
							controller: _passwordController,
							obscureText: _obscurePassword,
							suffixIcon: IconButton(
								icon: Icon(
									_obscurePassword
											? Icons.visibility_off_outlined
											: Icons.visibility_outlined,
									color: const Color(0xFF94A3B8),
									size: 20,
								),
								onPressed: () {
									setState(() => _obscurePassword = !_obscurePassword);
								},
							),
							validator: _validatePassword,
						),
						const SizedBox(height: 8),
						const Text(
							'Reminder: Password must be at least 8 characters.',
							style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
						),
						const SizedBox(height: 16),
						InputField(
							label: 'Confirm password',
							hint: 'Confirm your password',
							controller: _passwordConfirmController,
							obscureText: _obscurePasswordConfirm,
							textInputAction: TextInputAction.done,
							onFieldSubmitted: (_) => _handleRegister(),
							suffixIcon: IconButton(
								icon: Icon(
									_obscurePasswordConfirm
											? Icons.visibility_off_outlined
											: Icons.visibility_outlined,
									color: const Color(0xFF94A3B8),
									size: 20,
								),
								onPressed: () {
									setState(() => _obscurePasswordConfirm = !_obscurePasswordConfirm);
								},
							),
							validator: _validatePasswordConfirm,
						),
						const SizedBox(height: 24),
						_submitting
								? const Center(child: CircularProgressIndicator())
								: GradientButton(
										label: 'REGISTER FOR CLINIC PORTAL',
										onTap: _handleRegister,
									),
						const SizedBox(height: 16),
						Row(
							mainAxisAlignment: MainAxisAlignment.center,
							children: [
								const Text(
									'Already have an account?  ',
									style: TextStyle(color: Color(0xFF64748B), fontSize: 12),
								),
								MouseRegion(
									cursor: SystemMouseCursors.click,
									child: GestureDetector(
										onTap: () => Navigator.of(context).pop(),
										child: const Text(
											'Login',
											style: TextStyle(
												color: Color(0xFF2563EB),
												fontWeight: FontWeight.w600,
												fontSize: 12,
											),
										),
									),
								),
							],
						),
					],
				),
			),
		);
	}
}
