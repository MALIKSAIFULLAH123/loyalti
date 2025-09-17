import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:loyalty_app/Auth/SignIn.dart';
import 'package:loyalty_app/utils/api_constants.dart';
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:loyalty_app/Services/language_service.dart';
import 'package:provider/provider.dart';
import 'package:firebase_auth/firebase_auth.dart';

Map<String, dynamic>? globalLiscence;

class SignUpScreen2 extends StatefulWidget {
  const SignUpScreen2({super.key});

  @override
  State<SignUpScreen2> createState() => _SignUpScreen2State();
}

class _SignUpScreen2State extends State<SignUpScreen2> {
  final TextEditingController fullNameController = TextEditingController();
  final TextEditingController phoneController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  final TextEditingController otpController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _acceptTerms = false;
  final bool _obscurePassword = true;
  String _selectedLanguage = 'GR';
  bool _isLoading = false;
  bool _isCheckingUser = false;

  // Firebase OTP related variables
  final FirebaseAuth _auth = FirebaseAuth.instance;
  String? _verificationId;
  bool _otpSent = false;
  bool _isVerifyingOtp = false;
  bool _phoneVerified = false;
  int _resendToken = 0;
  bool _canResend = true;
  int _resendCountdown = 0;

  @override
  void initState() {
    super.initState();
  }

  @override
  void dispose() {
    fullNameController.dispose();
    phoneController.dispose();
    passwordController.dispose();
    otpController.dispose();
    super.dispose();
  }

  // Show custom snackbar at top with app color
  void _showCustomSnackBar(
    String message, {
    bool isError = false,
    bool isSuccess = false,
  }) {
    final overlay = Overlay.of(context);
    late OverlayEntry overlayEntry;

    overlayEntry = OverlayEntry(
      builder: (context) => Positioned(
        top: MediaQuery.of(context).padding.top + 10,
        left: 20,
        right: 20,
        child: Material(
          color: Colors.transparent,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: isError
                  ? Colors.red.shade600
                  : isSuccess
                  ? Colors.green.shade600
                  : Colors.orange.shade600,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.2),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Icon(
                  isError
                      ? Icons.error_outline
                      : isSuccess
                      ? Icons.check_circle_outline
                      : Icons.info_outline,
                  color: Colors.white,
                  size: 20,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    message,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w500,
                      fontFamily: 'Poppins',
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );

    overlay.insert(overlayEntry);
    Future.delayed(const Duration(seconds: 3), () {
      overlayEntry.remove();
    });
  }

  // Form validation
  String? _validateName(String? value) {
    final localizations = AppLocalizations.of(context)!;
    if (value == null || value.isEmpty) {
      return localizations.nameRequired;
    }
    if (value.length < 2) {
      return localizations.nameMinLength;
    }
    if (!RegExp(r'^[a-zA-Z\s]+$').hasMatch(value)) {
      return localizations.nameLettersOnly;
    }
    return null;
  }

  String? _validatePhone(String? value) {
    final localizations = AppLocalizations.of(context)!;
    if (value == null || value.isEmpty) {
      return localizations.phoneRequired;
    }

    // Remove spaces and special characters for validation
    String cleanValue = value.replaceAll(RegExp(r'[^\d+]'), '');

    // Remove leading + for length check
    String digitsOnly = cleanValue.replaceAll('+', '');

    // Check minimum length (should be at least 10 digits)
    if (digitsOnly.length < 10) {
      return localizations.phoneMinLength;
    }

    // Check maximum length (shouldn't exceed 15 digits as per E.164)
    if (digitsOnly.length > 15) {
      return localizations.phoneTooLong;
    }

    return null;
  }

  String? _validateOtp(String? value) {
    final localizations = AppLocalizations.of(context)!;
    if (value == null || value.isEmpty) {
      return localizations.otpRequired;
    }
    if (value.length != 6) {
      return localizations.otpMustBeSixDigits;
    }
    if (!RegExp(r'^[0-9]+$').hasMatch(value)) {
      return localizations.otpDigitsOnly;
    }
    return null;
  }

  // Firebase OTP Functions
  String _formatPhoneNumber(String phone) {
    // Remove all non-digit characters except +
    String cleanPhone = phone.replaceAll(RegExp(r'[^\d+]'), '');

    debugPrint('Original phone: $phone');
    debugPrint('Cleaned phone: $cleanPhone');
    final localizations = AppLocalizations.of(context)!;

    // If already has country code, return as is
    if (cleanPhone.startsWith('+')) {
      debugPrint('Already formatted phone: $cleanPhone');
      return cleanPhone;
    }

    // Handle different country patterns
    if (cleanPhone.startsWith('0')) {
      // Remove leading zero and detect country
      cleanPhone = cleanPhone.substring(1);

      // Pakistan numbers (3xxxxxxxxx after removing 0)
      if (cleanPhone.startsWith('3') && cleanPhone.length == 10) {
        cleanPhone = '+92$cleanPhone';
      }
      // India numbers (6,7,8,9xxxxxxxxx after removing 0)
      else if (RegExp(r'^[6-9]\d{9}$').hasMatch(cleanPhone)) {
        cleanPhone = '+91$cleanPhone';
      }
      // UK numbers
      else if (cleanPhone.startsWith('7') && cleanPhone.length == 10) {
        cleanPhone = '+44$cleanPhone';
      }
      // Add more country patterns as needed
      else {
        // Default: assume it needs country code
        throw Exception(localizations.includeCountryCode);
      }
    }
    // Handle numbers without leading zero
    else {
      // If 10-11 digits, might need country code
      if (cleanPhone.length >= 10 && cleanPhone.length <= 11) {
        // Pakistan pattern (3xxxxxxxxx)
        if (cleanPhone.startsWith('3') && cleanPhone.length == 10) {
          cleanPhone = '+92$cleanPhone';
        }
        // India pattern (6,7,8,9xxxxxxxxx)
        else if (RegExp(r'^[6-9]\d{9}$').hasMatch(cleanPhone)) {
          cleanPhone = '+91$cleanPhone';
        }
        // US/Canada pattern (10 digits)
        else if (cleanPhone.length == 10) {
          cleanPhone = '+1$cleanPhone';
        } else {
          throw Exception(localizations.includeCountryCode);
        }
      }
      // If 12+ digits, add + if missing
      else if (cleanPhone.length >= 12) {
        cleanPhone = '+$cleanPhone';
      } else {
        throw Exception(localizations.invalidPhoneFormat);
      }
    }

    debugPrint('Formatted phone: $cleanPhone');
    return cleanPhone;
  }

  Future<void> _manualVerifyOtp(String otp) async {
    final localizations = AppLocalizations.of(context)!;
    if (_verificationId == null) {
      _showCustomSnackBar(localizations.requestOtpFirst, isError: true);
      return;
    }

    setState(() => _isVerifyingOtp = true);

    try {
      PhoneAuthCredential credential = PhoneAuthProvider.credential(
        verificationId: _verificationId!,
        smsCode: otp,
      );

      // Just verify the credential without signing in
      UserCredential userCredential = await _auth.signInWithCredential(
        credential,
      );

      if (userCredential.user != null) {
        setState(() => _phoneVerified = true);
        _showCustomSnackBar(
          localizations.phoneVerifiedSuccess,
          isSuccess: true,
        );

        // Immediately sign out
        await _auth.signOut();

        // Proceed with backend signup
        await _handleBackendSignup();
      }
    } on FirebaseAuthException catch (e) {
      String errorMessage = localizations.invalidOtp;

      switch (e.code) {
        case 'invalid-verification-code':
          errorMessage = localizations.invalidOtpCheck;
          break;
        case 'session-expired':
          errorMessage = localizations.otpExpired;
          setState(() {
            _otpSent = false;
            _verificationId = null;
            otpController.clear();
          });
          break;
        default:
          errorMessage = e.message ?? localizations.invalidOtp;
      }

      _showCustomSnackBar(errorMessage, isError: true);
    } finally {
      setState(() => _isVerifyingOtp = false);
    }
  }

  Future<void> _sendOtp() async {
    await _sendOtpWithRetry();
  }

  void _startResendCountdown() {
    Future.delayed(const Duration(seconds: 1), () {
      if (mounted && _resendCountdown > 0) {
        setState(() => _resendCountdown--);
        _startResendCountdown();
      } else if (mounted) {
        setState(() => _canResend = true);
      }
    });
  }

  Future<void> _verifyOtp() async {
    final localizations = AppLocalizations.of(context)!;
    if (otpController.text.length != 6) {
      _showCustomSnackBar(localizations.enterSixDigitOtp, isError: true);
      return;
    }

    await _manualVerifyOtp(otpController.text.trim());
  }

  Future<void> _sendOtpWithRetry() async {
    final localizations = AppLocalizations.of(context)!;
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (!_acceptTerms) {
      _showCustomSnackBar(localizations.acceptTermsError, isError: true);
      return;
    }

    setState(() => _isLoading = true);

    try {
      final formattedPhone = _formatPhoneNumber(phoneController.text.trim());
      debugPrint('Attempting to send OTP to: $formattedPhone');

      await _auth.verifyPhoneNumber(
        phoneNumber: formattedPhone,
        timeout: const Duration(seconds: 120),

        verificationCompleted: (PhoneAuthCredential credential) async {
          debugPrint('✅ Auto verification completed');
          setState(() => _phoneVerified = true);
          _showCustomSnackBar(localizations.phoneVerifiedAuto, isSuccess: true);

          // Sign out immediately and proceed
          await _auth.signOut();
          await _handleBackendSignup();
        },

        verificationFailed: (FirebaseAuthException e) {
          debugPrint('❌ Verification failed: ${e.code} - ${e.message}');
          String errorMessage = localizations.failedSendOtp;

          switch (e.code) {
            case 'invalid-phone-number':
              errorMessage = localizations.invalidPhoneFormatCountry;
              break;
            case 'too-many-requests':
              errorMessage = localizations.tooManyAttempts;
              break;
            case 'quota-exceeded':
              errorMessage = localizations.smsQuotaExceeded;
              break;
            case 'app-not-authorized':
              errorMessage = localizations.appNotAuthorized;
              break;
            case 'network-request-failed':
              errorMessage = localizations.networkError;
              break;
            case 'missing-phone-number':
              errorMessage = localizations.phoneRequired;
              break;
            case 'captcha-check-failed':
              errorMessage = localizations.recaptchaFailed;
              break;
            default:
              errorMessage = e.message ?? localizations.failedSendOtpTryAgain;
          }

          _showCustomSnackBar(errorMessage, isError: true);
        },

        codeSent: (String verificationId, int? resendToken) {
          debugPrint('✅ OTP sent successfully');
          debugPrint('VerificationId: $verificationId');

          setState(() {
            _verificationId = verificationId;
            _otpSent = true;
            _resendToken = resendToken ?? 0;
            _canResend = false;
            _resendCountdown = 60;
          });

          _showCustomSnackBar(localizations.otpSent, isSuccess: true);
          _startResendCountdown();
        },

        codeAutoRetrievalTimeout: (String verificationId) {
          debugPrint('⏱️ Auto retrieval timeout');
          setState(() {
            _verificationId = verificationId;
          });
        },
      );
    } catch (e) {
      debugPrint('❗ Send OTP exception: $e');

      // Handle formatting errors specifically
      if (e.toString().contains('country code')) {
        _showCustomSnackBar(e.toString(), isError: true);
      } else {
        _showCustomSnackBar(localizations.errorSendingOtp, isError: true);
      }
    } finally {
      setState(() => _isLoading = false);
    }
  }

  // Updated text field for phone with better hint:
  Widget _buildPhoneField() {
    final localizations = AppLocalizations.of(context)!;
    return _buildTextFormField(
      controller: phoneController,
      validator: _validatePhone,
      hintText: localizations.enterPhoneWithCountryCode,
      prefixIcon: Icons.phone_outlined,
      keyboardType: TextInputType.phone,
    );
  }

  Future<void> _resendOtp() async {
    final localizations = AppLocalizations.of(context)!;
    if (!_canResend) return;

    setState(() => _isLoading = true);

    try {
      final formattedPhone = _formatPhoneNumber(phoneController.text.trim());

      await _auth.verifyPhoneNumber(
        phoneNumber: formattedPhone,
        verificationCompleted: (PhoneAuthCredential credential) async {
          setState(() => _phoneVerified = true);
          _showCustomSnackBar(localizations.phoneVerifiedAuto, isSuccess: true);
          await _auth.signOut();
          await _handleBackendSignup();
        },
        verificationFailed: (FirebaseAuthException e) {
          _showCustomSnackBar(localizations.failedResendOtp, isError: true);
        },
        codeSent: (String verificationId, int? resendToken) {
          setState(() {
            _verificationId = verificationId;
            _resendToken = resendToken ?? 0;
            _canResend = false;
            _resendCountdown = 30;
          });

          otpController.clear();
          _showCustomSnackBar(localizations.otpResent, isSuccess: true);
          _startResendCountdown();
        },
        codeAutoRetrievalTimeout: (String verificationId) {
          _verificationId = verificationId;
        },
        forceResendingToken: _resendToken,
        timeout: const Duration(seconds: 60),
      );
    } catch (e) {
      _showCustomSnackBar(localizations.failedResendOtp, isError: true);
    } finally {
      setState(() => _isLoading = false);
    }
  }

  // Backend API Functions
  Future<void> hitLicenseApiAndSave() async {
    final localizations = AppLocalizations.of(context)!;
    final uri = '${ApiConstants.baseUrl}https://webapp.xit.gr/service/license';
    try {
      final response = await http.get(Uri.parse(uri));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final prefs = await SharedPreferences.getInstance();
        await Future.wait([
          prefs.setString('token_type', data['token_type']),
          prefs.setInt('iat', data['iat']),
          prefs.setInt('expires_in', data['expires_in']),
          prefs.setString('jwt_token', data['jwt_token']),
        ]);
        debugPrint('✅ Token saved in SharedPreferences');
      } else {
        debugPrint('❌ License Error ${response.statusCode}: ${response.body}');
        _showCustomSnackBar(localizations.licenseFailed, isError: true);
        throw Exception('License API failed');
      }
    } catch (e) {
      debugPrint('❗ License Exception: $e');
      _showCustomSnackBar(localizations.networkError, isError: true);
      rethrow;
    }
  }

  Future<Map<String, dynamic>?> _getLicenseDetails() async {
    final prefs = await SharedPreferences.getInstance();
    final jwtToken = prefs.getString('jwt_token');
    if (jwtToken == null) return null;

    final uri =
        "${ApiConstants.baseUrl}https://license.xit.gr/wp-json/wp/v2/users/?slug=fanis2";
    try {
      final response = await http.get(
        Uri.parse(uri),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $jwtToken',
        },
      );

      if (response.statusCode == 200) {
        final List data = jsonDecode(response.body);
        if (data.isNotEmpty) {
          return {
            "company_url": data[0]["acf"]["company_url"],
            "appid": data[0]["acf"]["app_id"],
            "company_id": data[0]["acf"]["company_id"],
            "branch": data[0]["acf"]["branch"],
            "refid": data[0]["acf"]["refid"],
            "software_type": data[0]["acf"]["software_type"],
          };
        }
      } else {
        debugPrint('❌ License fetch failed: ${response.body}');
      }
    } catch (e) {
      debugPrint('❗ License fetch exception: $e');
    }
    return null;
  }

  Future<void> gettingClientID() async {
    final localizations = AppLocalizations.of(context)!;
    try {
      final license = await _getLicenseDetails();
      if (license == null) {
        _showCustomSnackBar(localizations.licenseFailed, isError: true);
        throw Exception('License check failed');
      }

      globalLiscence = license;
      final servicePath = license["software_type"] == "TESAE"
          ? "/pegasus/a_xit/connector.php"
          : "/s1services";

      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://${license["company_url"]}$servicePath",
      );

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          "service": "login",
          "username": 'fanis2',
          "password": '1234',
          "appId": "1001",
          "COMPANY": "1000",
          "BRANCH": "1000",
          "MODULE": "0",
          "REFID": "999",
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          final prefs = await SharedPreferences.getInstance();
          final clientID = data['clientID'];
          await Future.wait([
            prefs.setString('clientID', clientID),
            prefs.setString('company', data['companyinfo'].split('|')[0]),
            prefs.setString('company_url', license['company_url']),
            prefs.setString('software_type', license['software_type']),
          ]);
          debugPrint('✅ Client login successful');
        } else {
          _showCustomSnackBar(localizations.loginFailed, isError: true);
          throw Exception('Client login failed');
        }
      } else {
        _showCustomSnackBar(
          '${localizations.loginError} ${response.statusCode}',
          isError: true,
        );
        throw Exception('Login error');
      }
    } catch (e) {
      debugPrint('❗ Client login exception: $e');
      _showCustomSnackBar(localizations.connectionError, isError: true);
      rethrow;
    }
  }

  Future<bool> _checkUserExists() async {
    final localizations = AppLocalizations.of(context)!;
    setState(() => _isCheckingUser = true);
    try {
      final prefs = await SharedPreferences.getInstance();
      final clientID = prefs.getString('clientID');
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');

      if (clientID == null || companyUrl == null || softwareType == null) {
        _showCustomSnackBar(localizations.missingConfiguration, isError: true);
        return true;
      }

      final servicePath = softwareType == "TESAE"
          ? "/pegasus/a_xit/connector.php"
          : "/s1services";

      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://$companyUrl$servicePath",
      );

      final body = {
        "service": "getBrowserInfo",
        "clientID": clientID,
        "appId": "1001",
        "OBJECT": "CUSTOMER",
        "LIST": "",
        "VERSION": 2,
        "LIMIT": 1,
        "FILTERS": "CUSTOMER.CODE=${phoneController.text.trim()}",
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final totalCount = data['totalcount'] ?? 0;

        if (totalCount == 0) {
          return false; // User doesn't exist, proceed
        } else {
          _showCustomSnackBar(localizations.userAlreadyExists, isError: true);
          return true; // User exists, stop
        }
      } else {
        _showCustomSnackBar(localizations.failedCheckUser, isError: true);
        return true;
      }
    } catch (e) {
      debugPrint('❗ Check user exception: $e');
      _showCustomSnackBar(
        '${localizations.errorCheckingUser} ${e.toString()}',
        isError: true,
      );
      return true;
    } finally {
      setState(() => _isCheckingUser = false);
    }
  }

  Future<void> _signupCustomer() async {
    final localizations = AppLocalizations.of(context)!;
    try {
      final prefs = await SharedPreferences.getInstance();
      final clientID = prefs.getString('clientID');
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');

      if (clientID == null || companyUrl == null || softwareType == null) {
        _showCustomSnackBar(localizations.missingConfig, isError: true);
        throw Exception('Missing configuration');
      }

      final servicePath = softwareType == "TESAE"
          ? "/pegasus/a_xit/connector.php"
          : "/s1services";

      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://$companyUrl$servicePath",
      );

      final body = {
        "service": "setData",
        "clientID": clientID,
        "appId": "1001",
        "OBJECT": "CUSTOMER[FORM=WEB]",
        "KEY": "",
        "data": {
          "CUSTOMER": [
            {
              "CODE": phoneController.text.trim(),
              "NAME": fullNameController.text.trim(),
              "PHONE01": phoneController.text.trim(),
            },
          ],
        },
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          _showCustomSnackBar(localizations.accountCreated, isSuccess: true);
          _clearForm();

          // Navigate to SignIn after successful registration
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => SignInScreen()),
          );
        } else {
          _showCustomSnackBar(
            '${localizations.signupFailed}: ${data['error'] ?? localizations.unknownError}',
            isError: true,
          );
          throw Exception('Signup failed');
        }
      } else {
        _showCustomSnackBar(
          '${localizations.serverError}: ${response.statusCode}',
          isError: true,
        );
        throw Exception('Server error');
      }
    } catch (e) {
      debugPrint('❗ Signup exception: $e');
      _showCustomSnackBar(
        '${localizations.signupError} ${e.toString()}',
        isError: true,
      );
      rethrow;
    }
  }

  void _clearForm() {
    fullNameController.clear();
    phoneController.clear();
    passwordController.clear();
    otpController.clear();
    setState(() {
      _acceptTerms = false;
      _otpSent = false;
      _phoneVerified = false;
      _verificationId = null;
    });
  }

  // Main backend signup handler - Only called after phone verification
  Future<void> _handleBackendSignup() async {
    final localizations = AppLocalizations.of(context)!;
    setState(() => _isLoading = true);

    try {
      // Step 1: Get license and save tokens
      await hitLicenseApiAndSave();

      // Step 2: Get client ID
      await gettingClientID();

      // Step 3: Check if user already exists
      final userExists = await _checkUserExists();
      if (userExists) {
        return; // Stop if user exists
      }

      // Step 4: Create new user
      await _signupCustomer();
    } catch (e) {
      debugPrint('Backend signup failed: $e');
      _showCustomSnackBar(
        '${localizations.signupFailed} ${e.toString()}',
        isError: true,
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  // Main signup handler
  Future<void> _handleSignup() async {
    final localizations = AppLocalizations.of(context)!;
    // Validate form first
    if (!_formKey.currentState!.validate()) {
      _showCustomSnackBar(localizations.fixErrors, isError: true);
      return;
    }

    if (!_acceptTerms) {
      _showCustomSnackBar(localizations.acceptTermsError, isError: true);
      return;
    }

    // If OTP not sent yet, send OTP first
    if (!_otpSent) {
      await _sendOtp();
      return; // Stop here, user needs to enter OTP
    }

    // If OTP sent but not verified, verify OTP
    if (_otpSent && !_phoneVerified) {
      await _verifyOtp();
      return; // This will handle backend signup after verification
    }
  }

  @override
  Widget build(BuildContext context) {
    final localizations = AppLocalizations.of(context)!;
    final localizationService = Provider.of<LocalizationService>(context);

    return Scaffold(
      resizeToAvoidBottomInset: true,
      body: Stack(
        children: [
          // Background image
          Container(
            decoration: const BoxDecoration(
              image: DecorationImage(
                image: AssetImage("assets/images/auth.jpg"),
                fit: BoxFit.cover,
              ),
            ),
          ),
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Colors.black.withOpacity(0.3),
                  Colors.black.withOpacity(0.1),
                ],
              ),
            ),
          ),

          // Main content with SingleChildScrollView
          SafeArea(
            child: SingleChildScrollView(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
              ),
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  minHeight:
                      MediaQuery.of(context).size.height -
                      MediaQuery.of(context).padding.top,
                ),
                child: Column(
                  children: [
                    const SizedBox(height: 20),

                    // Logo
                    Center(
                      child: Image.asset(
                        'assets/images/app-logo.png',
                        height: 80,
                        width: 480,
                      ),
                    ),
                    const SizedBox(height: 15),

                    // Language Selection
                    Center(
                      child: Text(
                        localizations.chooseLanguage,
                        style: const TextStyle(
                          fontSize: 18,
                          color: Colors.white,
                          fontFamily: 'Poppins',
                          fontWeight: FontWeight.w500,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                    const SizedBox(height: 10),

                    Center(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          _buildLanguageOption('GR'),
                          _separator(),
                          _buildLanguageOption('en'),
                          _separator(),
                          _buildLanguageOption('ro'),
                        ],
                      ),
                    ),
                    const SizedBox(height: 15),

                    // White Card with form
                    Padding(
                      padding: const EdgeInsets.only(top: 90.0),
                      child: Container(
                        width: double.infinity,
                        margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(25),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.1),
                              blurRadius: 20,
                              offset: const Offset(0, 10),
                            ),
                          ],
                        ),
                        child: Form(
                          key: _formKey,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              // Title section
                              Column(
                                children: [
                                  Center(
                                    child: Text(
                                      localizations.signUp,
                                      style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 22,
                                        fontFamily: 'Poppins',
                                        color: Colors.black87,
                                      ),
                                      textAlign: TextAlign.center,
                                    ),
                                  ),
                                  const SizedBox(height: 6),
                                  Center(
                                    child: Text(
                                      _phoneVerified
                                          ? localizations.phoneVerifiedCreating
                                          : _otpSent
                                          ? localizations.enterSixDigitCode
                                          : localizations.createAccount,
                                      style: TextStyle(
                                        color: _phoneVerified
                                            ? Colors.green[600]
                                            : Colors.grey[600],
                                        fontSize: 11,
                                        fontFamily: 'Poppins',
                                      ),
                                      textAlign: TextAlign.center,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 16),

                              // Form fields section
                              if (!_otpSent) ...[
                                // Full Name field
                                _buildFieldLabel(localizations.fullName),
                                _buildTextFormField(
                                  controller: fullNameController,
                                  validator: _validateName,
                                  hintText: localizations.enterFullName,
                                  prefixIcon: Icons.person_outline,
                                ),
                                const SizedBox(height: 12),

                                // Phone Number field
                                _buildFieldLabel(localizations.phone),
                                _buildPhoneField(),
                              ] else if (!_phoneVerified) ...[
                                // OTP Verification Section
                                _buildFieldLabel(localizations.enterOtp),
                                _buildTextFormField(
                                  controller: otpController,
                                  validator: _validateOtp,
                                  hintText: localizations.enterSixDigitOtp,
                                  prefixIcon: Icons.security,
                                  keyboardType: TextInputType.number,
                                  inputFormatters: [
                                    FilteringTextInputFormatter.digitsOnly,
                                    LengthLimitingTextInputFormatter(6),
                                  ],
                                ),
                                const SizedBox(height: 10),
                                Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Flexible(
                                      child: Text(
                                        '${localizations.sentTo}: ${phoneController.text}',
                                        style: TextStyle(
                                          color: Colors.grey[600],
                                          fontSize: 11,
                                          fontFamily: 'Poppins',
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    TextButton(
                                      onPressed: _canResend ? _resendOtp : null,
                                      child: Text(
                                        _canResend
                                            ? localizations.resendOtp
                                            : '${localizations.resendIn} ${_resendCountdown}s',
                                        style: TextStyle(
                                          color: _canResend
                                              ? Colors.orange
                                              : Colors.grey,
                                          fontSize: 11,
                                          fontFamily: 'Poppins',
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ] else ...[
                                // Success message when phone verified
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: Colors.green[50],
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(
                                      color: Colors.green[200]!,
                                    ),
                                  ),
                                  child: Row(
                                    children: [
                                      Icon(
                                        Icons.check_circle,
                                        color: Colors.green[600],
                                        size: 18,
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          localizations.phoneVerifiedCreating,
                                          style: TextStyle(
                                            color: Colors.green[700],
                                            fontSize: 12,
                                            fontFamily: 'Poppins',
                                            fontWeight: FontWeight.w500,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],

                              const SizedBox(height: 10),

                              // Terms checkbox
                              Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Checkbox(
                                    value: _acceptTerms,
                                    onChanged: (val) {
                                      setState(() {
                                        _acceptTerms = val!;
                                      });
                                    },
                                    activeColor: const Color(0xFFEC7103),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                  ),
                                  Expanded(
                                    child: Padding(
                                      padding: const EdgeInsets.only(top: 10),
                                      child: RichText(
                                        text: TextSpan(
                                          style: const TextStyle(
                                            color: Colors.black87,
                                            fontFamily: 'Poppins',
                                            fontSize: 12,
                                          ),
                                          children: [
                                            TextSpan(
                                              text: '${localizations.iAccept} ',
                                            ),
                                            TextSpan(
                                              text: localizations.termsOfUse,
                                              style: const TextStyle(
                                                color: Color(0xFFEC7103),
                                                decoration:
                                                    TextDecoration.underline,
                                                fontWeight: FontWeight.w500,
                                              ),
                                            ),
                                            TextSpan(
                                              text: ' ${localizations.and} ',
                                            ),
                                            TextSpan(
                                              text: localizations.privacyPolicy,
                                              style: const TextStyle(
                                                color: Color(0xFFEC7103),
                                                decoration:
                                                    TextDecoration.underline,
                                                fontWeight: FontWeight.w500,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),

                              const SizedBox(height: 15),

                              // Bottom section with sign in text and button
                              Column(
                                children: [
                                  // Sign in text
                                  Center(
                                    child: RichText(
                                      text: TextSpan(
                                        style: const TextStyle(
                                          color: Colors.black87,
                                          fontFamily: 'Poppins',
                                          fontSize: 11,
                                        ),
                                        children: [
                                          TextSpan(
                                            text:
                                                '${localizations.haveAccount} ',
                                          ),
                                          TextSpan(
                                            text: localizations.signIn,
                                            style: const TextStyle(
                                              color: Color(0xFFEC7103),
                                              fontWeight: FontWeight.bold,
                                              decoration:
                                                  TextDecoration.underline,
                                              fontSize: 13,
                                            ),
                                            recognizer: TapGestureRecognizer()
                                              ..onTap = () {
                                                Navigator.push(
                                                  context,
                                                  MaterialPageRoute(
                                                    builder: (context) =>
                                                        SignInScreen(),
                                                  ),
                                                );
                                              },
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                  const SizedBox(height: 15),

                                  // Sign Up button
                                  Center(
                                    child: SizedBox(
                                      width: double.infinity,
                                      child: ElevatedButton(
                                        onPressed:
                                            (_isLoading || _isCheckingUser)
                                            ? null
                                            : _handleSignup,
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: const Color(
                                            0xFFEC7103,
                                          ),
                                          foregroundColor: Colors.white,
                                          padding: const EdgeInsets.symmetric(
                                            vertical: 14,
                                          ),
                                          shape: RoundedRectangleBorder(
                                            borderRadius: BorderRadius.circular(
                                              25,
                                            ),
                                          ),
                                          elevation: 2,
                                        ),
                                        child:
                                            (_isLoading ||
                                                _isCheckingUser ||
                                                _isVerifyingOtp)
                                            ? Row(
                                                mainAxisAlignment:
                                                    MainAxisAlignment.center,
                                                children: [
                                                  const SizedBox(
                                                    width: 18,
                                                    height: 18,
                                                    child: CircularProgressIndicator(
                                                      strokeWidth: 2,
                                                      valueColor:
                                                          AlwaysStoppedAnimation<
                                                            Color
                                                          >(Colors.white),
                                                    ),
                                                  ),
                                                  const SizedBox(width: 10),
                                                  Text(
                                                    _isCheckingUser
                                                        ? localizations
                                                              .checkingUser
                                                        : _isVerifyingOtp
                                                        ? localizations
                                                              .verifyingOtp
                                                        : _phoneVerified
                                                        ? localizations
                                                              .creatingAccount
                                                        : localizations
                                                              .sendingOtp,
                                                    style: const TextStyle(
                                                      fontWeight:
                                                          FontWeight.w600,
                                                      fontFamily: 'Poppins',
                                                      fontSize: 15,
                                                    ),
                                                  ),
                                                ],
                                              )
                                            : Text(
                                                !_otpSent
                                                    ? localizations.sendOtp
                                                    : !_phoneVerified
                                                    ? localizations.verifyOtp
                                                    : localizations
                                                          .createAccount,
                                                style: const TextStyle(
                                                  fontWeight: FontWeight.w600,
                                                  fontFamily: 'Poppins',
                                                  fontSize: 15,
                                                ),
                                              ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFieldLabel(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8, left: 4),
      child: Text(
        text,
        style: const TextStyle(
          fontWeight: FontWeight.w600,
          fontFamily: 'Poppins',
          fontSize: 14,
          color: Colors.black87,
        ),
        textAlign: TextAlign.left,
      ),
    );
  }

  Widget _buildTextFormField({
    required TextEditingController controller,
    required String? Function(String?) validator,
    required String hintText,
    required IconData prefixIcon,
    TextInputType keyboardType = TextInputType.text,
    List<TextInputFormatter>? inputFormatters,
    bool obscureText = false,
    Widget? suffixIcon,
  }) {
    return TextFormField(
      controller: controller,
      validator: validator,
      keyboardType: keyboardType,
      inputFormatters: inputFormatters,
      obscureText: obscureText,
      style: const TextStyle(fontSize: 14, fontFamily: 'Poppins'),
      decoration: InputDecoration(
        filled: true,
        fillColor: Colors.grey[100],
        hintText: hintText,
        hintStyle: TextStyle(color: Colors.grey[500], fontSize: 13),
        prefixIcon: Icon(prefixIcon, color: Colors.grey[600], size: 20),
        suffixIcon: suffixIcon,
        contentPadding: const EdgeInsets.symmetric(
          vertical: 16,
          horizontal: 16,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.orange.shade400, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.red, width: 1),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.red, width: 2),
        ),
        errorStyle: const TextStyle(fontSize: 12, fontFamily: 'Poppins'),
      ),
    );
  }

  Widget _buildLanguageOption(String langCode) {
    bool selected = langCode == _selectedLanguage;
    String displayText = langCode.toUpperCase();

    return GestureDetector(
      onTap: () async {
        final localizationService = Provider.of<LocalizationService>(
          context,
          listen: false,
        );
        await localizationService.changeLanguage(langCode);

        setState(() {
          _selectedLanguage = langCode;
        });

        // Show feedback
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(AppLocalizations.of(context)!.languageChanged),
            backgroundColor: Colors.orange,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
            duration: Duration(seconds: 1),
          ),
        );
      },
      child: Text(
        displayText,
        style: TextStyle(
          color: selected
              ? const Color(0xFFEC7103) // Orange for active
              : Colors.white, // White for inactive
          fontWeight: selected ? FontWeight.bold : FontWeight.normal,
          fontSize: 16,
          fontFamily: 'Jura',
        ),
      ),
    );
  }

  Widget _separator() => Container(
    height: 20,
    width: 1,
    margin: const EdgeInsets.symmetric(horizontal: 12),
    color: const Color.fromARGB(176, 54, 54, 54),
  );
}
