import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:loyalty_app/Auth/SignIn.dart';
import 'package:loyalty_app/utils/api_constants.dart';
import 'package:permission_handler/permission_handler.dart';
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:loyalty_app/Services/language_service.dart';
import 'package:provider/provider.dart';
import 'package:telephony_fix/telephony.dart';

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

  final _formKey = GlobalKey<FormState>();

  bool _acceptTerms = false;
  final bool _obscurePassword = true;
  String _selectedLanguage = 'el';
  bool _isLoading = false;
  bool _isCheckingUser = false;

  final Telephony telephony = Telephony.instance;

  @override
  void initState() {
    super.initState();

    _getPhoneNumber();
  }

  @override
  void dispose() {
    fullNameController.dispose();
    phoneController.dispose();
    passwordController.dispose();
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
    if (value == null || value.isEmpty) {
      return 'Name is required';
    }
    if (value.length < 2) {
      return 'Name must be at least 2 characters';
    }
    if (!RegExp(r'^[a-zA-Z\s]+$').hasMatch(value)) {
      return 'Name can only contain letters and spaces';
    }
    return null;
  }

  String? _validateEmail(String? value) {
    if (value == null || value.isEmpty) {
      return 'Email is required';
    }
    if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
      return 'Please enter a valid email address';
    }
    return null;
  }

  String? _validatePhone(String? value) {
    if (value == null || value.isEmpty) {
      return 'Phone number is required';
    }
    if (value.length < 10) {
      return 'Phone number must be at least 10 digits';
    }
    if (!RegExp(r'^[0-9]+$').hasMatch(value)) {
      return 'Phone number can only contain digits';
    }
    return null;
  }

  String? _validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password is required';
    }
    if (value.length < 6) {
      return 'Password must be at least 6 characters';
    }
    return null;
  }

  // Phone number auto-fill function
  Future<void> _getPhoneNumber() async {
    try {
      var permissionStatus = await Permission.phone.request();

      if (permissionStatus.isGranted) {
        await _tryAlternativePhoneDetection();
      } else {
        _showCustomSnackBar(
          'Phone permission helps verify your device. You can enable it in settings.',
        );
      }
    } catch (e) {
      debugPrint('Error getting phone permission: $e');
    }
  }

  Future<void> _tryAlternativePhoneDetection() async {
    try {
      String? simOperator = await telephony.simOperatorName;
      String? simState = telephony.simState.toString();

      debugPrint('SIM Operator: $simOperator');
      debugPrint('SIM State: $simState');

      if (simOperator != null && simOperator.isNotEmpty) {
        _showCustomSnackBar(
          'SIM card detected ($simOperator). Please enter your phone number manually.',
          isSuccess: true,
        );
      }
    } catch (e) {
      debugPrint('Alternative phone detection failed: $e');
    }
  }

  Future<bool> _initialize() async {
    try {
      final localizationService = Provider.of<LocalizationService>(
        context,
        listen: false,
      );
      setState(
        () =>
            _selectedLanguage = localizationService.currentLocale.languageCode,
      );

      // await hitLicenseApiAndSave();
      // await gettingClientID();
      await _signupCustomer();
      return true; // Success
    } catch (e) {
      return false; // Failure
    }
  }

  Future<void> hitLicenseApiAndSave() async {
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
        _showCustomSnackBar('Failed to get license token', isError: true);
      }
    } catch (e) {
      debugPrint('❗ License Exception: $e');
      _showCustomSnackBar('Network error occurred', isError: true);
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

  // First API: Check if user exists
  Future<bool> _checkUserExists() async {
    setState(() => _isCheckingUser = true);

    try {
      final prefs = await SharedPreferences.getInstance();
      final clientID = prefs.getString('clientID');
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');

      if (clientID == null || companyUrl == null || softwareType == null) {
        _showCustomSnackBar(
          'Missing configuration. Please restart the app.',
          isError: true,
        );
        return true; // Treat as exists to prevent signup
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
          // User doesn't exist, proceed\]
          return false;
        } else {
          // User already exists
          _showCustomSnackBar(
            'User with this phone number already exists!',
            isError: true,
          );
          return true;
        }

        // API call succeeded but returned failure
        _showCustomSnackBar('Failed to check user existence', isError: true);
        return true; // Default to exists to prevent signup
      } else {
        _showCustomSnackBar('Failed to check user existence', isError: true);
        return true; // Default to exists to prevent signup
      }
    } catch (e) {
      debugPrint('❗ Check user exception: $e');
      _showCustomSnackBar(
        'Error checking user: ${e.toString()}',
        isError: true,
      );
      return true; // Default to exists to prevent signup
    } finally {
      setState(() => _isCheckingUser = false);
    }
  }

  // Second API: Sign up user
  Future<void> _signupCustomer() async {
    setState(() => _isLoading = true);

    try {
      final prefs = await SharedPreferences.getInstance();
      final clientID = prefs.getString('clientID');
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');

      if (clientID == null || companyUrl == null || softwareType == null) {
        _showCustomSnackBar('Missing configuration', isError: true);
        return;
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
          _showCustomSnackBar('Account created successfully!', isSuccess: true);

          // Navigate to next screen or clear form
          _clearForm();
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => SignInScreen()),
          );
        } else {
          _showCustomSnackBar(
            'Signup failed: ${data['error'] ?? 'Unknown error'}',
            isError: true,
          );
        }
      } else {
        _showCustomSnackBar(
          'Server error: ${response.statusCode}',
          isError: true,
        );
      }
    } catch (e) {
      debugPrint('❗ Signup exception: $e');
      _showCustomSnackBar('Signup error: ${e.toString()}', isError: true);
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _clearForm() {
    fullNameController.clear();
    phoneController.clear();
    passwordController.clear();
    setState(() {
      _acceptTerms = false;
    });
  }

  // Handle signup process
  Future<void> _handleSignup() async {
    // Validate form first
    if (!_formKey.currentState!.validate()) {
      _showCustomSnackBar('Please fix the errors above', isError: true);
      return;
    }

    if (!_acceptTerms) {
      _showCustomSnackBar('Please accept the terms of use', isError: true);
      return;
    }

    // Show loading indicator
    setState(() => _isLoading = true);

    try {
      // First check license and get client ID
      await hitLicenseApiAndSave();
      await gettingClientID();

      // Check if user exists
      final userExists = await _checkUserExists();
      if (userExists) {
        // User exists - don't proceed further
        return;
      }

      // Only proceed with signup if user doesn't exist (totalCount == 0)
      await _initialize();
      _showCustomSnackBar('Signup successful!');
    } catch (e) {
      _showCustomSnackBar('Signup failed: ${e.toString()}', isError: true);
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> gettingClientID() async {
    setState(() => _isLoading = true);

    try {
      final license = await _getLicenseDetails();
      if (license == null) {
        _showCustomSnackBar('License check failed', isError: true);
        return;
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
          _showCustomSnackBar(
            'Login failed: Invalid credentials',
            isError: true,
          );
        }
      } else {
        _showCustomSnackBar(
          'Login error: ${response.statusCode}',
          isError: true,
        );
      }
    } catch (e) {
      debugPrint('❗ Client login exception: $e');
      _showCustomSnackBar('Connection error', isError: true);
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Map<String, String> get languageMap {
    return {'el': 'GR', 'en': 'EN', 'ro': 'RO'};
  }

  @override
  Widget build(BuildContext context) {
    final localizations = AppLocalizations.of(context)!;
    final localizationService = Provider.of<LocalizationService>(context);

    return Scaffold(
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
          SafeArea(
            child: Column(
              children: [
                const SizedBox(height: 20),
                // Logo
                Center(
                  child: Image.asset(
                    'assets/images/app-logo.png',
                    height: 100,
                    width: 600,
                  ),
                ),
                const SizedBox(height: 20),

                // Language Selection
                Center(
                  child: Text(
                    localizations.chooseLanguage,
                    style: const TextStyle(
                      fontSize: 20,
                      color: Colors.white,
                      fontFamily: 'Poppins',
                      fontWeight: FontWeight.w500,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
                const SizedBox(height: 15),
                Center(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      _buildLanguageOption('el'),
                      _separator(),
                      _buildLanguageOption('en'),
                      _separator(),
                      _buildLanguageOption('ro'),
                    ],
                  ),
                ),

                const SizedBox(height: 20),

                // White Card with form - Made larger and non-scrollable
                Expanded(
                  child: Container(
                    width: double.infinity,
                    margin: const EdgeInsets.fromLTRB(
                      16,
                      0,
                      16,
                      8,
                    ), // Reduced bottom margin from 16 to 8
                    padding: const EdgeInsets.all(
                      24,
                    ), // Increased padding from 20 to 24
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
                        children: [
                          // Top section with title and description
                          Column(
                            children: [
                              // Title
                              const Center(
                                child: Text(
                                  'SIGN UP',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 24,
                                    fontFamily: 'Poppins',
                                    color: Colors.black87,
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Center(
                                child: Text(
                                  'Create your account and start earning points',
                                  style: TextStyle(
                                    color: Colors.grey[600],
                                    fontSize: 12,
                                    fontFamily: 'Poppins',
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                              ),
                            ],
                          ),

                          const SizedBox(height: 20), // Add space after title
                          // Form fields section - Remove the Expanded wrapper
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Full Name field
                              _buildFieldLabel(localizations.Name),
                              _buildTextFormField(
                                controller: fullNameController,
                                validator: _validateName,
                                hintText: 'Enter your full name',
                                prefixIcon: Icons.person_outline,
                              ),

                              const SizedBox(height: 16),

                              // Phone Number field
                              Row(
                                children: [
                                  Expanded(
                                    child: _buildFieldLabel(
                                      localizations.phone,
                                    ),
                                  ),
                                  TextButton.icon(
                                    onPressed: _getPhoneNumber,
                                    icon: const Icon(
                                      Icons.phone_android,
                                      size: 16,
                                      color: Colors.orange,
                                    ),
                                    label: const Text(
                                      'Detect SIM',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: Colors.orange,
                                        fontFamily: 'Poppins',
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              _buildTextFormField(
                                controller: phoneController,
                                validator: _validatePhone,
                                hintText: 'Enter your phone number',
                                prefixIcon: Icons.phone_outlined,
                                keyboardType: TextInputType.phone,
                                inputFormatters: [
                                  FilteringTextInputFormatter.digitsOnly,
                                ],
                              ),

                              const SizedBox(
                                height: 12,
                              ), // Reduced gap for terms
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
                                      padding: const EdgeInsets.only(top: 12),
                                      child: RichText(
                                        text: TextSpan(
                                          style: const TextStyle(
                                            color: Colors.black87,
                                            fontFamily: 'Poppins',
                                            fontSize: 13,
                                          ),
                                          children: [
                                            const TextSpan(
                                              text: 'I accept the ',
                                            ),
                                            const TextSpan(
                                              text: 'Terms of Use',
                                              style: TextStyle(
                                                color: Color(0xFFEC7103),
                                                decoration:
                                                    TextDecoration.underline,
                                                fontWeight: FontWeight.w500,
                                              ),
                                            ),
                                            const TextSpan(text: ' and '),
                                            const TextSpan(
                                              text: 'Privacy Policy',
                                              style: TextStyle(
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
                            ],
                          ),

                          const SizedBox(
                            height: 20,
                          ), // Add space before bottom section
                          // Bottom section with sign in text and button
                          Column(
                            children: [
                              // Sign in text

                              // Inside your build method
                              Center(
                                child: RichText(
                                  text: TextSpan(
                                    style: TextStyle(
                                      color: Colors.black87,
                                      fontFamily: 'Poppins',
                                      fontSize: 12,
                                    ),
                                    children: [
                                      TextSpan(text: localizations.noAccount),
                                      TextSpan(
                                        text: localizations.signUp,
                                        style: TextStyle(
                                          color: Color(0xFFEC7103),
                                          fontWeight: FontWeight.bold,
                                          decoration: TextDecoration.underline,
                                          fontSize: 14,
                                        ),
                                        recognizer: TapGestureRecognizer()
                                          ..onTap = () {
                                            Navigator.push(
                                              context,
                                              MaterialPageRoute(
                                                builder: (context) =>
                                                    SignInScreen(),
                                              ), // yahan apni signup wali screen ka widget lagao
                                            );
                                          },
                                      ),
                                    ],
                                  ),
                                ),
                              ),

                              const SizedBox(height: 20),

                              // Sign Up button - Already centered
                              Center(
                                child: SizedBox(
                                  width: double.infinity,
                                  child: ElevatedButton(
                                    onPressed: (_isLoading || _isCheckingUser)
                                        ? null
                                        : _handleSignup,
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: const Color(0xFFEC7103),
                                      foregroundColor: Colors.white,
                                      padding: const EdgeInsets.symmetric(
                                        vertical: 16,
                                      ),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(25),
                                      ),
                                      elevation: 2,
                                    ),
                                    child: (_isLoading || _isCheckingUser)
                                        ? Row(
                                            mainAxisAlignment:
                                                MainAxisAlignment.center,
                                            children: [
                                              const SizedBox(
                                                width: 20,
                                                height: 20,
                                                child: CircularProgressIndicator(
                                                  strokeWidth: 2,
                                                  valueColor:
                                                      AlwaysStoppedAnimation<
                                                        Color
                                                      >(Colors.white),
                                                ),
                                              ),
                                              const SizedBox(width: 12),
                                              Text(
                                                _isCheckingUser
                                                    ? 'Checking User...'
                                                    : 'Set up ',
                                                style: const TextStyle(
                                                  fontWeight: FontWeight.w600,
                                                  fontFamily: 'Poppins',
                                                  fontSize: 16,
                                                ),
                                                textAlign: TextAlign.center,
                                              ),
                                            ],
                                          )
                                        : Text(
                                            localizations.signInButton,
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w600,
                                              fontFamily: 'Poppins',
                                              fontSize: 16,
                                            ),
                                            textAlign: TextAlign.center,
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
    String displayText = languageMap[langCode] ?? langCode.toUpperCase();

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
