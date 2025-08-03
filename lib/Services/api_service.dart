// import 'dart:convert';
// // import 'package:driveway/auth/emailverify.dart';
// // import 'package:driveway/utils/app-costant.dart';
// import 'package:flutter/material.dart';
// import 'package:get/get.dart';
// import 'package:http/http.dart' as http;

// import 'package:shared_preferences/shared_preferences.dart';

// class ApiService {
//   String email = "";
//   String password = "********";
//   String userId = "";
//   String token = "";

//   // Load user data from shared preferences
//   Future<void> _loadUserDataFromPrefs() async {
//     final prefs = await SharedPreferences.getInstance();
//     email = prefs.getString('user_email') ?? '';
//     userId = prefs.getString('user_id') ?? '';
//     token = prefs.getString('user_token') ?? '';
//   }

//   // Authenticate user and return response
// // Simple version - Direct email verification handling
// Future<AuthResponse?> authenticateUser(AuthRequest request) async {
//   final uri = Uri.parse("${ApiConstants.baseUrl}/User/AuthenticateUser");

//   try {
//     final response = await http.post(
//       uri,
//       headers: {'Content-Type': 'application/json'},
//       body: jsonEncode({
//         'email': request.email,
//         'password': request.password,
//       }),
//     );

//     if (response.statusCode == 200) {
//       final jsonData = jsonDecode(response.body);
//       return AuthResponse.fromJson(jsonData);
//     } else {
//       final errorData = jsonDecode(response.body);

//       if (errorData['errorCode'] == '118000018') {
//         String errorMessage = errorData['errorMessage'];
//         String email = extractEmailFromMessage(errorMessage);
//         Get.to(() => Emailverify(email: email));
//         return null;
//       } else {
//         throw ApiException(message: errorData['errorMessage'] ?? 'Login failed');
//       }
//     }
//   } catch (e) {
//     print("Authentication error: $e"); // Optional logging
//     throw ApiException(message: 'Something went wrong: ${e.toString()}');
//   }
// }

// String extractEmailFromMessage(String message) {
//   // Try regex first
//   final emailRegex = RegExp(r'\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b');
//   final match = emailRegex.firstMatch(message);
//   if (match != null) return match.group(0)!;

//   // Try fallback method
//   int start = message.indexOf('(') + 1;
//   int end = message.indexOf(')');
//   if (start > 0 && end > start) {
//     return message.substring(start, end);
//   }

//   return '';
// }

// // Navigation function
// void navigateToOtpVerification(String email) {
//   // Yahan tumhara navigation logic hoga
//   // Example for GetX:
//   // Get.to(() => OtpVerificationScreen(email: email));
  
//   // Example for Navigator:
//   // Navigator.push(
//   //   context,
//   //   MaterialPageRoute(
//   //     builder: (context) => OtpVerificationScreen(email: email),
//   //   ),
//   // );
  
//   print('Redirecting to OTP Verification for email: $email');
// }

// // Complete login function with error handling
// Future<void> handleLogin(AuthRequest request, BuildContext context) async {
//   try {
//     AuthResponse? response = await authenticateUser(request );
    
//     if (response != null) {
//       // Login successful
//       print('Login successful');
//       // Navigate to home screen
//     } else {
//       // Login failed or email verification needed
//       print('Login failed or verification needed');
//     }
//   } catch (e) {
//     print('Login error: $e');
//   }
// }
// Future<List<PostModel>> fetchPosts() async {
//   await _loadUserDataFromPrefs();
//   final uri =
//       Uri.parse("${ApiConstants.baseUrl}/Post/GetPostsByUser/$userId");
//   final response = await http.get(
//     uri,
//     headers: {
//       'Content-Type': 'application/json',
//       'Authorization': 'Bearer $token',
//     },
//   );

//   if (response.statusCode == 200) {
//     final decoded = jsonDecode(response.body);
//     final List jsonData = decoded['data'] ?? [];

//     return jsonData
//         .where((e) {
//   final mediaType = e['mediaType']?.toLowerCase();
//   return mediaType == 'image' || mediaType == 'audio,image';
// })
//         .map((e) => PostModel.fromJson(e))
//         .toList();
//   } else {
//     throw Exception('Failed to fetch posts');
//   }
// }

//   Future<List<PostModel>> OtherfetchPosts(int userID) async {
//   final uri = Uri.parse("${ApiConstants.baseUrl}/Post/GetPostsByUser/$userID");

//   final response = await http.get(
//     uri,
//     headers: {
//       'Content-Type': 'application/json',
//       'Authorization': 'Bearer $token',
//     },
//   );

//   if (response.statusCode == 200) {
//     final decoded = jsonDecode(response.body);
//     final List jsonData = decoded['data'] ?? [];

//     return jsonData
//         .where((e) {
//           final mediaType = e['mediaType']?.toLowerCase();
//           return mediaType == 'image' || mediaType == 'audio,image';
//         })
//         .map((e) => PostModel.fromJson(e))
//         .toList();
//   } else {
//     throw Exception('Failed to fetch posts');
//   }
// }


//   // Fetch reels based on user ID
//   // In ApiService.dart
//   Future<List<ReelModel>> fetchReels(String userId) async {
//     await _loadUserDataFromPrefs();

//     final uri =
//         Uri.parse("${ApiConstants.baseUrl}/Post/GetPostsByUser/$userId");

//     final response = await http.get(
//       uri,
//       headers: {
//         'Content-Type': 'application/json',
//         'Authorization': 'Bearer $token',
//       },
//     );
//     if (response.statusCode == 200) {
//       final decoded = jsonDecode(response.body);
//       final List jsonData = decoded['data'] ?? [];

//       return jsonData
//           .where((e) => e['mediaType'] == 'video')
//           .map((e) => ReelModel.fromJson(e))
//           .toList();
//     } else {
//       throw Exception('Failed to fetch reels');
//     }
//   }

//  // Fetch all posts
// Future<List<PostModel>> fetchAllPosts() async {
//   await _loadUserDataFromPrefs();
//   final uri = Uri.parse("${ApiConstants.baseUrl}/Post/GetAllPosts");

//   final response = await http.get(
//     uri,
//     headers: {
//       'Content-Type': 'application/json',
//       'Authorization': 'Bearer $token',
//     },
//   );

//   if (response.statusCode == 200) {
//     final decoded = jsonDecode(response.body);
//     final List jsonData = decoded['data'] ?? [];

//     return jsonData
//         .where((e) {
//           final mediaType = e['mediaType']?.toLowerCase();
//           return mediaType == 'image' || mediaType == 'audio,image';
//         })
//         .map((e) => PostModel.fromJson(e))
//         .toList(); // ✅ Yeh zaroori hai
//   } else {
//     throw Exception('Failed to fetch all posts');
//   }
// }


//   // Fetch all reels
//   Future<List<ReelModel>> fetchAllReels() async {
//     await _loadUserDataFromPrefs();
//     final uri = Uri.parse("${ApiConstants.baseUrl}/Post/GetAllPosts");

//     final response = await http.get(
//       uri,
//       headers: {
//         'Content-Type': 'application/json',
//         'Authorization': 'Bearer $token',
//       },
//     );

//     if (response.statusCode == 200) {
//       final decoded = jsonDecode(response.body);
//       final List jsonData = decoded['data'] ?? [];

//       return jsonData
//           .where((e) => e['mediaType'] == 'video') // Only video posts
//           .map((e) => ReelModel.fromJson(e))
//           .toList();
//     } else {
//       throw Exception('Failed to fetch all reels');
//     }
//   }

//   Future<bool> updateUserProfile({
//     required String username,
//     required String profileImageBase64,
//     required String coverImageBase64,
//     required String? password,
//   }) async {
//     await _loadUserDataFromPrefs();

//     final uri = Uri.parse("${ApiConstants.baseUrl}/User/UpdateProfile");

//     // Build body dynamically
//     final Map<String, dynamic> body = {
//       'userId': userId,
//       'username': username,
//       'profileImage': profileImageBase64,
//       'coverImage': coverImageBase64,
//     };

//     if (password != null && password.isNotEmpty) {
//       body['password'] = password;
//     }

//     final response = await http.put(
//       uri,
//       headers: {
//         'Content-Type': 'application/json',
//         'Authorization': 'Bearer $token',
//       },
//       body: jsonEncode(body),
//     );

//     if (response.statusCode == 200) {
//       final jsonData = jsonDecode(response.body);
//       return jsonData['success'] ?? false;
//     } else {
//       return false;
//     }
//   }

//   Future<Map<String, dynamic>?> fetchUserProfile(
//       String userId, String token) async {
//     final uri =
//         Uri.parse("${ApiConstants.baseUrl}/User/GetUserById?id=$userId");

//     final response = await http.get(
//       uri,
//       headers: {
//         'Content-Type': 'application/json',
//         'Authorization': 'Bearer $token',
//       },
//     );

//     if (response.statusCode == 200) {
//       try {
//         final decoded = jsonDecode(response.body);
//         // Directly return decoded object since it's not wrapped in 'data'
//         return decoded;
//       } catch (e) {
//         return null;
//       }
//     } else {
//       return null;
//     }
//   }

//   // Update user profile
//   Future<bool> updateProfile(String newUsername, String? newpassword,
//       String newProfileImageBase64, String newCoverImageBase64) async {
//     await _loadUserDataFromPrefs();

//     final uri = Uri.parse("${ApiConstants.baseUrl}/User/UpdateUserProfile");

//     final response = await http.put(
//       uri,
//       headers: {
//         'Content-Type': 'application/json',
//         'Authorization': 'Bearer $token',
//       },
//       body: jsonEncode({
//         'id': userId,
//         'username': newUsername,
//         'Password': newpassword ?? '',
//         'ProfilePicture':
//             newProfileImageBase64, // Assuming the image is in base64
//         'CoverPicture': newCoverImageBase64, // Assuming the image is in base64
//       }),
//     );

//     if (response.statusCode == 200) {
//       final jsonData = jsonDecode(response.body);
//       if (jsonData['success'] == true) {
//         // Profile update was successful
//         return true;
//       } else {
//         // Handle failure based on success flag in the response
//         _showTopSnackBar(
//             "Error", "Failed to update profile: ${jsonData['message']}");
//         return false;
//       }
//     } else {
//       _showTopSnackBar("Error", "Failed to update profile.");
//       return false;
//     }
//   }

// // FOLLOWERS/FOLLOWING COUNTS
//   Future<Map<String, int>> fetchFollowersAndFollowing(int userId) async {
//     try {
//       final followersUri = Uri.parse(
//           "${ApiConstants.baseUrl}/Follow/GetFollowers?userId=$userId");

//       final followingUri = Uri.parse(
//           "${ApiConstants.baseUrl}/Follow/GetFollowing?userId=$userId");

//       final followersResponse = await http.get(followersUri);
//       final followingResponse = await http.get(followingUri);

//       if (followersResponse.statusCode == 200 &&
//           followingResponse.statusCode == 200) {
//         final followers = jsonDecode(followersResponse.body);
//         final following = jsonDecode(followingResponse.body);

//         return {
//           'followers': followers.length,
//           'following': following.length,
//         };
//       } else {
//         throw Exception("Failed to fetch counts");
//       }
//     } catch (e) {
//       return {
//         'followers': 0,
//         'following': 0,
//       };
//     }
//   }

//   static Future<bool> toggleFollow(String followerId, int followeeId) async {
//     final url = Uri.parse("${ApiConstants.baseUrl}/Follow/ToggleFollow");

//     final body = jsonEncode({
//       "followerId": followerId,
//       "followeeId": followeeId,
//     });

//     try {
//       final response = await http.post(
//         url,
//         headers: {
//           'Content-Type': 'application/json',
//         },
//         body: body,
//       );

//       if (response.statusCode == 200) {
//         return true;
//       } else {
//         return false;
//       }
//     } catch (e) {
//       return false;
//     }
//   }

//   Future<bool> deletePost(int postId) async {
//     final url = Uri.parse('${ApiConstants.baseUrl}/Post/DeletePost?postId=$postId');
//     final response = await http.delete(
//       url,
//       headers: {
//         'Content-Type': 'application/json',
//         'Authorization': 'Bearer $token',
//       },
//       body: jsonEncode({'id': postId}),
//     );

//     return response.statusCode == 200;
//   }

//   static Future<Map<String, dynamic>> createEvent({
//     required String userId,
//     required String username,
//     required String eventName,
//     required String eventDate,
//     required String eventTime,
//     required String eventLocation,
//     required String eventDescription,
//     required String eventProfileImage,
//     required String eventCoverImage,
//   }) async {
//     try {
//       final prefs = await SharedPreferences.getInstance();
//       final token = prefs.getString('user_token') ?? '';

//       final url = Uri.parse('${ApiConstants.baseUrl}/Events/CreateEvent');

//       final response = await http.post(
//         url,
//         headers: {
//           'Content-Type': 'application/json',
//           'Authorization': 'Bearer $token',
//         },
//         body: jsonEncode({
//           'userId': userId,
//           'eventName': eventName,
//           'eventDate': eventDate,
//           'eventTime': eventTime,
//           'eventLocation': eventLocation,
//           'eventDescription': eventDescription,
//           'eventProfileImage': eventProfileImage,
//           'eventCoverImage': eventCoverImage,
//           "HOSTED BY": username,
//         }),
//       );

//       if (response.statusCode == 200 || response.statusCode == 201) {
//         return {
//           'success': true,
//           'data': jsonDecode(response.body),
//           'message': 'Event created successfully',
//         };
//       } else {
//         final errorData = jsonDecode(response.body);
//         return {
//           'success': false,
//           'message': errorData['message'] ?? 'Failed to create event',
//         };
//       }
//     } catch (e) {
//       return {
//         'success': false,
//         'message': 'Error creating event: $e',
//       };
//     }
//   }

//   static Future<bool> toggleEventResponse({
//     required int eventId,
//     required String userId,
//     required String response,
//   }) async {
//     final url = Uri.parse('${ApiConstants.baseUrl}/Events/ToggleEventResponse');

//     final body = jsonEncode({
//       "eventId": eventId,
//       "userId": userId,
//       "response": response,
//     });

//     try {
//       final res = await http.post(
//         url,
//         headers: {'Content-Type': 'application/json'},
//         body: body,
//       );

//       if (res.statusCode == 200) {
//         return true;
//       } else {
//         return false;
//       }
//     } catch (e) {
//       return false;
//     }
//   }

//   static Future<bool> toggleEventFollow(
//       String followerId, int followeeId) async {
//     final url = Uri.parse("${ApiConstants.baseUrl}/Events/ToggleFollowEvent");

//     final body = jsonEncode({
//       "userId": followerId,
//       "eventId": followeeId,
//     });

//     try {
//       final response = await http.post(
//         url,
//         headers: {
//           'Content-Type': 'application/json',
//         },
//         body: body,
//       );

//       if (response.statusCode == 200) {
//         return true;
//       } else {
//         return false;
//       }
//     } catch (e) {
//       return false;
//     }
//   }

//   Future<bool> hasFollowEvent(String userId, int eventId) async {
//     try {
//       final uri = Uri.parse(
//           '${ApiConstants.baseUrl}/Events/GetFollowStats?userId=$userId&eventId=$eventId');
//       final response = await http.get(uri);

//       if (response.statusCode == 200) {
//         final data = jsonDecode(response.body);
//         return data['isFollowing'] ?? false;
//       }
//       return false;
//     } catch (e) {
//       return false;
//     }
//   }

//   Future<String> hasResponse(String userId, int eventId) async {
//     try {
//       final uri = Uri.parse(
//           '${ApiConstants.baseUrl}/Events/GetUserEventStatueResponse?userId=$userId&eventId=$eventId');
//       final response = await http.get(uri);

//       if (response.statusCode == 200) {
//         final data = jsonDecode(response.body);
//         return data['response'] ?? false;
//       }
//       return 'false';
//     } catch (e) {
//       return 'false';
//     }
//   }
// }

// void _showTopSnackBar(String title, String message, {bool isError = true}) {
//   Get.snackbar(
//     title,
//     message,
//     backgroundColor: isError
//         ? AppConstant.appsecondaryColor.withOpacity(0.8)
//         : Colors.green.withOpacity(0.8),
//     colorText: Colors.white,
//     snackPosition: SnackPosition.TOP,
//     margin: const EdgeInsets.all(8),
//     borderRadius: 12,
//     icon: Icon(
//       isError ? Icons.error_outline : Icons.check_circle_outline,
//       color: Colors.white,
//     ),
//     duration: const Duration(seconds: 3),
//     animationDuration: const Duration(milliseconds: 900),
//   );
// }
// class ApiException implements Exception {
//   final String? message;
//   ApiException({this.message});

//   @override
//   String toString() => message ?? 'ApiException';
// }