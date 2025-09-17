// import 'package:flutter/material.dart';
// import 'package:flutter/services.dart';
// import 'package:firebase_auth/firebase_auth.dart';
// import 'package:firebase_messaging/firebase_messaging.dart';
// import 'package:provider/provider.dart';
// import 'package:loyalty_app/Services/language_service.dart';
// import 'package:http/http.dart' as http;
// import 'dart:convert';
// import 'dart:async';

// class OTPVerificationScreen extends StatefulWidget {
//   final String verificationId;
//   final String phoneNumber;
//   final int? resendToken;

//   const OTPVerificationScreen({
//     super.key,
//     required this.verificationId,
//     required this.phoneNumber,
//     this.resendToken,
//   });

//   @override
//   State<OTPVerificationScreen> createState() => _OTPVerificationScreenState();
// }

// class _OTPVerificationScreenState extends State<OTPVerificationScreen>
//     with TickerProviderStateMixin {
//   final List<TextEditingController> _otpControllers =
//       List.generate(6, (index) => TextEditingController());
//   final List<FocusNode> _focusNodes =
//       List.generate(6, (index) => FocusNode());
//   final FirebaseAuth _auth = FirebaseAuth.instance;
//   final FirebaseMessaging _messaging = FirebaseMessaging.instance;

//   bool _isLoading = false;
//   bool _isResending = false;
//   String _selectedLanguage = 'el';
//   Timer? _timer;
//   int _resendCountdown = 60;
//   bool _canResend = false;
//   String _currentVerificationId = '';
//   late AnimationController _shakeController;
//   late Animation<double> _shakeAnimation;

//   @override
//   void initState() {
//     super.initState();
//     _currentVerificationId = widget.verificationId;
//     _initializeLanguage();
//     _startResendTimer();
//     _setupAnimations();
//   }

//   void _setupAnimations() {
//     _shakeController = AnimationController(
//       duration: const Duration(milliseconds: 600),
//       vsync: this,
//     );
//     _shakeAnimation = Tween<double>(begin: 0, end: 10).animate(
//       CurvedAnimation(parent: _shakeController, curve: Curves.elasticIn),
//     );
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

//   void _startResendTimer() {
//     _canResend = false;
//     _resendCountdown = 60;
//     _timer?.cancel();
//     _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
//       if (mounted) {
//         if (_resendCountdown > 0) {
//           setState(() {
//             _resendCountdown--;
//           });
//         } else {
//           setState(() {
//             _canResend = true;
//           });
//           timer.cancel();
//         }
//       }
//     });
//   }

//   @override
//   void dispose() {
//     _timer?.cancel();
//     _shakeController.dispose();
//     for (var controller in _otpControllers) {
//       controller.dispose();
//     }
//     for (var focusNode in _focusNodes) {
//       focusNode.dispose();
//     }
//     super.dispose();
//   }

//   // Enhanced custom snackbar with better positioning and animation
//   void _showCustomSnackBar(
//     String message, {
//     bool isError = false,
//     bool isSuccess = false,
//   }) {
//     final overlay = Overlay.of(context);
//     late OverlayEntry overlayEntry;

//     overlayEntry = OverlayEntry(
//       builder: (context) => Positioned(
//         top: MediaQuery.of(context).padding.top + 20,
//         left: 16,
//         right: 16,
//         child: Material(
//           color: Colors.transparent,
//           child: TweenAnimationBuilder<double>(
//             tween: Tween(begin: 0.0, end: 1.0),
//             duration: const Duration(milliseconds: 300),
//             curve: Curves.elasticOut,
//             builder: (context, value, child) {
//               return Transform.scale(
//                 scale: value,
//                 child: Opacity(
//                   opacity: value,
//                   child: Container(
//                     padding: const EdgeInsets.symmetric(
//                         horizontal: 20, vertical: 16),
//                     decoration: BoxDecoration(
//                       color: isError
//                           ? Colors.red.shade600
//                           : isSuccess
//                               ? Colors.green.shade600
//                               : const Color(0xFFEC7103),
//                       borderRadius: BorderRadius.circular(16),
//                       boxShadow: [
//                         BoxShadow(
//                           color: Colors.black.withOpacity(0.15),
//                           blurRadius: 12,
//                           offset: const Offset(0, 4),
//                         ),
//                       ],
//                     ),
//                     child: Row(
//                       children: [
//                         Container(
//                           padding: const EdgeInsets.all(6),
//                           decoration: BoxDecoration(
//                             color: Colors.white.withOpacity(0.2),
//                             borderRadius: BorderRadius.circular(20),
//                           ),
//                           child: Icon(
//                             isError
//                                 ? Icons.error_outline_rounded
//                                 : isSuccess
//                                     ? Icons.check_circle_outline_rounded
//                                     : Icons.info_outline_rounded,
//                             color: Colors.white,
//                             size: 20,
//                           ),
//                         ),
//                         const SizedBox(width: 12),
//                         Expanded(
//                           child: Text(
//                             message,
//                             style: const TextStyle(
//                               color: Colors.white,
//                               fontWeight: FontWeight.w600,
//                               fontSize: 15,
//                               fontFamily: 'Poppins',
//                             ),
//                           ),
//                         ),
//                       ],
//                     ),
//                   ),
//                 ),
//               );
//             },
//           ),
//         ),
//       ),
//     );

//     overlay.insert(overlayEntry);

//     // Auto remove after delay with fade out animation
//     Future.delayed(const Duration(milliseconds: 2800), () {
//       if (overlayEntry.mounted) {
//         overlayEntry.remove();
//       }
//     });
//   }

//   String _getOTPCode() {
//     return _otpControllers.map((controller) => controller.text).join();
//   }

//   bool _isOTPComplete() {
//     return _getOTPCode().length == 6;
//   }

//   void _clearOTP() {
//     for (var controller in _otpControllers) {
//       controller.clear();
//     }
//     if (_focusNodes[0].canRequestFocus) {
//       _focusNodes[0].requestFocus();
//     }
//   }

//   void _shakeOTPFields() {
//     _shakeController.forward().then((_) {
//       _shakeController.reverse();
//     });
//   }

//   // Enhanced OTP verification with better error handling
//   Future<void> _verifyOTP() async {
//     if (!_isOTPComplete()) {
//       _showCustomSnackBar('Please enter complete 6-digit OTP', isError: true);
//       _shakeOTPFields();
//       return;
//     }

//     setState(() => _isLoading = true);

//     try {
//       String otpCode = _getOTPCode();
//       PhoneAuthCredential credential = PhoneAuthProvider.credential(
//         verificationId: _currentVerificationId,
//         smsCode: otpCode,
//       );

//       UserCredential userCredential =
//           await _auth.signInWithCredential(credential);

//       if (userCredential.user != null) {
//         _showCustomSnackBar('OTP verified successfully! 🎉', isSuccess: true);
        
//         // Add small delay for better UX
//         await Future.delayed(const Duration(milliseconds: 500));
//         await _sendTokenToBackend(userCredential.user!);
//       }
//     } on FirebaseAuthException catch (e) {
//       String errorMessage = _getFirebaseErrorMessage(e.code);
//       _showCustomSnackBar(errorMessage, isError: true);
//       _clearOTP();
//       _shakeOTPFields();
//     } catch (e) {
//       _showCustomSnackBar('Verification failed. Please try again.',
//           isError: true);
//       _clearOTP();
//       _shakeOTPFields();
//     } finally {
//       if (mounted) {
//         setState(() => _isLoading = false);
//       }
//     }
//   }

//   String _getFirebaseErrorMessage(String errorCode) {
//     switch (errorCode) {
//       case 'invalid-verification-code':
//         return 'Invalid OTP code. Please check and try again';
//       case 'session-expired':
//         return 'OTP has expired. Please request a new one';
//       case 'too-many-requests':
//         return 'Too many attempts. Please try again later';
//       case 'network-request-failed':
//         return 'Network error. Please check your connection';
//       case 'credential-already-in-use':
//         return 'This phone number is already registered';
//       default:
//         return 'Verification failed. Please try again';
//     }
//   }

//   // Enhanced resend OTP with better feedback
//   Future<void> _resendOTP() async {
//     if (!_canResend) return;

//     setState(() => _isResending = true);

//     try {
//       await _auth.verifyPhoneNumber(
//         phoneNumber: widget.phoneNumber,
//         forceResendingToken: widget.resendToken,
//         verificationCompleted: (PhoneAuthCredential credential) async {
//           await _signInWithCredential(credential);
//         },
//         verificationFailed: (FirebaseAuthException e) {
//           if (mounted) {
//             setState(() => _isResending = false);
//             String errorMessage = _getFirebaseErrorMessage(e.code);
//             _showCustomSnackBar(errorMessage, isError: true);
//           }
//         },
//         codeSent: (String verificationId, int? resendToken) {
//           if (mounted) {
//             setState(() {
//               _currentVerificationId = verificationId;
//               _isResending = false;
//             });
//             _startResendTimer();
//             _clearOTP();
//             _showCustomSnackBar('New OTP sent successfully! 📱',
//                 isSuccess: true);
//           }
//         },
//         codeAutoRetrievalTimeout: (String verificationId) {
//           // Handle timeout
//           debugPrint('Code auto-retrieval timeout: $verificationId');
//         },
//         timeout: const Duration(seconds: 60),
//       );
//     } catch (e) {
//       if (mounted) {
//         setState(() => _isResending = false);
//         _showCustomSnackBar('Failed to resend OTP. Please try again.',
//             isError: true);
//       }
//     }
//   }

//   // Sign in with credential (for auto-verification)
//   Future<void> _signInWithCredential(PhoneAuthCredential credential) async {
//     try {
//       UserCredential userCredential =
//           await _auth.signInWithCredential(credential);
//       if (userCredential.user != null) {
//         _showCustomSnackBar('Auto-verification successful! 🎉',
//             isSuccess: true);
//         await _sendTokenToBackend(userCredential.user!);
//       }
//     } catch (e) {
//       _showCustomSnackBar('Auto-verification failed. Please enter OTP manually.',
//           isError: true);
//     }
//   }

//   // Enhanced backend token sending with retry mechanism
//   Future<void> _sendTokenToBackend(User user) async {
//     try {
//       // Get FCM token with retry
//       String? fcmToken = await _getFCMTokenWithRetry();

//       if (fcmToken != null) {
//         // Prepare data
//         final requestData = {
//           'phone_number': user.phoneNumber,
//           'fcm_token': fcmToken,
//           'firebase_uid': user.uid,
//           'timestamp': DateTime.now().toIso8601String(),
//           'platform': 'flutter',
//         };

//         // Send to backend with retry mechanism
//         bool success = await _sendToBackendWithRetry(requestData);

//         if (success) {
//           _showCustomSnackBar('Authentication successful! Welcome! 🎉',
//               isSuccess: true);
          
//           // Wait a bit for the success message to be visible
//           await Future.delayed(const Duration(milliseconds: 1500));
          
//           // Navigate to main app screen
//           if (mounted) {
//             Navigator.of(context).pushReplacementNamed('/home');
//           }
//         } else {
//           _showCustomSnackBar(
//               'Authentication completed but sync failed. Please try logging in again.',
//               isError: true);
//         }
//       } else {
//         _showCustomSnackBar('Could not get device token. Please try again.',
//             isError: true);
//       }
//     } catch (e) {
//       debugPrint('Error in _sendTokenToBackend: $e');
//       _showCustomSnackBar(
//           'Authentication completed but there was an issue. Please try logging in again.',
//           isError: true);
//     }
//   }

//   // Get FCM token with retry mechanism
//   Future<String?> _getFCMTokenWithRetry([int maxRetries = 3]) async {
//     for (int attempt = 0; attempt < maxRetries; attempt++) {
//       try {
//         String? token = await _messaging.getToken();
//         if (token != null) {
//           return token;
//         }
//       } catch (e) {
//         debugPrint('FCM token attempt ${attempt + 1} failed: $e');
//         if (attempt < maxRetries - 1) {
//           await Future.delayed(Duration(seconds: attempt + 1));
//         }
//       }
//     }
//     return null;
//   }

//   // Send to backend with retry mechanism
//   Future<bool> _sendToBackendWithRetry(Map<String, dynamic> data,
//       [int maxRetries = 3]) async {
//     for (int attempt = 0; attempt < maxRetries; attempt++) {
//       try {
//         final response = await http
//             .post(
//               Uri.parse('YOUR_BACKEND_API_ENDPOINT'), // Replace with your endpoint
//               headers: {
//                 'Content-Type': 'application/json',
//                 'Accept': 'application/json',
//                 'User-Agent': 'LoyaltyApp/1.0',
//               },
//               body: jsonEncode(data),
//             )
//             .timeout(const Duration(seconds: 15));

//         if (response.statusCode == 200 || response.statusCode == 201) {
//           // Parse response to ensure it's valid JSON
//           try {
//             final responseData = jsonDecode(response.body);
//             debugPrint('Backend response: $responseData');
//             return true;
//           } catch (e) {
//             debugPrint('Invalid JSON response: ${response.body}');
//             // If response is not JSON but status is success, still consider it successful
//             return true;
//           }
//         } else {
//           debugPrint(
//               'Backend responded with status: ${response.statusCode}, body: ${response.body}');
//           if (attempt == maxRetries - 1) {
//             return false;
//           }
//         }
//       } on TimeoutException {
//         debugPrint('Request timed out on attempt ${attempt + 1}');
//         if (attempt == maxRetries - 1) {
//           return false;
//         }
//       } catch (e) {
//         debugPrint('Network error on attempt ${attempt + 1}: $e');
//         if (attempt == maxRetries - 1) {
//           return false;
//         }
//       }

//       // Wait before retry with exponential backoff
//       if (attempt < maxRetries - 1) {
//         await Future.delayed(Duration(seconds: (attempt + 1) * 2));
//       }
//     }
//     return false;
//   }

//   // Handle OTP input changes with auto-focus
//   void _onOTPChanged(int index, String value) {
//     if (value.length == 1 && index < 5) {
//       _focusNodes[index + 1].requestFocus();
//     } else if (value.isEmpty && index > 0) {
//       _focusNodes[index - 1].requestFocus();
//     }

//     // Auto-verify when all fields are filled
//     if (_isOTPComplete()) {
//       Future.delayed(const Duration(milliseconds: 300), () {
//         if (_isOTPComplete()) {
//           _verifyOTP();
//         }
//       });
//     }
//   }

//   @override
//   Widget build(BuildContext context) {
//     return Scaffold(
//       body: Stack(
//         children: [
//           // Background
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
//                   Colors.black.withOpacity(0.4),
//                   Colors.black.withOpacity(0.2),
//                 ],
//               ),
//             ),
//           ),
//           SafeArea(
//             child: Column(
//               children: [
//                 const SizedBox(height: 20),
//                 // Back button and logo
//                 Padding(
//                   padding: const EdgeInsets.symmetric(horizontal: 20),
//                   child: Row(
//                     children: [
//                       IconButton(
//                         onPressed: () => Navigator.of(context).pop(),
//                         icon: const Icon(
//                           Icons.arrow_back_ios_rounded,
//                           color: Colors.white,
//                           size: 24,
//                         ),
//                       ),
//                       const Spacer(),
//                       Image.asset(
//                         'assets/images/app-logo.png',
//                         height: 80,
//                         width: 200,
//                       ),
//                       const Spacer(),
//                       const SizedBox(width: 48), // Balance the back button
//                     ],
//                   ),
//                 ),
//                 const SizedBox(height: 30),
//                 // Main content card
//                 Expanded(
//                   child: Container(
//                     width: double.infinity,
//                     margin: const EdgeInsets.fromLTRB(16, 0, 16, 8),
//                     padding: const EdgeInsets.all(28),
//                     decoration: BoxDecoration(
//                       color: Colors.white,
//                       borderRadius: BorderRadius.circular(30),
//                       boxShadow: [
//                         BoxShadow(
//                           color: Colors.black.withOpacity(0.15),
//                           blurRadius: 25,
//                           offset: const Offset(0, 15),
//                         ),
//                       ],
//                     ),
//                     child: Column(
//                       crossAxisAlignment: CrossAxisAlignment.center,
//                       children: [
//                         // Title section
//                         const Text(
//                           'OTP VERIFICATION',
//                           style: TextStyle(
//                             fontWeight: FontWeight.bold,
//                             fontSize: 26,
//                             fontFamily: 'Poppins',
//                             color: Colors.black87,
//                           ),
//                           textAlign: TextAlign.center,
//                         ),
//                         const SizedBox(height: 8),
//                         Text(
//                           'We sent a verification code to',
//                           style: TextStyle(
//                             color: Colors.grey[600],
//                             fontSize: 14,
//                             fontFamily: 'Poppins',
//                           ),
//                           textAlign: TextAlign.center,
//                         ),
//                         const SizedBox(height: 6),
//                         Container(
//                           padding: const EdgeInsets.symmetric(
//                               horizontal: 16, vertical: 8),
//                           decoration: BoxDecoration(
//                             color: const Color(0xFFEC7103).withOpacity(0.1),
//                             borderRadius: BorderRadius.circular(20),
//                           ),
//                           child: Text(
//                             widget.phoneNumber,
//                             style: const TextStyle(
//                               color: Color(0xFFEC7103),
//                               fontSize: 16,
//                               fontFamily: 'Poppins',
//                               fontWeight: FontWeight.w600,
//                             ),
//                           ),
//                         ),
//                         const SizedBox(height: 40),

//                         // OTP Input Fields
//                         AnimatedBuilder(
//                           animation: _shakeAnimation,
//                           builder: (context, child) {
//                             return Transform.translate(
//                               offset: Offset(_shakeAnimation.value, 0),
//                               child: Row(
//                                 mainAxisAlignment:
//                                     MainAxisAlignment.spaceEvenly,
//                                 children: List.generate(
//                                   6,
//                                   (index) => _buildOTPField(index),
//                                 ),
//                               ),
//                             );
//                           },
//                         ),
                        
//                         const SizedBox(height: 30),

//                         // Resend section
//                         Row(
//                           mainAxisAlignment: MainAxisAlignment.center,
//                           children: [
//                             Text(
//                               "Didn't receive code? ",
//                               style: TextStyle(
//                                 color: Colors.grey[600],
//                                 fontSize: 14,
//                                 fontFamily: 'Poppins',
//                               ),
//                             ),
//                             if (_canResend)
//                               GestureDetector(
//                                 onTap: _resendOTP,
//                                 child: Text(
//                                   _isResending ? 'Sending...' : 'Resend',
//                                   style: const TextStyle(
//                                     color: Color(0xFFEC7103),
//                                     fontSize: 14,
//                                     fontFamily: 'Poppins',
//                                     fontWeight: FontWeight.w600,
//                                     decoration: TextDecoration.underline,
//                                   ),
//                                 ),
//                               )
//                             else
//                               Text(
//                                 'Resend in ${_resendCountdown}s',
//                                 style: TextStyle(
//                                   color: Colors.grey[500],
//                                   fontSize: 14,
//                                   fontFamily: 'Poppins',
//                                 ),
//                               ),
//                           ],
//                         ),
                        
//                         const Spacer(),

//                         // Verify button
//                         SizedBox(
//                           width: double.infinity,
//                           child: ElevatedButton(
//                             onPressed: _isLoading ? null : _verifyOTP,
//                             style: ElevatedButton.styleFrom(
//                               backgroundColor: const Color(0xFFEC7103),
//                               foregroundColor: Colors.white,
//                               padding: const EdgeInsets.symmetric(vertical: 18),
//                               shape: RoundedRectangleBorder(
//                                 borderRadius: BorderRadius.circular(25),
//                               ),
//                               elevation: 3,
//                               shadowColor: const Color(0xFFEC7103).withOpacity(0.3),
//                             ),
//                             child: _isLoading
//                                 ? const Row(
//                                     mainAxisAlignment: MainAxisAlignment.center,
//                                     children: [
//                                       SizedBox(
//                                         width: 22,
//                                         height: 22,
//                                         child: CircularProgressIndicator(
//                                           strokeWidth: 2.5,
//                                           valueColor:
//                                               AlwaysStoppedAnimation<Color>(
//                                                   Colors.white),
//                                         ),
//                                       ),
//                                       SizedBox(width: 12),
//                                       Text(
//                                         'Verifying...',
//                                         style: TextStyle(
//                                           fontWeight: FontWeight.w600,
//                                           fontFamily: 'Poppins',
//                                           fontSize: 16,
//                                         ),
//                                       ),
//                                     ],
//                                   )
//                                 : const Text(
//                                     'Verify OTP',
//                                     style: TextStyle(
//                                       fontWeight: FontWeight.w600,
//                                       fontFamily: 'Poppins',
//                                       fontSize: 16,
//                                     ),
//                                   ),
//                           ),
//                         ),
//                         const SizedBox(height: 20),
//                       ],
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

//   Widget _buildOTPField(int index) {
//     return Container(
//       width: 50,
//       height: 60,
//       decoration: BoxDecoration(
//         color: Colors.grey[100],
//         borderRadius: BorderRadius.circular(12),
//         border: Border.all(
//           color: _otpControllers[index].text.isNotEmpty
//               ? const Color(0xFFEC7103)
//               : Colors.grey[300]!,
//           width: _otpControllers[index].text.isNotEmpty ? 2 : 1,
//         ),
//       ),
//       child: TextFormField(
//         controller: _otpControllers[index],
//         focusNode: _focusNodes[index],
//         textAlign: TextAlign.center,
//         keyboardType: TextInputType.number,
//         maxLength: 1,
//         style: const TextStyle(
//           fontSize: 20,
//           fontWeight: FontWeight.bold,
//           fontFamily: 'Poppins',
//           color: Colors.black87,
//         ),
//         inputFormatters: [
//           FilteringTextInputFormatter.digitsOnly,
//         ],
//         decoration: const InputDecoration(
//           counterText: '',
//           border: InputBorder.none,
//           contentPadding: EdgeInsets.zero,
//         ),
//         onChanged: (value) => _onOTPChanged(index, value),
//         onTap: () {
//           // Clear field when tapped if it has content
//           if (_otpControllers[index].text.isNotEmpty) {
//             _otpControllers[index].clear();
//           }
//         },
//       ),
//     );
//   }
// }