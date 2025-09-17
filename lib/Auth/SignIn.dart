import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:loyalty_app/Auth/TermsConditionsScreen.dart';
import 'package:loyalty_app/Auth/signup2.dart';
import 'package:loyalty_app/screen/MainScreen.dart';
import 'package:loyalty_app/utils/api_constants.dart';
import 'package:permission_handler/permission_handler.dart';
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:provider/provider.dart';
import 'package:loyalty_app/Services/language_service.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

// 👇 Global license data usable everywhere
Map<String, dynamic>? globalLiscence;

class SignInScreen extends StatefulWidget {
  const SignInScreen({super.key});

  @override
  State<SignInScreen> createState() => _SignInScreenState();
}

class _SignInScreenState extends State<SignInScreen> {
  final TextEditingController phoneController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _rememberMe = false;
  bool _isLoading = false;
  bool _isCheckingUser = false;
  String? _fcmToken;

  // Language mapping for display
  final Map<String, String> languageCodeMap = {
    'GR': 'el',
    'EN': 'en',
    'RO': 'ro',
  };

  final Map<String, String> displayLanguageMap = {
    'el': 'GR',
    'en': 'EN',
    'ro': 'RO',
  };

  @override
  void initState() {
    super.initState();
    _getPhoneNumber();
    _getFCMToken();
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

  // Phone number auto-fill function
  Future<void> _getPhoneNumber() async {
    try {
      var permissionStatus = await Permission.phone.request();

      if (permissionStatus.isGranted) {
        // Phone permission granted
      } else {
        final localizations = AppLocalizations.of(context)!;
        _showCustomSnackBar(localizations.phonePermissionMessage);
      }
    } catch (e) {
      debugPrint('Error getting phone permission: $e');
    }
  }

  Future<void> _getFCMToken() async {
    try {
      String? token = await FirebaseMessaging.instance.getToken();
      if (token != null) {
        setState(() {
          _fcmToken = token;
        });

        // Save token locally
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('fcm_token', token);

        debugPrint('✅ FCM Token signin se lya he : $token');
      }
    } catch (e) {
      debugPrint('❗ FCM Token Error: $e');
    }
  }

  // Send token to backend
  Future<void> _sendTokenToBackend(String token) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final clientID = prefs.getString('clientID');
      final phone = prefs.getString('PHONE');

      if (clientID == null || globalLiscence == null) return;

      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://${globalLiscence!['company_url']}/s1services",
      );

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          "service": "updateNotificationToken",
          "clientID": clientID,
          "appId": "1001",
          "phone": phone,
          "fcm_token": token,
        }),
      );

      if (response.statusCode == 200) {
        debugPrint('✅ FCM Token sent to backend successfully');
      } else {
        debugPrint('❌ Failed to send FCM token: ${response.statusCode}');
      }
    } catch (e) {
      debugPrint('❗ Error sending FCM token: $e');
    }
  }

  // Form validation
  String? _validatePhone(String? value) {
    final localizations = AppLocalizations.of(context)!;

    if (value == null || value.isEmpty) {
      return localizations.phoneRequired;
    }
    if (value.length < 10) {
      return localizations.phoneMinLength;
    }
    if (!RegExp(r'^[0-9]+$').hasMatch(value)) {
      return localizations.phoneDigitsOnly;
    }
    return null;
  }

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
      }
    } catch (e) {
      debugPrint('❗ License Exception: $e');
      _showCustomSnackBar(localizations.networkError, isError: true);
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

  Future<void> _checkMemberAndSaveTrdr({
    required String clientID,
    required String phone,
  }) async {
    final localizations = AppLocalizations.of(context)!;

    try {
      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://${globalLiscence!['company_url']}/s1services",
      );

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          "service": "getBrowserInfo",
          "clientID": clientID,
          "appId": "1001",
          "OBJECT": "CUSTOMER",
          "LIST": "",
          "VERSION": 2,
          "LIMIT": 1,
          "FILTERS": "CUSTOMER.PHONE01=$phone",
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final totalCount = data['totalcount'] ?? 0;

        if (totalCount == 0) {
          _showCustomSnackBar(localizations.userNotExist, isError: true);
        } else {
          final rows = data['rows'];
          final String zoomInfo = rows[0][0]; // "CUSTOMER;24360"
          final String trdr = zoomInfo.split(';')[1];
          final String name = rows[0][3]; // "malik"
          final String phone = rows[0][2]; // "1234567890"
          final String upddate = data['upddate'] ?? "";

          // 🔐 Save to SharedPreferences
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('TRDR', trdr);
          await prefs.setString('NAME', name);
          await prefs.setString('PHONE', phone);
          await prefs.setString('UPDDATE', upddate);

          debugPrint('✅ TRDR saved: $trdr');
          debugPrint('✅ NAME saved: $name');
          debugPrint('✅ PHONE saved: $phone');
          debugPrint('✅ UPDDATE saved: $upddate');

          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => MainScreen()),
          );
        }
      } else {
        _showCustomSnackBar(localizations.serverError, isError: true);
      }
    } catch (e) {
      debugPrint('❗ Error in _checkMemberAndSaveTrdr: $e');
      _showCustomSnackBar(localizations.connectionError, isError: true);
    }
  }

  // Check if user exists and sign in
  Future<void> _handleSignIn() async {
    final localizations = AppLocalizations.of(context)!;

    if (!_formKey.currentState!.validate()) {
      _showCustomSnackBar(localizations.phoneRequired, isError: true);
      return;
    }

    if (!_rememberMe) {
      _showCustomSnackBar(localizations.acceptTermsError, isError: true);
      return;
    }

    setState(() => _isCheckingUser = true);

    await hitLicenseApiAndSave();
    await gettingClientID();
    try {
      final prefs = await SharedPreferences.getInstance();
      final clientID = prefs.getString('clientID');

      if (clientID == null) {
        _showCustomSnackBar(localizations.configurationError, isError: true);
        return;
      }
      await _checkMemberAndSaveTrdr(
        clientID: clientID,
        phone: phoneController.text.trim(),
      );
    } catch (e) {
      debugPrint('❗ Sign in exception: $e');
      _showCustomSnackBar(localizations.connectionError, isError: true);
    } finally {
      setState(() => _isCheckingUser = false);
    }
  }

  Future<void> gettingClientID() async {
    final localizations = AppLocalizations.of(context)!;
    setState(() => _isLoading = true);

    try {
      final license = await _getLicenseDetails();
      if (license == null) {
        _showCustomSnackBar(localizations.licenseFailed, isError: true);
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
          _showCustomSnackBar(localizations.loginFailed, isError: true);
        }
      } else {
        _showCustomSnackBar(localizations.serverError, isError: true);
      }
    } catch (e) {
      debugPrint('❗ Client login exception: $e');
      _showCustomSnackBar(localizations.connectionError, isError: true);
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<LocalizationService>(
      builder: (context, localizationService, child) {
        final localizations = AppLocalizations.of(context)!;
        final currentDisplayLanguage =
            displayLanguageMap[localizationService.currentLocale.languageCode] ??
                'GR';

        return Scaffold(
          body: Stack(
            children: [
              // Background image with blur
              Container(
                decoration: const BoxDecoration(
                  image: DecorationImage(
                    image: AssetImage("assets/images/auth.jpg"),
                    fit: BoxFit.cover,
                  ),
                ),
              ),
              Container(color: Colors.black.withOpacity(0.3)),
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
                    const SizedBox(height: 40),

                    // Language Selection - Centered
                    Center(
                      child: Text(
                        localizations.chooseLanguage,
                        style: TextStyle(
                          fontSize: 20,
                          color: Colors.white,
                          fontFamily: 'Poppins',
                        ),
                      ),
                    ),
                    const SizedBox(height: 10),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        _buildLanguageOption(
                          'GR',
                          localizationService,
                          currentDisplayLanguage,
                        ),
                        _separator(),
                        _buildLanguageOption(
                          'EN',
                          localizationService,
                          currentDisplayLanguage,
                        ),
                        _separator(),
                        _buildLanguageOption(
                          'RO',
                          localizationService,
                          currentDisplayLanguage,
                        ),
                      ],
                    ),

                    Spacer(),

                    // White Card
                    Container(
                      width: double.infinity,
                      margin: const EdgeInsets.symmetric(horizontal: 36),
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(30),
                      ),
                      child: Form(
                        key: _formKey,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Center(
                              child: Text(
                                localizations.signIn,
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 22,
                                  fontFamily: 'Poppins',
                                ),
                              ),
                            ),
                            const SizedBox(height: 10),
                            Center(
                              child: Text(
                                localizations.signInSubtitle,
                                style: TextStyle(
                                  color: Colors.grey[700],
                                  fontSize: 11,
                                  fontFamily: 'Poppins',
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ),
                            const SizedBox(height: 10),

                            // Phone field
                            const SizedBox(height: 6),
                            TextFormField(
                              controller: phoneController,
                              validator: _validatePhone,
                              keyboardType: TextInputType.phone,
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                              decoration: InputDecoration(
                                filled: true,
                                fillColor: Colors.grey[200],
                                hintText: localizations.enterPhone,
                                hintStyle: TextStyle(
                                  color: Colors.grey[500],
                                  fontSize: 13,
                                ),
                                prefixIcon: Icon(
                                  Icons.phone_outlined,
                                  color: Colors.grey[600],
                                  size: 20,
                                ),
                                contentPadding: EdgeInsets.symmetric(
                                  vertical: 10,
                                  horizontal: 12,
                                ),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  borderSide: BorderSide.none,
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  borderSide: BorderSide(
                                    color: Colors.grey[300]!,
                                  ),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  borderSide: BorderSide(
                                    color: Colors.orange.shade400,
                                    width: 2,
                                  ),
                                ),
                                errorBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  borderSide: const BorderSide(
                                    color: Colors.red,
                                    width: 1,
                                  ),
                                ),
                              ),
                              style: TextStyle(
                                fontSize: 14,
                                fontFamily: 'Poppins',
                              ),
                            ),

                            const SizedBox(height: 12),
                            // Terms checkbox
                            Row(
                              children: [
                                Checkbox(
                                  value: _rememberMe,
                                  onChanged: (val) {
                                    setState(() {
                                      _rememberMe = val!;
                                    });
                                  },
                                  activeColor: Color(0xFFEC7103),
                                ),
                                Expanded(
                                  child: RichText(
                                    text: TextSpan(
                                      style: TextStyle(
                                        color: Colors.black87,
                                        fontFamily: 'Poppins',
                                      ),
                                      children: [
                                        TextSpan(text: '${localizations.acceptTerms} '),
                                        TextSpan(
                                          text: localizations.termsOfUse,
                                          style: TextStyle(
                                            color: Color(0xFFEC7103),
                                            decoration:
                                                TextDecoration.underline,
                                          ),
                                          recognizer: TapGestureRecognizer()
                                            ..onTap = () {
                                              Navigator.push(
                                                context,
                                                MaterialPageRoute(
                                                  builder: (context) =>
                                                      TermsConditionsScreen(),
                                                ),
                                              );
                                            },
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),

                            // Sign up text
                            Center(
                              child: RichText(
                                text: TextSpan(
                                  style: TextStyle(
                                    color: Colors.black87,
                                    fontFamily: 'Poppins',
                                    fontSize: 12,
                                  ),
                                  children: [
                                    TextSpan(text: '${localizations.noAccount} '),
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
                                                  SignUpScreen2(),
                                            ),
                                          );
                                        },
                                    ),
                                  ],
                                ),
                              ),
                            ),

                            const SizedBox(height: 30),

                            // Sign In button - Centered
                            Center(
                              child: SizedBox(
                                width: double.infinity,
                                child: ElevatedButton(
                                  onPressed: (_isLoading || _isCheckingUser)
                                      ? null
                                      : _handleSignIn,
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
                                                        Color>(Colors.white),
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            Text(
                                              _isCheckingUser
                                                  ? localizations.checkingUser
                                                  : localizations.signingIn,
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
                      ),
                    ),

                    const SizedBox(height: 30),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildLanguageOption(
    String displayLang,
    LocalizationService localizationService,
    String currentDisplayLanguage,
  ) {
    bool selected = displayLang == currentDisplayLanguage;
    return GestureDetector(
      onTap: () {
        // Convert display language to language code and change language
        final languageCode = languageCodeMap[displayLang] ?? 'el';
        localizationService.changeLanguage(languageCode);
      },
      child: Text(
        displayLang,
        style: TextStyle(
          color: selected
              ? Color(0xFFEC7103)
              : Colors.white,
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
        color: Colors.white.withOpacity(0.6),
      );
}