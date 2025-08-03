//old home page
// import 'dart:convert';
// import 'dart:io';
// import 'package:flutter/material.dart';
// import 'package:http/http.dart' as http;
// import 'package:loyalty_app/Auth/LanguageSelectionPage.dart';
// import 'package:loyalty_app/Services/language_service.dart';
// import 'package:shared_preferences/shared_preferences.dart';
// import 'package:cached_network_image/cached_network_image.dart';
// // import 'package:flutter_gen/gen_l10n/app_localizations.dart';

// // Model classes for API responses
// class BannerModel {
//   final String imageUrl;
//   final String title;

//   BannerModel({required this.imageUrl, required this.title});

//   factory BannerModel.fromJson(Map<String, dynamic> json) {
//     return BannerModel(
//       imageUrl: json['imageUrl'] ?? '',
//       title: json['title'] ?? '',
//     );
//   }
// }

// class HomeScreenMessage {
//   final String message;
//   final String? subtitle;

//   HomeScreenMessage({required this.message, this.subtitle});

//   factory HomeScreenMessage.fromJson(Map<String, dynamic> json) {
//     return HomeScreenMessage(
//       message: json['message'] ?? '',
//       subtitle: json['subtitle'],
//     );
//   }
// }

// class HomeScreen extends StatefulWidget {
//   final Function(int) onNavItemTapped;
//   final int currentIndex;

//   const HomeScreen({
//     super.key,
//     required this.onNavItemTapped,
//     required this.currentIndex,
//   });

//   @override
//   State<HomeScreen> createState() => _HomeScreenState();
// }

// class _HomeScreenState extends State<HomeScreen> with TickerProviderStateMixin, AutomaticKeepAliveClientMixin {
//   late AnimationController _fadeController;
//   late AnimationController _slideController;
//   late AnimationController _pulseController;
//   late Animation<double> _fadeAnimation;
//   late Animation<Offset> _slideAnimation;
//   late Animation<double> _pulseAnimation;

//   String totalPoints = "0";
//   List<BannerModel> banners = [];
//   HomeScreenMessage? homeMessage;
//   bool _isLoading = false;
//   bool _hasError = false;
//   String? _errorMessage;

//   @override
//   bool get wantKeepAlive => true;

//   @override
//   void initState() {
//     super.initState();
//     _initializeAnimations();
//     _loadAllHomeData();
//     print('init chal gaya');
//   }

//   @override
//   void didUpdateWidget(HomeScreen oldWidget) {
//     super.didUpdateWidget(oldWidget);
//     // Check if this screen became active
//     if (widget.currentIndex == 0 && oldWidget.currentIndex != 0) {
//       _loadAllHomeData();
//     }
//   }

//   void _initializeAnimations() {
//     _fadeController = AnimationController(
//       duration: const Duration(milliseconds: 1000),
//       vsync: this,
//     );
//     _slideController = AnimationController(
//       duration: const Duration(milliseconds: 800),
//       vsync: this,
//     );
//     _pulseController = AnimationController(
//       duration: const Duration(milliseconds: 2000),
//       vsync: this,
//     );

//     _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
//       CurvedAnimation(parent: _fadeController, curve: Curves.easeInOut),
//     );
//     _slideAnimation = Tween<Offset>(
//       begin: const Offset(0, 0.3),
//       end: Offset.zero,
//     ).animate(
//       CurvedAnimation(parent: _slideController, curve: Curves.easeOutCubic),
//     );
//     _pulseAnimation = Tween<double>(begin: 0.8, end: 1.0).animate(
//       CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
//     );

//     _fadeController.forward();
//     _slideController.forward();
//     _pulseController.repeat(reverse: true);
//   }

//   // Load all home screen data
//   Future<void> _loadAllHomeData() async {
//     await Future.wait([
//       loadTotalPoints(),
//       loadBanners(),
//       loadHomeMessage(),
//     ]);
//   }

//   // Load total points (existing method)
//   Future<void> loadTotalPoints() async {
//     if (_isLoading) return;

//     setState(() {
//       _isLoading = true;
//       _hasError = false;
//       _errorMessage = null;
//     });

//     try {
//       final prefs = await SharedPreferences.getInstance();
//       final companyUrl = prefs.getString('company_url');
//       final softwareType = prefs.getString('software_type');
//       final clientID = prefs.getString('clientID');
//       final trdr = prefs.getString('TRDR');

//       if (companyUrl == null || softwareType == null || clientID == null || trdr == null) {
//         throw Exception("Missing required SharedPreferences values");
//       }

//       final servicePath = softwareType == "TESAE"
//           ? "/pegasus/a_xit/connector.php"
//           : "/s1services";

//       final uri = Uri.parse("${ApiConstants.baseUrl}https://$companyUrl$servicePath");

//       debugPrint("🔄 Making API call to: $uri");

//       final requestBody = {
//         "service": "SqlData",
//         "clientID": clientID,
//         "appId": "1001",
//         "SqlName": "9700",
//         "trdr": trdr,
//       };

//       final response = await http.post(
//         uri,
//         headers: {
//           'Content-Type': 'application/json',
//           'Accept': 'application/json',
//           'User-Agent': 'LoyaltyApp/1.0',
//         },
//         body: jsonEncode(requestBody),
//       ).timeout(const Duration(seconds: 10), onTimeout: () {
//         throw Exception("Request timeout");
//       });

//       debugPrint("📥 Response status: ${response.statusCode}");
//       debugPrint("📥 Response body: ${response.body}");

//       if (response.statusCode == 200) {
//         final data = jsonDecode(response.body);
//         if (data is Map<String, dynamic> &&
//             data['success'] == true &&
//             data['rows'] != null &&
//             data['rows'].isNotEmpty) {
//           final points = data['rows'][0]['totalpoints']?.toString() ?? "0";

//           /// ✅ Save to SharedPreferences
//           await prefs.setString('totalPoints', points);

//           if (mounted) {
//             setState(() {
//               totalPoints = points;
//               _isLoading = false;
//               _hasError = false;
//             });
//           }
//           return;
//         } else {
//           throw Exception("Invalid response or no data");
//         }
//       } else {
//         throw Exception("HTTP ${response.statusCode}: ${response.body}");
//       }
//     } catch (e) {
//       debugPrint("❗ Error loading total points: $e");
//       if (mounted) {
//         setState(() {
//           _isLoading = false;
//           _hasError = true;
//           _errorMessage = e.toString();
//         });
//       }
//     }
//   }

//   // Load banners (SqlName: 9703)
//   Future<void> loadBanners() async {
//     try {
//       final prefs = await SharedPreferences.getInstance();
//       final companyUrl = prefs.getString('company_url');
//       final softwareType = prefs.getString('software_type');
//       final clientID = prefs.getString('clientID');
//       final trdr = prefs.getString('TRDR');

//       if (companyUrl == null || softwareType == null || clientID == null || trdr == null) {
//         throw Exception("Missing required SharedPreferences values");
//       }

//       final servicePath = softwareType == "TESAE"
//           ? "/pegasus/a_xit/connector.php"
//           : "/s1services";

//       final uri = Uri.parse("${ApiConstants.baseUrl}https://$companyUrl$servicePath");

//       debugPrint("🔄 Loading banners from: $uri");

//       final requestBody = {
//         "service": "SqlData",
//         "clientID": clientID,
//         "appId": "1001",
//         "SqlName": "9703",
//         "trdr": trdr,
//       };

//       final response = await http.post(
//         uri,
//         headers: {
//           'Content-Type': 'application/json',
//           'Accept': 'application/json',
//           'User-Agent': 'LoyaltyApp/1.0',
//         },
//         body: jsonEncode(requestBody),
//       ).timeout(const Duration(seconds: 10));

//       debugPrint("📥 Banners response: ${response.statusCode}");
//       debugPrint("📥 Banners body: ${response.body}");

//       if (response.statusCode == 200) {
//         final data = jsonDecode(response.body);
//         if (data is Map<String, dynamic> &&
//             data['success'] == true &&
//             data['rows'] != null) {
//           final List<dynamic> bannerData = data['rows'];
//           final List<BannerModel> loadedBanners = bannerData
//               .map((banner) => BannerModel.fromJson(banner))
//               .toList();

//           if (mounted) {
//             setState(() {
//               banners = loadedBanners;
//             });
//           }
//         }
//       }
//     } catch (e) {
//       debugPrint("❗ Error loading banners: $e");
//     }
//   }

//   // Load home screen message (SqlName: 9704)
//   Future<void> loadHomeMessage() async {
//     try {
//       final prefs = await SharedPreferences.getInstance();
//       final companyUrl = prefs.getString('company_url');
//       final softwareType = prefs.getString('software_type');
//       final clientID = prefs.getString('clientID');
//       final trdr = prefs.getString('TRDR');

//       if (companyUrl == null || softwareType == null || clientID == null || trdr == null) {
//         throw Exception("Missing required SharedPreferences values");
//       }

//       final servicePath = softwareType == "TESAE"
//           ? "/pegasus/a_xit/connector.php"
//           : "/s1services";

//       final uri = Uri.parse("${ApiConstants.baseUrl}https://$companyUrl$servicePath");

//       debugPrint("🔄 Loading home message from: $uri");

//       final requestBody = {
//         "service": "SqlData",
//         "clientID": clientID,
//         "appId": "1001",
//         "SqlName": "9704",
//         "trdr": trdr,
//       };

//       final response = await http.post(
//         uri,
//         headers: {
//           'Content-Type': 'application/json',
//           'Accept': 'application/json',
//           'User-Agent': 'LoyaltyApp/1.0',
//         },
//         body: jsonEncode(requestBody),
//       ).timeout(const Duration(seconds: 10));

//       debugPrint("📥 Home message response: ${response.statusCode}");
//       debugPrint("📥 Home message body: ${response.body}");

//       if (response.statusCode == 200) {
//         final data = jsonDecode(response.body);
//         if (data is Map<String, dynamic> &&
//             data['success'] == true &&
//             data['rows'] != null &&
//             data['rows'].isNotEmpty) {
//           final messageData = data['rows'][0];
//           final message = HomeScreenMessage.fromJson(messageData);

//           if (mounted) {
//             setState(() {
//               homeMessage = message;
//             });
//           }
//         }
//       }
//     } catch (e) {
//       debugPrint("❗ Error loading home message: $e");
//     }
//   }

//   @override
//   Widget build(BuildContext context) {
//     super.build(context);
//     final localizations = AppLocalizations.of(context)!;

//     return Scaffold(
//       backgroundColor: Colors.grey.shade50,
//       appBar: _buildAppBar(localizations),
//       body: _buildHomeContent(localizations),
//     );
//   }

//   PreferredSizeWidget _buildAppBar(AppLocalizations localizations) {
//     return AppBar(
//       backgroundColor: Colors.black,
//       automaticallyImplyLeading: false,
//       elevation: 0,
//       title: FadeTransition(
//         opacity: _fadeAnimation,
//         child: Row(
//           mainAxisAlignment: MainAxisAlignment.spaceBetween,
//           children: [
//             Expanded(
//               child: Text.rich(
//                 TextSpan(
//                   text: '${localizations.appTitle} ',
//                   style: const TextStyle(
//                     color: Colors.white,
//                     fontWeight: FontWeight.bold,
//                     fontSize: 18,
//                     letterSpacing: 0.5,
//                   ),
//                   children: [
//                     TextSpan(
//                       text: localizations.appSubtitle,
//                       style: TextStyle(
//                         color: Colors.orange.shade400,
//                         fontWeight: FontWeight.w900,
//                         fontSize: 14,
//                       ),
//                     ),
//                   ],
//                 ),
//               ),
//             ),
//             IconButton(
//               onPressed: () {
//                 Navigator.push(
//                   context,
//                   MaterialPageRoute(
//                     builder: (context) => const LanguageSelectionPage(),
//                   ),
//                 );
//               },
//               icon: const Icon(Icons.language, color: Colors.white, size: 24),
//               tooltip: localizations.changeLanguage,
//             ),
//           ],
//         ),
//       ),
//     );
//   }

//   Widget _buildHomeContent(AppLocalizations localizations) {
//     return RefreshIndicator(
//       onRefresh: _loadAllHomeData,
//       color: Colors.orange,
//       backgroundColor: Colors.white,
//       strokeWidth: 2.0,
//       child: SingleChildScrollView(
//         physics: const AlwaysScrollableScrollPhysics(),
//         child: Column(
//           crossAxisAlignment: CrossAxisAlignment.start,
//           children: [
//             _buildBalanceSection(localizations),
//             _buildGreetingSection(localizations),
//             const SizedBox(height: 24),
//             if (banners.isNotEmpty) _buildBannersSection(localizations),
//             if (banners.isNotEmpty) const SizedBox(height: 24),
//             _buildImageSection(localizations),
//             const SizedBox(height: 100),
//           ],
//         ),
//       ),
//     );
//   }

//   Widget _buildBalanceSection(AppLocalizations localizations) {
//     return SlideTransition(
//       position: _slideAnimation,
//       child: Container(
//         margin: const EdgeInsets.all(16),
//         padding: const EdgeInsets.all(20),
//         decoration: BoxDecoration(
//           gradient: LinearGradient(
//             colors: [Colors.white, Colors.grey.shade50],
//             begin: Alignment.topLeft,
//             end: Alignment.bottomRight,
//           ),
//           borderRadius: BorderRadius.circular(16),
//           boxShadow: [
//             BoxShadow(
//               color: Colors.black.withOpacity(0.08),
//               spreadRadius: 0,
//               blurRadius: 15,
//               offset: const Offset(0, 4),
//             ),
//           ],
//         ),
//         child: Column(
//           children: [
//             Row(
//               mainAxisAlignment: MainAxisAlignment.spaceBetween,
//               children: [
//                 Expanded(
//                   child: Column(
//                     crossAxisAlignment: CrossAxisAlignment.start,
//                     children: [
//                       Text(
//                         localizations.myBalance,
//                         style: TextStyle(
//                           color: Colors.grey.shade600,
//                           fontSize: 14,
//                           fontWeight: FontWeight.w500,
//                         ),
//                       ),
//                       const SizedBox(height: 8),
//                       _buildPointsDisplay(localizations),
//                     ],
//                   ),
//                 ),
//                 _buildBalanceIcon(),
//               ],
//             ),
//             if (_hasError) _buildErrorSection(),
//           ],
//         ),
//       ),
//     );
//   }

//   Widget _buildPointsDisplay(AppLocalizations localizations) {
//     if (_isLoading) {
//       return Row(
//         children: [
//           const SizedBox(
//             width: 20,
//             height: 20,
//             child: CircularProgressIndicator(
//               strokeWidth: 2,
//               valueColor: AlwaysStoppedAnimation<Color>(Colors.orange),
//             ),
//           ),
//           const SizedBox(width: 12),
//           Text(
//             "Loading...",
//             style: TextStyle(
//               fontWeight: FontWeight.bold,
//               fontSize: 18,
//               color: Colors.grey.shade600,
//             ),
//           ),
//         ],
//       );
//     }

//     return ScaleTransition(
//       scale: _pulseAnimation,
//       child: Text(
//         "$totalPoints ${localizations.points}",
//         style: const TextStyle(
//           fontWeight: FontWeight.bold,
//           fontSize: 28,
//           color: Colors.orange,
//           letterSpacing: 0.5,
//         ),
//       ),
//     );
//   }

//   Widget _buildBalanceIcon() {
//     return Container(
//       padding: const EdgeInsets.all(12),
//       decoration: BoxDecoration(
//         color: Colors.orange.withOpacity(0.1),
//         borderRadius: BorderRadius.circular(12),
//       ),
//       child: Icon(
//         _hasError ? Icons.error_outline : Icons.account_balance_wallet,
//         color: _hasError ? Colors.red : Colors.orange,
//         size: 24,
//       ),
//     );
//   }

//   Widget _buildErrorSection() {
//     return Container(
//       margin: const EdgeInsets.only(top: 16),
//       padding: const EdgeInsets.all(12),
//       decoration: BoxDecoration(
//         color: Colors.red.shade50,
//         borderRadius: BorderRadius.circular(8),
//         border: Border.all(color: Colors.red.shade200),
//       ),
//       child: Row(
//         children: [
//           Icon(Icons.warning, color: Colors.red.shade600, size: 20),
//           const SizedBox(width: 8),
//           Expanded(
//             child: Text(
//               "Failed to load points",
//               style: TextStyle(
//                 color: Colors.red.shade700,
//                 fontSize: 12,
//                 fontWeight: FontWeight.w500,
//               ),
//             ),
//           ),
//           TextButton(
//             onPressed: _loadAllHomeData,
//             child: Text(
//               "Retry",
//               style: TextStyle(
//                 color: Colors.red.shade600,
//                 fontWeight: FontWeight.bold,
//               ),
//             ),
//           ),
//         ],
//       ),
//     );
//   }

//   Widget _buildGreetingSection(AppLocalizations localizations) {
//     return FadeTransition(
//       opacity: _fadeAnimation,
//       child: Container(
//         margin: const EdgeInsets.symmetric(horizontal: 16),
//         padding: const EdgeInsets.all(24),
//         decoration: BoxDecoration(
//           gradient: LinearGradient(
//             colors: [Colors.orange.shade50, Colors.orange.shade100],
//             begin: Alignment.topLeft,
//             end: Alignment.bottomRight,
//           ),
//           borderRadius: BorderRadius.circular(16),
//           border: Border.all(color: Colors.orange.shade200, width: 1),
//         ),
//         child: Column(
//           crossAxisAlignment: CrossAxisAlignment.start,
//           children: [
//             Row(
//               children: [
//                 Container(
//                   padding: const EdgeInsets.all(8),
//                   decoration: BoxDecoration(
//                     color: Colors.orange,
//                     borderRadius: BorderRadius.circular(8),
//                   ),
//                   child: const Icon(
//                     Icons.wb_sunny,
//                     color: Colors.white,
//                     size: 20,
//                   ),
//                 ),
//                 const SizedBox(width: 12),
//                 Expanded(
//                   child: Text(
//                     homeMessage?.message ?? localizations.greeting,
//                     style: const TextStyle(
//                       color: Colors.orange,
//                       fontSize: 22,
//                       fontWeight: FontWeight.bold,
//                     ),
//                   ),
//                 ),
//               ],
//             ),
//             const SizedBox(height: 16),
//             Text(
//               homeMessage?.subtitle ?? localizations.glowMoreEarnMore,
//               style: const TextStyle(
//                 fontSize: 18,
//                 color: Colors.black87,
//                 fontWeight: FontWeight.w600,
//                 height: 1.3,
//               ),
//             ),
//             const SizedBox(height: 4),
//             Text(
//               localizations.dailyBeautyRewards,
//               style: TextStyle(
//                 fontSize: 14,
//                 color: Colors.grey.shade700,
//                 height: 1.4,
//               ),
//             ),
//           ],
//         ),
//       ),
//     );
//   }

//   // New banners section
//   Widget _buildBannersSection(AppLocalizations localizations) {
//     return SlideTransition(
//       position: _slideAnimation,
//       child: Container(
//         margin: const EdgeInsets.symmetric(horizontal: 16),
//         child: Column(
//           crossAxisAlignment: CrossAxisAlignment.start,
//           children: [
//             Text(
//               "Special Offers",
//               style: TextStyle(
//                 fontSize: 20,
//                 fontWeight: FontWeight.bold,
//                 color: Colors.grey.shade800,
//               ),
//             ),
//             const SizedBox(height: 12),
//             SizedBox(
//               height: 180,
//               child: ListView.builder(
//                 scrollDirection: Axis.horizontal,
//                 itemCount: banners.length,
//                 padding: const EdgeInsets.symmetric(horizontal: 4),
//                 itemBuilder: (context, index) {
//                   final banner = banners[index];
//                   return Container(
//                     width: 280,
//                     margin: const EdgeInsets.only(right: 16),
//                     decoration: BoxDecoration(
//                       borderRadius: BorderRadius.circular(16),
//                       boxShadow: [
//                         BoxShadow(
//                           color: Colors.black.withOpacity(0.1),
//                           spreadRadius: 0,
//                           blurRadius: 15,
//                           offset: const Offset(0, 4),
//                         ),
//                       ],
//                     ),
//                     child: ClipRRect(
//                       borderRadius: BorderRadius.circular(16),
//                       child: Stack(
//                         children: [
//                           CachedNetworkImage(
//                             imageUrl: banner.imageUrl,
//                             height: 180,
//                             width: double.infinity,
//                             fit: BoxFit.cover,
//                             placeholder: (context, url) => Container(
//                               color: Colors.grey.shade200,
//                               child: const Center(
//                                 child: CircularProgressIndicator(
//                                   valueColor: AlwaysStoppedAnimation<Color>(Colors.orange),
//                                 ),
//                               ),
//                             ),
//                             errorWidget: (context, url, error) => Container(
//                               color: Colors.grey.shade200,
//                               child: const Center(
//                                 child: Icon(
//                                   Icons.image_not_supported,
//                                   color: Colors.grey,
//                                   size: 40,
//                                 ),
//                               ),
//                             ),
//                           ),
//                           if (banner.title.isNotEmpty)
//                             Positioned(
//                               bottom: 0,
//                               left: 0,
//                               right: 0,
//                               child: Container(
//                                 padding: const EdgeInsets.all(16),
//                                 decoration: BoxDecoration(
//                                   gradient: LinearGradient(
//                                     colors: [
//                                       Colors.transparent,
//                                       Colors.black.withOpacity(0.7),
//                                     ],
//                                     begin: Alignment.topCenter,
//                                     end: Alignment.bottomCenter,
//                                   ),
//                                 ),
//                                 child: Text(
//                                   banner.title,
//                                   style: const TextStyle(
//                                     color: Colors.white,
//                                     fontSize: 16,
//                                     fontWeight: FontWeight.bold,
//                                   ),
//                                 ),
//                               ),
//                             ),
//                         ],
//                       ),
//                     ),
//                   );
//                 },
//               ),
//             ),
//           ],
//         ),
//       ),
//     );
//   }

//   Widget _buildImageSection(AppLocalizations localizations) {
//     return SlideTransition(
//       position: _slideAnimation,
//       child: Container(
//         margin: const EdgeInsets.all(16),
//         decoration: BoxDecoration(
//           borderRadius: BorderRadius.circular(20),
//           boxShadow: [
//             BoxShadow(
//               color: Colors.black.withOpacity(0.1),
//               spreadRadius: 0,
//               blurRadius: 20,
//               offset: const Offset(0, 8),
//             ),
//           ],
//         ),
//         child: ClipRRect(
//           borderRadius: BorderRadius.circular(20),
//           child: Stack(
//             children: [
//               SizedBox(
//                 width: double.infinity,
//                 height: 320,
//                 child: Image.asset(
//                   "assets/images/hair.png",
//                   fit: BoxFit.cover,
//                   errorBuilder: (context, error, stackTrace) {
//                     return Container(
//                       decoration: BoxDecoration(
//                         gradient: LinearGradient(
//                           colors: [
//                             Colors.grey.shade200,
//                             Colors.grey.shade300,
//                           ],
//                           begin: Alignment.topLeft,
//                           end: Alignment.bottomRight,
//                         ),
//                       ),
//                       child: Center(
//                         child: Column(
//                           mainAxisAlignment: MainAxisAlignment.center,
//                           children: [
//                             const Icon(
//                               Icons.image_not_supported,
//                               size: 50,
//                               color: Colors.grey,
//                             ),
//                             const SizedBox(height: 8),
//                             Text(
//                               localizations.imageNotFound,
//                               style: const TextStyle(
//                                 color: Colors.grey,
//                                 fontSize: 14,
//                               ),
//                             ),
//                           ],
//                         ),
//                       ),
//                     );
//                   },
//                 ),
//               ),
//               Positioned(
//                 bottom: 0,
//                 left: 0,
//                 right: 0,
//                 child: Container(
//                   padding: const EdgeInsets.all(20),
//                   decoration: BoxDecoration(
//                     gradient: LinearGradient(
//                       colors: [
//                         Colors.transparent,
//                         Colors.black.withOpacity(0.7),
//                       ],
//                       begin: Alignment.topCenter,
//                       end: Alignment.bottomCenter,
//                     ),
//                   ),
//                   child: Text(
//                     localizations.discoverBeautyServices,
//                     style: const TextStyle(
//                       color: Colors.white,
//                       fontSize: 18,
//                       fontWeight: FontWeight.bold,
//                     ),
//                   ),
//                 ),
//               ),
//             ],
//           ),
//         ),
//       ),
//     );
//   }

//   @override
//   void dispose() {
//     _fadeController.dispose();
//     _slideController.dispose();
//     _pulseController.dispose();
//     super.dispose();
//   }
// }

// new home page // Updated home page with dynamic name and custom star icon
import 'dart:convert';
import 'dart:io';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:loyalty_app/Auth/LanguageSelectionPage.dart';
import 'package:loyalty_app/Services/language_service.dart';
import 'package:loyalty_app/utils/api_constants.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:google_fonts/google_fonts.dart';
// import 'package:flutter_gen/gen_l10n/app_localizations.dart';

// Model classes for API responses
class BannerModel {
  final String imageUrl;
  final String title;

  BannerModel({required this.imageUrl, required this.title});

  factory BannerModel.fromJson(Map<String, dynamic> json) {
    return BannerModel(
      imageUrl: json['imageUrl'] ?? '',
      title: json['title'] ?? '',
    );
  }
}

class HomeScreenMessage {
  final String message;
  final String? subtitle;

  HomeScreenMessage({required this.message, this.subtitle});

  factory HomeScreenMessage.fromJson(Map<String, dynamic> json) {
    return HomeScreenMessage(
      message: json['message'] ?? '',
      subtitle: json['subtitle'],
    );
  }
}

class HomeScreen extends StatefulWidget {
  final Function(int) onNavItemTapped;
  final int currentIndex;

  const HomeScreen({
    super.key,
    required this.onNavItemTapped,
    required this.currentIndex,
  });

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen>
    with TickerProviderStateMixin, AutomaticKeepAliveClientMixin {
  late AnimationController _fadeController;
  late AnimationController _slideController;
  late AnimationController _pulseController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;
  late Animation<double> _pulseAnimation;

  String totalPoints = "0";
  String userName = "User";
  List<BannerModel> banners = [];
  HomeScreenMessage? homeMessage;
  bool _isLoading = false;
  bool _hasError = false;
  String? _errorMessage;
  String? profileImagePath;

  // Banner slider controller
  final PageController _bannerPageController = PageController();
  int _currentBannerIndex = 0;

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _initializeAnimations();
    _loadUserName();
    _loadAllHomeData();
    _loadProfileImage();
    _startBannerAutoSlide();
    print('init chal gaya');
  }

  @override
  void didUpdateWidget(HomeScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    // Check if this screen became active
    if (widget.currentIndex == 0 && oldWidget.currentIndex != 0) {
      _loadAllHomeData();
      _loadProfileImage(); // 🔥 FIX: Reload profile image when screen becomes active
    }
  }

  void _initializeAnimations() {
    _fadeController = AnimationController(
      duration: const Duration(milliseconds: 1000),
      vsync: this,
    );
    _slideController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );
    _pulseController = AnimationController(
      duration: const Duration(milliseconds: 2000),
      vsync: this,
    );

    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _fadeController, curve: Curves.easeInOut),
    );
    _slideAnimation =
        Tween<Offset>(begin: const Offset(0, 0.3), end: Offset.zero).animate(
          CurvedAnimation(parent: _slideController, curve: Curves.easeOutCubic),
        );
    _pulseAnimation = Tween<double>(begin: 0.8, end: 1.0).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );

    _fadeController.forward();
    _slideController.forward();
    _pulseController.repeat(reverse: true);
  }

  // 🔥 FIX: Auto-slide banners every 3 seconds
  void _startBannerAutoSlide() {
    Future.delayed(const Duration(seconds: 3), () {
      if (mounted && banners.isNotEmpty) {
        if (_currentBannerIndex < banners.length - 1) {
          _currentBannerIndex++;
        } else {
          _currentBannerIndex = 0;
        }

        _bannerPageController.animateToPage(
          _currentBannerIndex,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeInOut,
        );

        _startBannerAutoSlide(); // Continue auto-slide
      }
    });
  }

  // Load user name from SharedPreferences
  Future<void> _loadUserName() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final name = prefs.getString('user_fullname') ?? 'user';
      if (mounted) {
        setState(() {
          userName = name;
        });
      }
    } catch (e) {
      debugPrint("❗ Error loading user name: $e");
    }
  }

  // Load all home screen data
  Future<void> _loadAllHomeData() async {
    await Future.wait([loadTotalPoints(), loadBanners(), loadHomeMessage()]);
  }

  // Load total points (existing method)
  Future<void> loadTotalPoints() async {
    if (_isLoading) return;

    setState(() {
      _isLoading = true;
      _hasError = false;
      _errorMessage = null;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');
      final clientID = prefs.getString('clientID');
      final trdr = prefs.getString('TRDR');

      if (companyUrl == null ||
          softwareType == null ||
          clientID == null ||
          trdr == null) {
        throw Exception("Missing required SharedPreferences values");
      }

      final servicePath = softwareType == "TESAE"
          ? "/pegasus/a_xit/connector.php"
          : "/s1services";

      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://$companyUrl$servicePath",
      );

      debugPrint("🔄 Making API call to: $uri");

      final requestBody = {
        "service": "SqlData",
        "clientID": clientID,
        "appId": "1001",
        "SqlName": "9700",
        "trdr": trdr,
      };

      final response = await http
          .post(
            uri,
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'User-Agent': 'LoyaltyApp/1.0',
            },
            body: jsonEncode(requestBody),
          )
          .timeout(
            const Duration(seconds: 10),
            onTimeout: () {
              throw Exception("Request timeout");
            },
          );

      debugPrint("📥 Response status: ${response.statusCode}");
      debugPrint("📥 Response body: ${response.body}");

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map<String, dynamic> &&
            data['success'] == true &&
            data['rows'] != null &&
            data['rows'].isNotEmpty) {
          final points = data['rows'][0]['totalpoints']?.toString() ?? "0";

          /// ✅ Save to SharedPreferences
          await prefs.setString('totalPoints', points);

          if (mounted) {
            setState(() {
              totalPoints = points;
              _isLoading = false;
              _hasError = false;
            });
          }
          return;
        } else {
          throw Exception("Invalid response or no data");
        }
      } else {
        throw Exception("HTTP ${response.statusCode}: ${response.body}");
      }
    } catch (e) {
      debugPrint("❗ Error loading total points: $e");
      if (mounted) {
        setState(() {
          _isLoading = false;
          _hasError = true;
          _errorMessage = e.toString();
        });
      }
    }
  }

  // Load banners (SqlName: 9703)
  Future<void> loadBanners() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');
      final clientID = prefs.getString('clientID');
      final trdr = prefs.getString('TRDR');

      if (companyUrl == null ||
          softwareType == null ||
          clientID == null ||
          trdr == null) {
        throw Exception("Missing required SharedPreferences values");
      }

      final servicePath = softwareType == "TESAE"
          ? "/pegasus/a_xit/connector.php"
          : "/s1services";

      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://$companyUrl$servicePath",
      );

      debugPrint("🔄 Loading banners from: $uri");

      final requestBody = {
        "service": "SqlData",
        "clientID": clientID,
        "appId": "1001",
        "SqlName": "9703",
        "trdr": trdr,
      };

      final response = await http
          .post(
            uri,
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'User-Agent': 'LoyaltyApp/1.0',
            },
            body: jsonEncode(requestBody),
          )
          .timeout(const Duration(seconds: 10));

      debugPrint("📥 Banners response: ${response.statusCode}");
      debugPrint("📥 Banners body: ${response.body}");

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map<String, dynamic> &&
            data['success'] == true &&
            data['rows'] != null) {
          final List<dynamic> bannerData = data['rows'];
          final List<BannerModel> loadedBanners = bannerData
              .map((banner) => BannerModel.fromJson(banner))
              .toList();

          if (mounted) {
            setState(() {
              banners = loadedBanners;
            });
          }
        }
      }
    } catch (e) {
      debugPrint("❗ Error loading banners: $e");
    }
  }

  // Load home screen message (SqlName: 9704)
  Future<void> loadHomeMessage() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');
      final clientID = prefs.getString('clientID');
      final trdr = prefs.getString('TRDR');

      if (companyUrl == null ||
          softwareType == null ||
          clientID == null ||
          trdr == null) {
        throw Exception("Missing required SharedPreferences values");
      }

      final servicePath = softwareType == "TESAE"
          ? "/pegasus/a_xit/connector.php"
          : "/s1services";

      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://$companyUrl$servicePath",
      );

      debugPrint("🔄 Loading home message from: $uri");

      final requestBody = {
        "service": "SqlData",
        "clientID": clientID,
        "appId": "1001",
        "SqlName": "9704",
        "trdr": trdr,
      };

      final response = await http
          .post(
            uri,
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'User-Agent': 'LoyaltyApp/1.0',
            },
            body: jsonEncode(requestBody),
          )
          .timeout(const Duration(seconds: 10));

      debugPrint("📥 Home message response: ${response.statusCode}");
      debugPrint("📥 Home message body: ${response.body}");

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map<String, dynamic> &&
            data['success'] == true &&
            data['rows'] != null &&
            data['rows'].isNotEmpty) {
          final messageData = data['rows'][0];
          final message = HomeScreenMessage.fromJson(messageData);

          if (mounted) {
            setState(() {
              homeMessage = message;
            });
          }
        }
      }
    } catch (e) {
      debugPrint("❗ Error loading home message: $e");
    }
  }

  Future<void> _loadProfileImage() async {
    final prefs = await SharedPreferences.getInstance();
    final path = prefs.getString('user_profile_image');
    if (mounted) {
      setState(() {
        profileImagePath = path;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    // final localizations = AppLocalizations.of(context)!;

    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      body: RefreshIndicator(
        onRefresh: () async {
          await _loadAllHomeData();
          await _loadProfileImage(); // 🔥 FIX: Also reload profile image on refresh
        },
        color: const Color(0xFFEC7103),
        backgroundColor: Colors.white,
        strokeWidth: 2.0,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            children: [
              _buildHeaderSection(),
              _buildBalanceSection(),
              _buildBannerSlider(), // 🔥 NEW: Banner slider
              _buildContentSection(),
              _buildDiscoverButton(),
              const SizedBox(height: 100),
            ],
          ),
        ),
      ),
    );
  }

  // New banners section
  Widget _buildBannersSection(AppLocalizations localizations) {
    return SlideTransition(
      position: _slideAnimation,
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              "Special Offers",
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.grey.shade800,
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              height: 180,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: banners.length,
                padding: const EdgeInsets.symmetric(horizontal: 4),
                itemBuilder: (context, index) {
                  final banner = banners[index];
                  return Container(
                    width: 280,
                    margin: const EdgeInsets.only(right: 16),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.1),
                          spreadRadius: 0,
                          blurRadius: 15,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Stack(
                        children: [
                          CachedNetworkImage(
                            imageUrl: banner.imageUrl,
                            height: 180,
                            width: double.infinity,
                            fit: BoxFit.cover,
                            placeholder: (context, url) => Container(
                              color: Colors.grey.shade200,
                              child: const Center(
                                child: CircularProgressIndicator(
                                  valueColor: AlwaysStoppedAnimation<Color>(
                                     Color(0xFFEC7103),
                                  ),
                                ),
                              ),
                            ),
                            errorWidget: (context, url, error) => Container(
                              color: Colors.grey.shade200,
                              child: const Center(
                                child: Icon(
                                  Icons.image_not_supported,
                                  color: Colors.grey,
                                  size: 40,
                                ),
                              ),
                            ),
                          ),
                          if (banner.title.isNotEmpty)
                            Positioned(
                              bottom: 0,
                              left: 0,
                              right: 0,
                              child: Container(
                                padding: const EdgeInsets.all(16),
                                decoration: BoxDecoration(
                                  gradient: LinearGradient(
                                    colors: [
                                      Colors.transparent,
                                      Colors.black.withOpacity(0.7),
                                    ],
                                    begin: Alignment.topCenter,
                                    end: Alignment.bottomCenter,
                                  ),
                                ),
                                child: Text(
                                  banner.title,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeaderSection() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 50, 20, 30),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFFEC7103), Color(0xFFFF8A3D)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: SafeArea(
        child: Row(
          children: [
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white, width: 3),
              ),
              child: ClipOval(
                child:
                    (profileImagePath != null &&
                        profileImagePath!.isNotEmpty &&
                        File(profileImagePath!).existsSync())
                    ? Image.file(
                        File(profileImagePath!),
                        fit: BoxFit.cover,
                        errorBuilder: _defaultImageErrorBuilder,
                      )
                    : Image.asset(
                        'assets/images/profile_temp.jpg',
                        fit: BoxFit.cover,
                        errorBuilder: _defaultImageErrorBuilder,
                      ),
              ),
            ),

            const SizedBox(width: 15),
            // Welcome text with dynamic name
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Welcome',
                    style: GoogleFonts.dmSans(
                      fontSize: 16,
                      fontWeight: FontWeight.w500,
                      color: Colors.white,
                    ),
                  ),
                  Text(
                    userName, // Dynamic user name from SharedPreferences
                    style: GoogleFonts.dmSans(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            ),
            // Language button
            IconButton(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const LanguageSelectionPage(),
                  ),
                );
              },
              icon: const Icon(
                Icons.g_translate,
                color: Colors.white,
                size: 28,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _defaultImageErrorBuilder(
    BuildContext context,
    Object error,
    StackTrace? stackTrace,
  ) {
    return Container(
      decoration: const BoxDecoration(
        shape: BoxShape.circle,
        color: Colors.white,
      ),
      child: const Icon(Icons.person, color: Color(0xFFEC7103), size: 30),
    );
  }

  Widget _buildBalanceSection() {
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 30, 20, 0),
      child: Column(
        children: [
          Text(
            'My balance',
            style: GoogleFonts.dmSans(
              fontSize: 20,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 15),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 30, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey.shade400, width: 2),
              boxShadow: [
                BoxShadow(
                  color: const Color.fromARGB(
                    255,
                    134,
                    134,
                    134,
                  ).withOpacity(0.4),
                  blurRadius: 6,
                  offset: const Offset(9, 10),
                ),
              ],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  _isLoading ? 'Loading...' : '$totalPoints POINTS',
                  style: GoogleFonts.dmSans(
                    fontSize: 20, // Bigger text like in image
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFFEC7103),
                  ),
                ),
                const SizedBox(width: 8),
                // Bigger star icon
                Image.asset(
                  'assets/icons/star-icon.png',
                  width: 30,
                  height: 30,
                  errorBuilder: (context, error, stackTrace) {
                    return const Icon(
                      Icons.star,
                      color: Color(0xFFEC7103),
                      size: 35,
                    );
                  },
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // 🔥 NEW: Banner slider widget
  Widget _buildBannerSlider() {
    if (banners.isEmpty) {
      return const SizedBox.shrink(); // Hide if no banners
    }

    return Container(
      margin: const EdgeInsets.fromLTRB(20, 40, 20, 20),
      height: 180,
      child: Column(
        children: [
          // Banner PageView
          Expanded(
            child: PageView.builder(
              controller: _bannerPageController,
              onPageChanged: (index) {
                setState(() {
                  _currentBannerIndex = index;
                });
              },
              itemCount: banners.length,
              itemBuilder: (context, index) {
                final banner = banners[index];
                return Container(
                  margin: const EdgeInsets.symmetric(horizontal: 5),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(15),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.1),
                        blurRadius: 10,
                        offset: const Offset(0, 5),
                      ),
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(15),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        // Banner image
                        banner.imageUrl.isNotEmpty
                            ? Image.network(
                                banner.imageUrl,
                                fit: BoxFit.cover,
                                errorBuilder: (context, error, stackTrace) {
                                  return Container(
                                    color: const Color(
                                      0xFFEC7103,
                                    ).withOpacity(0.1),
                                    child: const Center(
                                      child: Icon(
                                        Icons.image,
                                        size: 50,
                                        color: Color(0xFFEC7103),
                                      ),
                                    ),
                                  );
                                },
                                loadingBuilder:
                                    (context, child, loadingProgress) {
                                      if (loadingProgress == null) return child;
                                      return Container(
                                        color: const Color(
                                          0xFFEC7103,
                                        ).withOpacity(0.1),
                                        child: const Center(
                                          child: CircularProgressIndicator(
                                            color: Color(0xFFEC7103),
                                          ),
                                        ),
                                      );
                                    },
                              )
                            : Container(
                                color: const Color(0xFFEC7103).withOpacity(0.1),
                                child: const Center(
                                  child: Icon(
                                    Icons.image,
                                    size: 50,
                                    color: Color(0xFFEC7103),
                                  ),
                                ),
                              ),
                        // Banner title overlay
                        if (banner.title.isNotEmpty)
                          Positioned(
                            bottom: 0,
                            left: 0,
                            right: 0,
                            child: Container(
                              padding: const EdgeInsets.all(15),
                              decoration: BoxDecoration(
                                gradient: LinearGradient(
                                  begin: Alignment.bottomCenter,
                                  end: Alignment.topCenter,
                                  colors: [
                                    Colors.black.withOpacity(0.7),
                                    Colors.transparent,
                                  ],
                                ),
                              ),
                              child: Text(
                                banner.title,
                                style: GoogleFonts.dmSans(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                  color: Colors.white,
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 15),
          // Dots indicator
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(
              banners.length,
              (index) => Container(
                margin: const EdgeInsets.symmetric(horizontal: 4),
                width: _currentBannerIndex == index ? 20 : 8,
                height: 8,
                decoration: BoxDecoration(
                  color: _currentBannerIndex == index
                      ? const Color(0xFFEC7103)
                      : Colors.grey.shade400,
                  borderRadius: BorderRadius.circular(4),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContentSection() {
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 30, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // Glow More text with Jura font
          Text(
            'Glow More, Earn More',
            style: GoogleFonts.jura(
              fontSize: 25,
              fontWeight: FontWeight.w300,
              color: Colors.black87,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 10),
          // Daily Beauty Rewards with DM Sans font
          Text(
            'Daily Beauty Rewards',
            style: GoogleFonts.dmSans(
              fontSize: 20,
              fontWeight: FontWeight.w400,
              color: Colors.black87,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 40),
          // Main image
          AspectRatio(
            aspectRatio: 12 / 8.7,
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(150),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 15,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              clipBehavior: Clip.hardEdge,
              child: Image.asset(
                'assets/images/HOMEPAGE.png',
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) {
                  return Container(
                    color: Colors.grey.shade200,
                    child: const Center(
                      child: Icon(
                        Icons.broken_image,
                        size: 50,
                        color: Colors.grey,
                      ),
                    ),
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDiscoverButton() {
    return LayoutBuilder(
      builder: (context, constraints) {
        double screenWidth = MediaQuery.of(context).size.width;
        double fontSize = screenWidth * 0.06;
        double imageSize = screenWidth * 0.1;

        return Container(
          margin: const EdgeInsets.symmetric(horizontal: 0),
          width: double.infinity,
          height: 40,
          child: Container(
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFFEC7103), Color(0xFFF3DECB)],
                begin: Alignment.centerLeft,
                end: Alignment.centerRight,
              ),
              borderRadius: BorderRadius.circular(0),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFFEC7103).withOpacity(0.3),
                  blurRadius: 15,
                  offset: const Offset(0, 5),
                ),
              ],
            ),
            child: Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: () {
                  // Handle discover button tap
                },
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 35),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Flexible(
                        child: Text(
                          'Discover More',
                          style: GoogleFonts.dmSans(
                            fontSize: fontSize.clamp(24, 38),
                            fontWeight: FontWeight.bold,
                            color: Colors.black87,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      Image.asset(
                        'assets/icons/arrow-icon.png',
                        width: imageSize.clamp(30, 44),
                        height: imageSize.clamp(30, 44),
                        fit: BoxFit.contain,
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  @override
  void dispose() {
    _fadeController.dispose();
    _slideController.dispose();
    _pulseController.dispose();
    _bannerPageController.dispose(); // 🔥 FIX: Dispose banner controller
    super.dispose();
  }
}
