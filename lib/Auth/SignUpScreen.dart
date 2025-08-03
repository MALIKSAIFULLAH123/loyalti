// import 'package:flutter/material.dart';
// import 'package:http/http.dart' as http;
// import 'package:loyalty_app/utils/api_constants.dart';
// import 'dart:convert';
// import 'package:shared_preferences/shared_preferences.dart';
// import 'package:loyalty_app/Services/language_service.dart';
// import 'package:provider/provider.dart';

// Map<String, dynamic>? globalLiscence;

// class SignUpScreen extends StatefulWidget {
//   const SignUpScreen({super.key});

//   @override
//   State<SignUpScreen> createState() => _SignUpScreenState();
// }

// class _SignUpScreenState extends State<SignUpScreen> {
//   final TextEditingController fullNameController = TextEditingController();
//   final TextEditingController emailController = TextEditingController();
//   final TextEditingController phoneController = TextEditingController();
//   final TextEditingController passwordController = TextEditingController();

//   bool _acceptTerms = false;
//   final bool _obscurePassword = true;
//   String _selectedLanguage = 'el'; // Default to Greek
//   bool _isLoading = false;

//   @override
//   void initState() {
//     super.initState();
//     _initialize();
//   }

//   void _initialize() async {
//     // Get current language from provider
//     final localizationService = Provider.of<LocalizationService>(
//       context,
//       listen: false,
//     );
//     setState(() {
//       _selectedLanguage = localizationService.currentLocale.languageCode;
//     });

//     await hitLicenseApiAndSave(); // Wait until this completes
//     await gettingclintID(); // Then run this
//     await signupCustomer();
//   }

//   Future<void> hitLicenseApiAndSave() async {
//     final uri = Uri.parse(
//       '${ApiConstants.baseUrl}https://webapp.xit.gr/service/license',
//     );

//     try {
//       final response = await http.get(uri);

//       if (response.statusCode == 200) {
//         final data = jsonDecode(response.body);

//         final prefs = await SharedPreferences.getInstance();
//         await prefs.setString('token_type', data['token_type']);
//         await prefs.setInt('iat', data['iat']);
//         await prefs.setInt('expires_in', data['expires_in']);
//         await prefs.setString('jwt_token', data['jwt_token']);

//         print('✅ Token saved in SharedPreferences');
//       } else {
//         print('❌ License Error ${response.statusCode}: ${response.body}');
//       }
//     } catch (e) {
//       print('❗ License Exception: $e');
//     }
//   }

//   Future<Map<String, dynamic>?> _getLiscenceDetails() async {
//     final prefs = await SharedPreferences.getInstance();
//     final jwtToken = prefs.getString('jwt_token');

//     if (jwtToken == null) return null;

//     final uri = Uri.parse(
//       "${ApiConstants.baseUrl}https://license.xit.gr/wp-json/wp/v2/users/?slug=fanis2",
//     );

//     final response = await http.get(
//       uri,
//       headers: {
//         'Content-Type': 'application/json',
//         'Authorization': 'Bearer $jwtToken',
//       },
//     );

//     if (response.statusCode == 200) {
//       final List data = jsonDecode(response.body);
//       if (data.isNotEmpty) {
//         return {
//           "company_url": data[0]["acf"]["company_url"],
//           "appid": data[0]["acf"]["app_id"],
//           "company_id": data[0]["acf"]["company_id"],
//           "branch": data[0]["acf"]["branch"],
//           "refid": data[0]["acf"]["refid"],
//           "software_type": data[0]["acf"]["software_type"],
//         };
//       }
//     } else {
//       print('❌ License fetch failed: ${response.body}');
//     }

//     return null;
//   }

//   Future<void> signupCustomer() async {
//     setState(() => _isLoading = true);

//     try {
//       final prefs = await SharedPreferences.getInstance();
//       final clientID = prefs.getString('clientID');
//       final companyUrl = prefs.getString('company_url');
//       final softwareType = prefs.getString('software_type');

//       if (clientID == null || companyUrl == null || softwareType == null) {
//         ScaffoldMessenger.of(context).showSnackBar(
//           const SnackBar(content: Text('Missing clientID or config')),
//         );
//         return;
//       }

//       final servicePath = softwareType == "TESAE"
//           ? "/pegasus/a_xit/connector.php"
//           : "/s1services";

//       final uri = Uri.parse(
//         "${ApiConstants.baseUrl}https://$companyUrl$servicePath",
//       );

//       final body = {
//         "service": "setData",
//         "clientID": clientID,
//         "appId": "1001",
//         "OBJECT": "CUSTOMER[FORM=WEB]",
//         "KEY": "",
//         "data": {
//           "CUSTOMER": [
//             {
//               "CODE": "123456789",
//               "NAME": "TEST XIT",
//               "PHONE01": "123456789",
//             },
//           ],
//         },
//       };

//       final response = await http.post(
//         uri,
//         headers: {'Content-Type': 'application/json'},
//         body: jsonEncode(body),
//       );

//       if (response.statusCode == 200) {
//         final data = jsonDecode(response.body);
//         if (data['success'] == true) {
//           ScaffoldMessenger.of(
//             context,
//           ).showSnackBar(const SnackBar(content: Text('Signup successful!')));
//           // Navigate to next screen if needed
//         } else {
//           ScaffoldMessenger.of(context).showSnackBar(
//             SnackBar(
//               content: Text(
//                 'Signup failed: ${data['error'] ?? 'Unknown error'}',
//               ),
//             ),
//           );
//         }
//       } else {
//         ScaffoldMessenger.of(context).showSnackBar(
//           SnackBar(content: Text('Server error: ${response.statusCode}')),
//         );
//       }
//     } catch (e) {
//       ScaffoldMessenger.of(
//         context,
//       ).showSnackBar(SnackBar(content: Text('Error: $e')));
//     } finally {
//       setState(() => _isLoading = false);
//     }
//   }

//   Future<void> gettingclintID() async {
//     setState(() => _isLoading = true);

//     try {
//       final liscence = await _getLiscenceDetails();
//       if (liscence == null) {
//         ScaffoldMessenger.of(
//           context,
//         ).showSnackBar(SnackBar(content: Text('License check failed')));
//         return;
//       }

//       globalLiscence = liscence;

//       final servicePath = liscence["software_type"] == "TESAE"
//           ? "/pegasus/a_xit/connector.php"
//           : "/s1services";

//       final uri = Uri.parse(
//         "${ApiConstants.baseUrl}https://${liscence["company_url"]}$servicePath",
//       );

//       final response = await http.post(
//         uri,
//         headers: {'Content-Type': 'application/json'},
//         body: jsonEncode({
//           "service": "login",
//           "username": 'fanis2',
//           "password": '1234',
//           "appId": "1001",
//           "COMPANY": "1000",
//           "BRANCH": "1000",
//           "MODULE": "0",
//           "REFID": "999",
//         }),
//       );

//       if (response.statusCode == 200) {
//         final data = jsonDecode(response.body);
//         if (data['success'] == true) {
//           final prefs = await SharedPreferences.getInstance();
//           final clientID = data['clientID'];

//           await prefs.setString('clientID', clientID);
//           await prefs.setString('company', data['companyinfo'].split('|')[0]);
//           await prefs.setString('company_url', liscence['company_url']);
//           await prefs.setString('software_type', liscence['software_type']);

//           ScaffoldMessenger.of(
//             context,
//           ).showSnackBar(SnackBar(content: Text('Login successful!')));
//         } else {
//           ScaffoldMessenger.of(context).showSnackBar(
//             SnackBar(content: Text('Login failed: Invalid credentials')),
//           );
//         }
//       } else {
//         ScaffoldMessenger.of(context).showSnackBar(
//           SnackBar(content: Text('Error: ${response.statusCode}')),
//         );
//       }
//     } catch (e) {
//       ScaffoldMessenger.of(
//         context,
//       ).showSnackBar(SnackBar(content: Text('Error: $e')));
//     } finally {
//       setState(() => _isLoading = false);
//     }
//   }

//   // Language mapping for display
//   Map<String, String> get languageMap {
//     return {'el': 'GR', 'en': 'EN', 'ro': 'RO'};
//   }

//   @override
//   Widget build(BuildContext context) {
//     final localizations = AppLocalizations.of(context)!;
//     final localizationService = Provider.of<LocalizationService>(context);

//     return Scaffold(
//       body: Stack(
//         children: [
//           // Background image with blur
//           Container(
//             decoration: const BoxDecoration(
//               image: DecorationImage(
//                 image: AssetImage("images/auth.jpg"),
//                 fit: BoxFit.cover,
//               ),
//             ),
//           ),
//           Container(),
//           SafeArea(
//             child: Column(
//               children: [
//                 const SizedBox(height: 20),
//                 // Logo
//                 Image.asset('images/app-logo.png', height: 100, width: 600),
//                 const SizedBox(height: 40),

//                 // Language Selection
//                 Text(
//                   localizations.chooseLanguage,
//                   style: TextStyle(
//                     fontSize: 20,
//                     color: Colors.white,
//                     fontFamily: 'Poppins',
//                   ),
//                 ),
//                 const SizedBox(height: 10),
//                 Row(
//                   mainAxisAlignment: MainAxisAlignment.center,
//                   children: [
//                     _buildLanguageOption('el'),
//                     _separator(),
//                     _buildLanguageOption('en'),
//                     _separator(),
//                     _buildLanguageOption('ro'),
//                   ],
//                 ),

//                 const SizedBox(height: 30),

//                 // White Card with scroll capability
//                 Expanded(
//                   child: SingleChildScrollView(
//                     child: Container(
//                       width: double.infinity,
//                       margin: const EdgeInsets.symmetric(horizontal: 36),
//                       padding: const EdgeInsets.all(24),
//                       decoration: BoxDecoration(
//                         color: Colors.white,
//                         borderRadius: BorderRadius.circular(30),
//                       ),
//                       child: Column(
//                         crossAxisAlignment: CrossAxisAlignment.start,
//                         mainAxisSize: MainAxisSize.min,
//                         children: [
//                           Center(
//                             child: Text(
//                               'SIGN UP',
//                               style: TextStyle(
//                                 fontWeight: FontWeight.bold,
//                                 fontSize: 22,
//                                 fontFamily: 'Poppins',
//                               ),
//                             ),
//                           ),
//                           const SizedBox(height: 10),
//                           Center(
//                             child: Text(
//                               'Sign up and start earning points',
//                               style: TextStyle(
//                                 color: Colors.grey[700],
//                                 fontSize: 11,
//                                 fontFamily: 'Poppins',
//                               ),
//                               textAlign: TextAlign.center,
//                             ),
//                           ),
//                           const SizedBox(height: 10),

//                           // Full Name field
//                           Text(
//                             localizations.Name,
//                             style: TextStyle(
//                               fontWeight: FontWeight.bold,
//                               fontFamily: 'Poppins',
//                             ),
//                           ),
//                           const SizedBox(height: 6),
//                           TextField(
//                             controller: fullNameController,
//                             decoration: InputDecoration(
//                               filled: true,
//                               fillColor: Colors.grey[200],
//                               contentPadding: EdgeInsets.symmetric(
//                                 vertical: 10,
//                                 horizontal: 12,
//                               ),
//                               border: OutlineInputBorder(
//                                 borderRadius: BorderRadius.circular(12),
//                                 borderSide: BorderSide.none,
//                               ),
//                             ),
//                             style: TextStyle(
//                               fontSize: 14,
//                               fontFamily: 'Poppins',
//                             ),
//                           ),

//                           const SizedBox(height: 15),

//                           // Email field
//                           // Text(
//                           //   localizations.email,
//                           //   style: TextStyle(
//                           //     fontWeight: FontWeight.bold,
//                           //     fontFamily: 'Poppins',
//                           //   ),
//                           // ),
//                           // const SizedBox(height: 6),
//                           // TextField(
//                           //   controller: emailController,
//                           //   keyboardType: TextInputType.emailAddress,
//                           //   decoration: InputDecoration(
//                           //     filled: true,
//                           //     fillColor: Colors.grey[200],
//                           //     contentPadding: EdgeInsets.symmetric(
//                           //       vertical: 10,
//                           //       horizontal: 12,
//                           //     ),
//                           //     border: OutlineInputBorder(
//                           //       borderRadius: BorderRadius.circular(12),
//                           //       borderSide: BorderSide.none,
//                           //     ),
//                           //   ),
//                           //   style: TextStyle(
//                           //     fontSize: 14,
//                           //     fontFamily: 'Poppins',
//                           //   ),
//                           // ),
//                           const SizedBox(height: 15),

//                           // Phone Number field
//                           Text(
//                             localizations.phone,
//                             style: TextStyle(
//                               fontWeight: FontWeight.bold,
//                               fontFamily: 'Poppins',
//                             ),
//                           ),
//                           const SizedBox(height: 6),
//                           TextField(
//                             controller: phoneController,
//                             keyboardType: TextInputType
//                                 .number, // Changed from TextInputType.phone
//                             decoration: InputDecoration(
//                               filled: true,
//                               fillColor: Colors.grey[200],
//                               contentPadding: EdgeInsets.symmetric(
//                                 vertical: 10,
//                                 horizontal: 12,
//                               ),
//                               border: OutlineInputBorder(
//                                 borderRadius: BorderRadius.circular(12),
//                                 borderSide: BorderSide.none,
//                               ),
//                             ),
//                             style: TextStyle(
//                               fontSize: 14,
//                               fontFamily: 'Poppins',
//                             ),
//                           ),

//                           const SizedBox(height: 15),

//                           // Password field
//                           // Text(
//                           //   localizations.password,
//                           //   style: TextStyle(
//                           //     fontWeight: FontWeight.bold,
//                           //     fontFamily: 'Poppins',
//                           //   ),
//                           // ),
//                           // const SizedBox(height: 6),
//                           // TextField(
//                           //   controller: passwordController,
//                           //   obscureText: _obscurePassword,
//                           //   decoration: InputDecoration(
//                           //     filled: true,
//                           //     fillColor: Colors.grey[200],
//                           //     contentPadding: EdgeInsets.symmetric(
//                           //       vertical: 9,
//                           //       horizontal: 12,
//                           //     ),
//                           //     border: OutlineInputBorder(
//                           //       borderRadius: BorderRadius.circular(12),
//                           //       borderSide: BorderSide.none,
//                           //     ),
//                           //     suffixIcon: IconButton(
//                           //       icon: Icon(
//                           //         _obscurePassword
//                           //             ? Icons.visibility
//                           //             : Icons.visibility_off,
//                           //       ),
//                           //       onPressed: () {
//                           //         setState(() {
//                           //           _obscurePassword = !_obscurePassword;
//                           //         });
//                           //       },
//                           //     ),
//                           //   ),
//                           //   style: TextStyle(
//                           //     fontSize: 14,
//                           //     fontFamily: 'Poppins',
//                           //   ),
//                           // ),
//                           // const SizedBox(height: 5),

//                           // Terms checkbox
//                           Row(
//                             children: [
//                               Checkbox(
//                                 value: _acceptTerms,
//                                 onChanged: (val) {
//                                   setState(() {
//                                     _acceptTerms = val!;
//                                   });
//                                 },
//                               ),
//                               RichText(
//                                 text: TextSpan(
//                                   style: TextStyle(
//                                     color: Colors.black87,
//                                     fontFamily: 'Poppins',
//                                   ),
//                                   children: [
//                                     TextSpan(text: 'i accept the '),
//                                     TextSpan(
//                                       text: 'terms of use',
//                                       style: TextStyle(
//                                         color: Colors.orange,
//                                         decoration: TextDecoration.underline,
//                                       ),
//                                     ),
//                                   ],
//                                 ),
//                               ),
//                             ],
//                           ),

//                           // Sign in text
//                           Center(
//                             child: RichText(
//                               text: TextSpan(
//                                 style: TextStyle(
//                                   color: Colors.black87,
//                                   fontFamily: 'Poppins',
//                                   fontSize: 12,
//                                 ),
//                                 children: [
//                                   TextSpan(text: localizations.noAccount),
//                                   TextSpan(
//                                     text: localizations.signUp,
//                                     style: TextStyle(
//                                       color: Colors.orange,
//                                       fontWeight: FontWeight.bold,
//                                       decoration: TextDecoration.underline,
//                                       fontSize: 14,
//                                     ),
//                                   ),
//                                 ],
//                               ),
//                             ),
//                           ),

//                           const SizedBox(height: 30),

//                           // Sign In button
//                           Align(
//                             alignment: Alignment.centerRight,
//                             child: SizedBox(
//                               child: ElevatedButton(
//                                 onPressed: _isLoading
//                                     ? null
//                                     : () {
//                                         if (!_acceptTerms) {
//                                           ScaffoldMessenger.of(
//                                             context,
//                                           ).showSnackBar(
//                                             SnackBar(
//                                               content: Text(
//                                                 'Please accept terms of use',
//                                               ),
//                                             ),
//                                           );
//                                           return;
//                                         }

//                                         if (fullNameController.text.isEmpty ||
//                                             emailController.text.isEmpty ||
//                                             phoneController.text.isEmpty ||
//                                             passwordController.text.isEmpty) {
//                                           ScaffoldMessenger.of(
//                                             context,
//                                           ).showSnackBar(
//                                             SnackBar(
//                                               content: Text(
//                                                 'Please fill all fields',
//                                               ),
//                                             ),
//                                           );
//                                           return;
//                                         }

//                                         signupCustomer();
//                                       },
//                                 style: ElevatedButton.styleFrom(
//                                   backgroundColor: Colors.orange,
//                                   padding: const EdgeInsets.symmetric(
//                                     vertical: 6,
//                                     horizontal: 44,
//                                   ),
//                                   shape: RoundedRectangleBorder(
//                                     borderRadius: BorderRadius.circular(30),
//                                   ),
//                                 ),
//                                 child: _isLoading
//                                     ? SizedBox(
//                                         width: 16,
//                                         height: 16,
//                                         child: CircularProgressIndicator(
//                                           strokeWidth: 2,
//                                           valueColor:
//                                               AlwaysStoppedAnimation<Color>(
//                                                 Colors.white,
//                                               ),
//                                         ),
//                                       )
//                                     : Text(
//                                         localizations.signInButton,
//                                         style: TextStyle(
//                                           color: Colors.white,
//                                           fontWeight: FontWeight.w600,
//                                           fontFamily: 'Poppins',
//                                         ),
//                                       ),
//                               ),
//                             ),
//                           ),
//                         ],
//                       ),
//                     ),
//                   ),
//                 ),
//                 const SizedBox(height: 20),
//               ],
//             ),
//           ),
//         ],
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

//         // Show feedback
//         ScaffoldMessenger.of(context).showSnackBar(
//           SnackBar(
//             content: Text(AppLocalizations.of(context)!.languageChanged),
//             backgroundColor: Colors.orange,
//             behavior: SnackBarBehavior.floating,
//             shape: RoundedRectangleBorder(
//               borderRadius: BorderRadius.circular(10),
//             ),
//             duration: Duration(seconds: 1),
//           ),
//         );
//       },
//       child: Text(
//         displayText,
//         style: TextStyle(
//           color: selected
//               ? const Color.fromARGB(255, 247, 246, 230)
//               : Color.fromARGB(255, 36, 36, 36),
//           fontWeight: selected ? FontWeight.bold : FontWeight.normal,
//           fontSize: 16,
//           fontFamily: 'Jura',
//         ),
//       ),
//     );
//   }

//   Widget _separator() => Container(
//     height: 20,
//     width: 1,
//     margin: const EdgeInsets.symmetric(horizontal: 12),
//     color: const Color.fromARGB(176, 54, 54, 54),
//   );
// }
