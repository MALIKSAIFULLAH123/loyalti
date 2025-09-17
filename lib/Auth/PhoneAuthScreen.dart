// import 'package:flutter/material.dart';
// import 'package:flutter/services.dart';
// import 'package:firebase_auth/firebase_auth.dart';
// import 'package:firebase_messaging/firebase_messaging.dart';
// import 'package:loyalty_app/Auth/OTPVerificationScreen.dart';
// import 'package:provider/provider.dart';
// import 'package:loyalty_app/Services/language_service.dart';
// import 'package:http/http.dart' as http;
// import 'dart:convert';

// class PhoneAuthScreen extends StatefulWidget {
//   const PhoneAuthScreen({super.key});

//   @override
//   State<PhoneAuthScreen> createState() => _PhoneAuthScreenState();
// }

// class _PhoneAuthScreenState extends State<PhoneAuthScreen> {
//   final TextEditingController phoneController = TextEditingController();
//   final _formKey = GlobalKey<FormState>();
//   final FirebaseAuth _auth = FirebaseAuth.instance;
//   final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  
//   bool _isLoading = false;
//   String _selectedLanguage = 'el';
//   String _countryCode = '+92'; // Default Pakistan code

//   @override
//   void initState() {
//     super.initState();
//     _initializeLanguage();
//   }

//   void _initializeLanguage() {
//     final localizationService = Provider.of<LocalizationService>(
//       context,
//       listen: false,
//     );
//     setState(() {
//       _selectedLanguage = localizationService.currentLocale.languageCode;
//     });
//   }

//   @override
//   void dispose() {
//     phoneController.dispose();
//     super.dispose();
//   }

//   // Show custom snackbar at top with app color
//   void _showCustomSnackBar(
//     String message, {
//     bool isError = false,
//     bool isSuccess = false,
//   }) {
//     final overlay = Overlay.of(context);
//     late OverlayEntry overlayEntry;

//     overlayEntry = OverlayEntry(
//       builder: (context) => Positioned(
//         top: MediaQuery.of(context).padding.top + 10,
//         left: 20,
//         right: 20,
//         child: Material(
//           color: Colors.transparent,
//           child: Container(
//             padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
//             decoration: BoxDecoration(
//               color: isError
//                   ? Colors.red.shade600
//                   : isSuccess
//                       ? Colors.green.shade600
//                       : Colors.orange.shade600,
//               borderRadius: BorderRadius.circular(12),
//               boxShadow: [
//                 BoxShadow(
//                   color: Colors.black.withOpacity(0.2),
//                   blurRadius: 8,
//                   offset: const Offset(0, 2),
//                 ),
//               ],
//             ),
//             child: Row(
//               children: [
//                 Icon(
//                   isError
//                       ? Icons.error_outline
//                       : isSuccess
//                           ? Icons.check_circle_outline
//                           : Icons.info_outline,
//                   color: Colors.white,
//                   size: 20,
//                 ),
//                 const SizedBox(width: 8),
//                 Expanded(
//                   child: Text(
//                     message,
//                     style: const TextStyle(
//                       color: Colors.white,
//                       fontWeight: FontWeight.w500,
//                       fontFamily: 'Poppins',
//                     ),
//                   ),
//                 ),
//               ],
//             ),
//           ),
//         ),
//       ),
//     );

//     overlay.insert(overlayEntry);
//     Future.delayed(const Duration(seconds: 3), () {
//       overlayEntry.remove();
//     });
//   }

//   // Phone validation
//   String? _validatePhone(String? value) {
//     if (value == null || value.isEmpty) {
//       return 'Phone number is required';
//     }
//     if (value.length < 10) {
//       return 'Phone number must be at least 10 digits';
//     }
//     if (!RegExp(r'^[0-9]+$').hasMatch(value)) {
//       return 'Phone number can only contain digits';
//     }
//     return null;
//   }

//   // Send OTP via Firebase
//   Future<void> _sendOTP() async {
//     if (!_formKey.currentState!.validate()) {
//       _showCustomSnackBar('Please enter a valid phone number', isError: true);
//       return;
//     }

//     setState(() => _isLoading = true);

//     try {
//       String fullPhoneNumber = _countryCode + phoneController.text.trim();
      
//       await _auth.verifyPhoneNumber(
//         phoneNumber: fullPhoneNumber,
//         verificationCompleted: (PhoneAuthCredential credential) async {
//           // Auto-verification completed (Android only)
//           await _signInWithCredential(credential);
//         },
//         verificationFailed: (FirebaseAuthException e) {
//           setState(() => _isLoading = false);
//           String errorMessage = 'Verification failed';
          
//           if (e.code == 'invalid-phone-number') {
//             errorMessage = 'Invalid phone number format';
//           } else if (e.code == 'too-many-requests') {
//             errorMessage = 'Too many attempts. Please try again later';
//           } else if (e.code == 'quota-exceeded') {
//             errorMessage = 'SMS quota exceeded. Please try again later';
//           }
          
//           _showCustomSnackBar(errorMessage, isError: true);
//         },
//         codeSent: (String verificationId, int? resendToken) {
//           setState(() => _isLoading = false);
//           _showCustomSnackBar('OTP sent successfully!', isSuccess: true);
          
//           // Navigate to OTP verification screen
//           Navigator.push(
//             context,
//             MaterialPageRoute(
//               builder: (context) => OTPVerificationScreen(
//                 verificationId: verificationId,
//                 phoneNumber: fullPhoneNumber,
//                 resendToken: resendToken,
//               ),
//             ),
//           );
//         },
//         codeAutoRetrievalTimeout: (String verificationId) {
//           // Auto-retrieval timeout
//           debugPrint('Auto-retrieval timeout: $verificationId');
//         },
//         timeout: const Duration(seconds: 60),
//       );
//     } catch (e) {
//       setState(() => _isLoading = false);
//       _showCustomSnackBar('Error sending OTP: ${e.toString()}', isError: true);
//     }
//   }

//   // Sign in with credential (for auto-verification)
//   Future<void> _signInWithCredential(PhoneAuthCredential credential) async {
//     try {
//       UserCredential userCredential = await _auth.signInWithCredential(credential);
//       if (userCredential.user != null) {
//         await _sendTokenToBackend(userCredential.user!);
//       }
//     } catch (e) {
//       _showCustomSnackBar('Auto-verification failed: ${e.toString()}', isError: true);
//     }
//   }

//   // Send FCM token and phone to backend
//   Future<void> _sendTokenToBackend(User user) async {
//     try {
//       // Get FCM token
//       String? fcmToken = await _messaging.getToken();
      
//       if (fcmToken != null) {
//         // Send to your backend API
//         final response = await http.post(
//           Uri.parse('YOUR_BACKEND_API_ENDPOINT'), // Replace with your API endpoint
//           headers: {
//             'Content-Type': 'application/json',
//           },
//           body: jsonEncode({
//             'phone_number': user.phoneNumber,
//             'fcm_token': fcmToken,
//             'firebase_uid': user.uid,
//           }),
//         );

//         if (response.statusCode == 200) {
//           _showCustomSnackBar('Authentication successful!', isSuccess: true);
//           // Navigate to your main app screen
//         } else {
//           _showCustomSnackBar('Backend authentication failed', isError: true);
//         }
//       }
//     } catch (e) {
//       _showCustomSnackBar('Error sending data to backend: ${e.toString()}', isError: true);
//     }
//   }

//   Map<String, String> get languageMap {
//     return {'el': 'GR', 'en': 'EN', 'ro': 'RO'};
//   }

//   @override
//   Widget build(BuildContext context) {
// final localizations = AppLocalizations.of(context);
// if (localizations == null) {
//   return const SizedBox(); // Or a loading widget
// }    final localizationService = Provider.of<LocalizationService>(context);

//     return Scaffold(
//       body: Stack(
//         children: [
//           // Background image
//           Container(
//             decoration: const BoxDecoration(
//               image: DecorationImage(
//                 image: AssetImage("assets/images/auth.jpg"),
//                 fit: BoxFit.cover,
//               ),
//             ),
//           ),
//           Container(
//             decoration: BoxDecoration(
//               gradient: LinearGradient(
//                 begin: Alignment.topCenter,
//                 end: Alignment.bottomCenter,
//                 colors: [
//                   Colors.black.withOpacity(0.3),
//                   Colors.black.withOpacity(0.1),
//                 ],
//               ),
//             ),
//           ),
//           SafeArea(
//             child: Column(
//               children: [
//                 const SizedBox(height: 20),
//                 // Logo
//                 Center(
//                   child: Image.asset(
//                     'assets/images/app-logo.png',
//                     height: 100,
//                     width: 600,
//                   ),
//                 ),
//                 const SizedBox(height: 20),
//                 // Language Selection
//                 Center(
//                   child: Text(
//                     localizations.chooseLanguage,
//                     style: const TextStyle(
//                       fontSize: 20,
//                       color: Colors.white,
//                       fontFamily: 'Poppins',
//                       fontWeight: FontWeight.w500,
//                     ),
//                     textAlign: TextAlign.center,
//                   ),
//                 ),
//                 const SizedBox(height: 15),
//                 Center(
//                   child: Row(
//                     mainAxisAlignment: MainAxisAlignment.center,
//                     children: [
//                       _buildLanguageOption('el'),
//                       _separator(),
//                       _buildLanguageOption('en'),
//                       _separator(),
//                       _buildLanguageOption('ro'),
//                     ],
//                   ),
//                 ),
//                 const SizedBox(height: 20),
//                 // White Card with form
//                 Expanded(
//                   child: Container(
//                     width: double.infinity,
//                     margin: const EdgeInsets.fromLTRB(16, 0, 16, 8),
//                     padding: const EdgeInsets.all(24),
//                     decoration: BoxDecoration(
//                       color: Colors.white,
//                       borderRadius: BorderRadius.circular(25),
//                       boxShadow: [
//                         BoxShadow(
//                           color: Colors.black.withOpacity(0.1),
//                           blurRadius: 20,
//                           offset: const Offset(0, 10),
//                         ),
//                       ],
//                     ),
//                     child: Form(
//                       key: _formKey,
//                       child: Column(
//                         crossAxisAlignment: CrossAxisAlignment.start,
//                         children: [
//                           // Title section
//                           Column(
//                             children: [
//                               const Center(
//                                 child: Text(
//                                   'PHONE VERIFICATION',
//                                   style: TextStyle(
//                                     fontWeight: FontWeight.bold,
//                                     fontSize: 24,
//                                     fontFamily: 'Poppins',
//                                     color: Colors.black87,
//                                   ),
//                                   textAlign: TextAlign.center,
//                                 ),
//                               ),
//                               const SizedBox(height: 8),
//                               Center(
//                                 child: Text(
//                                   'Enter your phone number to receive OTP',
//                                   style: TextStyle(
//                                     color: Colors.grey[600],
//                                     fontSize: 12,
//                                     fontFamily: 'Poppins',
//                                   ),
//                                   textAlign: TextAlign.center,
//                                 ),
//                               ),
//                             ],
//                           ),
//                           const SizedBox(height: 40),
                          
//                           // Phone Number Section
//                           _buildFieldLabel('Phone Number'),
                          
//                           // Country Code and Phone Input Row
//                           Row(
//                             children: [
//                               // Country Code Dropdown
//                               Container(
//                                 padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
//                                 decoration: BoxDecoration(
//                                   color: Colors.grey[100],
//                                   border: Border.all(color: Colors.grey[300]!),
//                                   borderRadius: BorderRadius.circular(12),
//                                 ),
//                                 child: DropdownButton<String>(
//                                   value: _countryCode,
//                                   underline: const SizedBox(),
//                                   items: const [
//                                     DropdownMenuItem(value: '+92', child: Text('+92 🇵🇰')),
//                                     DropdownMenuItem(value: '+1', child: Text('+1 🇺🇸')),
//                                     DropdownMenuItem(value: '+91', child: Text('+91 🇮🇳')),
//                                     DropdownMenuItem(value: '+44', child: Text('+44 🇬🇧')),
//                                     DropdownMenuItem(value: '+30', child: Text('+30 🇬🇷')),
//                                   ],
//                                   onChanged: (value) {
//                                     setState(() {
//                                       _countryCode = value!;
//                                     });
//                                   },
//                                 ),
//                               ),
//                               const SizedBox(width: 10),
//                               // Phone Number Input
//                               Expanded(
//                                 child: _buildTextFormField(
//                                   controller: phoneController,
//                                   validator: _validatePhone,
//                                   hintText: 'Enter phone number',
//                                   prefixIcon: Icons.phone_outlined,
//                                   keyboardType: TextInputType.phone,
//                                   inputFormatters: [
//                                     FilteringTextInputFormatter.digitsOnly,
//                                     LengthLimitingTextInputFormatter(15),
//                                   ],
//                                 ),
//                               ),
//                             ],
//                           ),
                          
//                           const Spacer(),
                          
//                           // Send OTP Button
//                           Center(
//                             child: SizedBox(
//                               width: double.infinity,
//                               child: ElevatedButton(
//                                 onPressed: _isLoading ? null : _sendOTP,
//                                 style: ElevatedButton.styleFrom(
//                                   backgroundColor: const Color(0xFFEC7103),
//                                   foregroundColor: Colors.white,
//                                   padding: const EdgeInsets.symmetric(vertical: 16),
//                                   shape: RoundedRectangleBorder(
//                                     borderRadius: BorderRadius.circular(25),
//                                   ),
//                                   elevation: 2,
//                                 ),
//                                 child: _isLoading
//                                     ? const Row(
//                                         mainAxisAlignment: MainAxisAlignment.center,
//                                         children: [
//                                           SizedBox(
//                                             width: 20,
//                                             height: 20,
//                                             child: CircularProgressIndicator(
//                                               strokeWidth: 2,
//                                               valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
//                                             ),
//                                           ),
//                                           SizedBox(width: 12),
//                                           Text(
//                                             'Sending OTP...',
//                                             style: TextStyle(
//                                               fontWeight: FontWeight.w600,
//                                               fontFamily: 'Poppins',
//                                               fontSize: 16,
//                                             ),
//                                           ),
//                                         ],
//                                       )
//                                     : const Text(
//                                         'Send OTP',
//                                         style: TextStyle(
//                                           fontWeight: FontWeight.w600,
//                                           fontFamily: 'Poppins',
//                                           fontSize: 16,
//                                         ),
//                                       ),
//                               ),
//                             ),
//                           ),
//                           const SizedBox(height: 20),
//                         ],
//                       ),
//                     ),
//                   ),
//                 ),
//               ],
//             ),
//           ),
//         ],
//       ),
//     );
//   }

//   Widget _buildFieldLabel(String text) {
//     return Padding(
//       padding: const EdgeInsets.only(bottom: 8, left: 4),
//       child: Text(
//         text,
//         style: const TextStyle(
//           fontWeight: FontWeight.w600,
//           fontFamily: 'Poppins',
//           fontSize: 14,
//           color: Colors.black87,
//         ),
//         textAlign: TextAlign.left,
//       ),
//     );
//   }

//   Widget _buildTextFormField({
//     required TextEditingController controller,
//     required String? Function(String?) validator,
//     required String hintText,
//     required IconData prefixIcon,
//     TextInputType keyboardType = TextInputType.text,
//     List<TextInputFormatter>? inputFormatters,
//     bool obscureText = false,
//     Widget? suffixIcon,
//   }) {
//     return TextFormField(
//       controller: controller,
//       validator: validator,
//       keyboardType: keyboardType,
//       inputFormatters: inputFormatters,
//       obscureText: obscureText,
//       style: const TextStyle(fontSize: 14, fontFamily: 'Poppins'),
//       decoration: InputDecoration(
//         filled: true,
//         fillColor: Colors.grey[100],
//         hintText: hintText,
//         hintStyle: TextStyle(color: Colors.grey[500], fontSize: 13),
//         prefixIcon: Icon(prefixIcon, color: Colors.grey[600], size: 20),
//         suffixIcon: suffixIcon,
//         contentPadding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
//         border: OutlineInputBorder(
//           borderRadius: BorderRadius.circular(12),
//           borderSide: BorderSide.none,
//         ),
//         enabledBorder: OutlineInputBorder(
//           borderRadius: BorderRadius.circular(12),
//           borderSide: BorderSide(color: Colors.grey[300]!),
//         ),
//         focusedBorder: OutlineInputBorder(
//           borderRadius: BorderRadius.circular(12),
//           borderSide: BorderSide(color: Colors.orange.shade400, width: 2),
//         ),
//         errorBorder: OutlineInputBorder(
//           borderRadius: BorderRadius.circular(12),
//           borderSide: const BorderSide(color: Colors.red, width: 1),
//         ),
//         focusedErrorBorder: OutlineInputBorder(
//           borderRadius: BorderRadius.circular(12),
//           borderSide: const BorderSide(color: Colors.red, width: 2),
//         ),
//         errorStyle: const TextStyle(fontSize: 12, fontFamily: 'Poppins'),
//       ),
//     );
//   }

//   Widget _buildLanguageOption(String langCode) {
//     bool selected = langCode == _selectedLanguage;
//     String displayText = languageMap[langCode] ?? langCode.toUpperCase();

//     return GestureDetector(
//       onTap: () async {
//         final localizationService = Provider.of<LocalizationService>(
//           context,
//           listen: false,
//         );
//         await localizationService.changeLanguage(langCode);
//         setState(() {
//           _selectedLanguage = langCode;
//         });

//         ScaffoldMessenger.of(context).showSnackBar(
//           SnackBar(
//             content: Text(AppLocalizations.of(context)!.languageChanged),
//             backgroundColor: Colors.orange,
//             behavior: SnackBarBehavior.floating,
//             shape: RoundedRectangleBorder(
//               borderRadius: BorderRadius.circular(10),
//             ),
//             duration: const Duration(seconds: 1),
//           ),
//         );
//       },
//       child: Text(
//         displayText,
//         style: TextStyle(
//           color: selected ? const Color(0xFFEC7103) : Colors.white,
//           fontWeight: selected ? FontWeight.bold : FontWeight.normal,
//           fontSize: 16,
//           fontFamily: 'Jura',
//         ),
//       ),
//     );
//   }

//   Widget _separator() => Container(
//         height: 20,
//         width: 1,
//         margin: const EdgeInsets.symmetric(horizontal: 12),
//         color: const Color.fromARGB(176, 54, 54, 54),
//       );
// }