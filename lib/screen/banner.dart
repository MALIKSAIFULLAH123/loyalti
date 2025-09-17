// import 'dart:convert';
// import 'package:flutter/foundation.dart';
// import 'package:flutter/material.dart';
// import 'package:google_fonts/google_fonts.dart';
// import 'package:http/http.dart' as http;
// import 'package:shared_preferences/shared_preferences.dart';

// class BannerModel {
//   final String imageUrl;
//   final String title;

//   BannerModel({required this.imageUrl, required this.title});

//   factory BannerModel.fromJson(Map<String, dynamic> json) {
//     return BannerModel(
//       imageUrl: json['image'] ?? '',
//       title: json['title'] ?? '',
//     );
//   }
// }

// class ApiConstants {
//   static String get baseUrl {
//     return kIsWeb
//         ? '' // for CORS, full url is prefixed later
//         : 'https://'; // Android/iOS base (you can customize)
//   }
// }

// class BannerSliderScreen extends StatefulWidget {
//   const BannerSliderScreen({super.key});

//   @override
//   State<BannerSliderScreen> createState() => _BannerSliderScreenState();
// }

// class _BannerSliderScreenState extends State<BannerSliderScreen> {
//   final PageController _bannerPageController = PageController();
//   int _currentBannerIndex = 0;
//   List<BannerModel> banners = [];

//   @override
//   void initState() {
//     super.initState();
//     loadBanners();
//   }

//   Future<void> loadBanners() async {
//     try {
//       final prefs = await SharedPreferences.getInstance();
//       final companyUrl = prefs.getString('company_url');
//       final softwareType = prefs.getString('software_type');
//       final clientID = prefs.getString('clientID');
//       final trdr = prefs.getString('TRDR');

//       if (companyUrl == null ||
//           softwareType == null ||
//           clientID == null ||
//           trdr == null) {
//         throw Exception("Missing required SharedPreferences values");
//       }

//       final servicePath = softwareType == "TESAE"
//           ? "/pegasus/a_xit/connector.php"
//           : "/s1services";

//       final fullUrl = "https://$companyUrl$servicePath";
//       final uri = Uri.parse("${ApiConstants.baseUrl}$fullUrl");

//       debugPrint("🔄 Loading banners from: $uri");

//       final requestBody = {
//         "service": "SqlData",
//         "clientID": clientID,
//         "appId": "1001",
//         "SqlName": "9703",
//         "trdr": trdr,
//       };

//       final response = await http
//           .post(
//             uri,
//             headers: {
//               'Content-Type': 'application/json',
//               'Accept': 'application/json',
//               'User-Agent': 'LoyaltyApp/1.0',
//             },
//             body: jsonEncode(requestBody),
//           )
//           .timeout(const Duration(seconds: 10));

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

//   @override
//   Widget build(BuildContext context) {
//     return Scaffold(
//       appBar: AppBar(title: const Text("Banner Slider")),
//       body: _buildBannerSlider(),
//     );
//   }

//   Widget _buildBannerSlider() {
//     if (banners.isEmpty) {
//       return const Center(child: CircularProgressIndicator());
//     }

//     return Container(
//       margin: const EdgeInsets.fromLTRB(20, 40, 20, 20),
//       height: 220,
//       child: Column(
//         children: [
//           // Banner PageView
//           Expanded(
//             child: PageView.builder(
//               controller: _bannerPageController,
//               onPageChanged: (index) {
//                 setState(() {
//                   _currentBannerIndex = index;
//                 });
//               },
//               itemCount: banners.length,
//               itemBuilder: (context, index) {
//                 final banner = banners[index];
//                 final imageUrl = kIsWeb
//                     ? "https://corsproxy.io/?${banner.imageUrl}"
//                     : banner.imageUrl;

//                 return Container(
//                   margin: const EdgeInsets.symmetric(horizontal: 5),
//                   decoration: BoxDecoration(
//                     borderRadius: BorderRadius.circular(15),
//                     boxShadow: [
//                       BoxShadow(
//                         color: Colors.black.withOpacity(0.1),
//                         blurRadius: 10,
//                         offset: const Offset(0, 5),
//                       ),
//                     ],
//                   ),
//                   child: ClipRRect(
//                     borderRadius: BorderRadius.circular(15),
//                     child: Stack(
//                       fit: StackFit.expand,
//                       children: [
//                         Image.network(
//                           imageUrl,
//                           fit: BoxFit.cover,
//                           errorBuilder: (context, error, stackTrace) {
//                             return Container(
//                               color: const Color(0xFFEC7103).withOpacity(0.1),
//                               child: const Center(
//                                 child: Icon(
//                                   Icons.broken_image_outlined,
//                                   size: 50,
//                                   color: Color(0xFFEC7103),
//                                 ),
//                               ),
//                             );
//                           },
//                           loadingBuilder: (context, child, progress) {
//                             if (progress == null) return child;
//                             return Container(
//                               color: const Color(0xFFEC7103).withOpacity(0.1),
//                               child: const Center(
//                                 child: CircularProgressIndicator(
//                                   color: Color(0xFFEC7103),
//                                 ),
//                               ),
//                             );
//                           },
//                         ),
//                         if (banner.title.isNotEmpty)
//                           Positioned(
//                             bottom: 0,
//                             left: 0,
//                             right: 0,
//                             child: Container(
//                               padding: const EdgeInsets.all(15),
//                               decoration: BoxDecoration(
//                                 gradient: LinearGradient(
//                                   begin: Alignment.bottomCenter,
//                                   end: Alignment.topCenter,
//                                   colors: [
//                                     Colors.black.withOpacity(0.7),
//                                     Colors.transparent,
//                                   ],
//                                 ),
//                               ),
//                               child: Text(
//                                 banner.title,
//                                 style: GoogleFonts.dmSans(
//                                   fontSize: 16,
//                                   fontWeight: FontWeight.w600,
//                                   color: Colors.white,
//                                 ),
//                                 maxLines: 2,
//                                 overflow: TextOverflow.ellipsis,
//                               ),
//                             ),
//                           ),
//                       ],
//                     ),
//                   ),
//                 );
//               },
//             ),
//           ),

//           const SizedBox(height: 15),

//           // Dot Indicators
//           Row(
//             mainAxisAlignment: MainAxisAlignment.center,
//             children: List.generate(
//               banners.length,
//               (index) => AnimatedContainer(
//                 duration: const Duration(milliseconds: 300),
//                 margin: const EdgeInsets.symmetric(horizontal: 4),
//                 width: _currentBannerIndex == index ? 20 : 8,
//                 height: 8,
//                 decoration: BoxDecoration(
//                   color: _currentBannerIndex == index
//                       ? const Color(0xFFEC7103)
//                       : Colors.grey.shade400,
//                   borderRadius: BorderRadius.circular(4),
//                 ),
//               ),
//             ),
//           ),
//         ],
//       ),
//     );
//   }
// }
