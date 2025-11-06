
// new home page // Updated home page with dynamic name and custom star iconimport 'dart:convert';
import 'dart:convert';

import 'package:flutter/services.dart' show rootBundle;
import 'package:googleapis_auth/auth_io.dart';
import 'package:loyalty_app/utils/language_decoder.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:io';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:loyalty_app/Auth/LanguageSelectionPage.dart';
import 'package:loyalty_app/Services/language_service.dart';
import 'package:loyalty_app/utils/api_constants.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:charset_converter/charset_converter.dart';
// import 'package:flutter_gen/gen_l10n/app_localizations.dart';

// Model classes for API responses
class BannerModel {
  final String imageUrl;
  final String title;

  BannerModel({required this.imageUrl, required this.title});

  factory BannerModel.fromJson(Map<String, dynamic> json) {
    String rawUrl = json['imageUrl'] ?? json['COLUMN2'] ?? '';

    // Add https:// if missing from the URL
    String formattedUrl = rawUrl;
    if (formattedUrl.isNotEmpty && !formattedUrl.startsWith('http')) {
      formattedUrl = 'https://$formattedUrl';
    }

    return BannerModel(
      imageUrl: formattedUrl,
      title: json['title'] ?? json['COLUMN3'] ?? '',
    );
  }
}

class HomeScreenMessage {
  final String message;
  final String? subtitle;
  final String? clickUrl; // New field for redirection URL

  HomeScreenMessage({
    required this.message,
    this.subtitle,
    this.clickUrl, // Add this
  });

  factory HomeScreenMessage.fromJson(Map<String, dynamic> json) {
    return HomeScreenMessage(
      message: json['message'] ?? '',
      subtitle: json['subtitle'],
      clickUrl: json['redirecturl'] ?? json['COLUMN3'] ?? '', // Add URL parsing
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
  static const _tokenPrefsKey = "fcm_access_token";
  static const _tokenExpiryKey = "fcm_token_expiry";
  static const _apiTokenKey = "fcm_api_token"; // New key for API token
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
    _loadAllHomeData();
    _startBannerAutoSlide();
    _loadUserName();
    _generateAccessToken();
    _handleFcmToken(); // Add this line
    // _sendFcmTokenToBackend();
    print('init chal gaya');
    // HomeScreen ke initState() mein add karo:
    // Test notification send karne ke liye
    // Future.delayed(Duration(seconds: 5), () {
    //   NotificationService.sendTestNotification();
    // });
  }

  /// Generate new Access Token
  static Future<String?> _generateAccessToken() async {
    try {
      final jsonKey = await _loadServiceAccountJson();
      final accountCredentials = ServiceAccountCredentials.fromJson(jsonKey);
      const scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

      final client = await clientViaServiceAccount(accountCredentials, scopes);
      final accessToken = client.credentials.accessToken.data;

      // Token usually expires in 1 hour, save expiry time (55 minutes to be safe)
      final expiryTime = DateTime.now()
          .add(Duration(minutes: 55))
          .millisecondsSinceEpoch;

      // Save to SharedPreferences
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_tokenPrefsKey, accessToken);
      await prefs.setInt(_tokenExpiryKey, expiryTime);

      client.close();

      debugPrint("✅ New access token generated successfully");
      print('✔✔✔✔ access  $accessToken');
      return accessToken;
    } catch (e) {
      debugPrint("❗ Error generating access token: $e");
      return null;
    }
  }

  static Future<Map<String, dynamic>> _loadServiceAccountJson() async {
    try {
      String jsonString = await rootBundle.loadString(
        'assets/service_account.json',
      );
      return json.decode(jsonString);
    } catch (e) {
      debugPrint("❗ Error loading service account JSON: $e");
      rethrow;
    }
  }

  @override
  void didUpdateWidget(HomeScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    // Check if this screen became active
    if (widget.currentIndex == 0 && oldWidget.currentIndex != 0) {
      _loadAllHomeData();
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

  // Add this to your imports

  // Add this method to your _HomeScreenState class

  Future<void> _handleFcmToken() async {
    try {
      String? fcmToken;
      fcmToken = await FirebaseMessaging.instance.getToken();

      await _sendFcmTokenToBackend(fcmToken!);

      if (kIsWeb) {
        // Web-specific FCM initialization
        fcmToken = await FirebaseMessaging.instance.getToken();
      } else {
        // Mobile platform
        fcmToken = await FirebaseMessaging.instance.getToken();
      }

      if (fcmToken != null) {
        debugPrint("✅ FCM Token liya gaya: $fcmToken");

        // Save in SharedPreferences
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('fcm_token', fcmToken);

        // Backend pe bhejo
        await _sendFcmTokenToBackend(fcmToken);
      }

      // Listen for token refresh (kabhi kabhi Firebase token change kar deta hai)
      FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
        debugPrint("♻️ FCM Token refresh hua: $newToken");

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('fcm_token', newToken);

        await _sendFcmTokenToBackend(newToken);
      });
    } catch (e) {
      debugPrint("❌ Error getting FCM token: $e");
    }
  }

  // Web-specific FCM initialization
  Future<void> _initializeFcmForWeb() async {
    try {
      // Request notification permission
      NotificationSettings settings = await FirebaseMessaging.instance
          .requestPermission();

      if (settings.authorizationStatus == AuthorizationStatus.authorized) {
        String? fcmToken = await FirebaseMessaging.instance.getToken(
          vapidKey: "YOUR_VAPID_KEY", // Web app configuration se milega
        );

        if (fcmToken != null) {
          debugPrint("Web FCM Token: $fcmToken");
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('fcm_token', fcmToken);
          await _sendFcmTokenToBackend(fcmToken);
        }
      }
    } catch (e) {
      debugPrint("Web FCM Error: $e");
    }
  }

  Future<void> _sendFcmTokenToBackend(String fcmToken) async {
    print("_sendFcmTokenToBackend chal gaya bhai");
    print("fcm token  $fcmToken");

    try {
      final prefs = await SharedPreferences.getInstance();
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');
      final clientID = prefs.getString('clientID');
      final trdr = prefs.getString('TRDR');
      final userName = prefs.getString('user_fullname') ?? '';
      final phone = prefs.getString('user_phone') ?? '';
      print("1 $companyUrl");
      print("2 $softwareType");
      print("3 $clientID");
      print("4 $trdr");
      print("5 $phone");

      if (companyUrl == null || clientID == null || trdr == null) {
        throw Exception("Missing required SharedPreferences values");
      }

      final servicePath = softwareType == "TESAE"
          ? "/pegasus/a_xit/connector.php"
          : "/s1services";

      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://$companyUrl$servicePath",
      );

      debugPrint("🔄 Sending FCM token to backend: $uri");

      final requestBody = {
        "service": "setData",
        "clientID": clientID,
        "appId": "1001",
        "OBJECT": "CUSTOMER[FORM=WEB]",
        "KEY": trdr,
        "data": {
          "CUSTOMER": [
            {
              "NAME": userName,
              "PHONE01": phone,
              "GLNCODE": fcmToken,
              "CCCXITACCESSTOKEN": await _generateAccessToken(),
            },
          ],
        },
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

      debugPrint("📥 FCM token response: ${response.statusCode}");
      debugPrint("📥 FCM token response body: ${response.body}");

      if (response.statusCode == 200) {
        debugPrint("✅ FCM token sent successfully");
      } else {
        debugPrint("❌ Failed to send FCM token: ${response.statusCode}");
      }
    } catch (e) {
      debugPrint("❗ Error sending FCM token to backend: $e");
    }
  }

  // 🔥 FIX: Auto-slide banners every 3 seconds
  void _startBannerAutoSlide() {
    Future.delayed(const Duration(seconds: 3), () {
      if (mounted && banners.isNotEmpty) {
        final nextIndex = (_currentBannerIndex + 1) % banners.length;

        _bannerPageController.animateToPage(
          nextIndex,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeInOut,
        );

        _startBannerAutoSlide();
      }
    });
  }

  // Load user name from SharedPreferences
  Future<void> _loadUserName() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final name = prefs.getString('Namee') ?? 'unknown';
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
    await Future.wait([loadTotalPoints(), loadHomeMessage(), loadBanners()]);
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
        String responseBody = await decodeGreekResponseBytes(response.bodyBytes);
        final data = jsonDecode(responseBody);
        if (data is Map<String, dynamic> &&
            data['success'] == true &&
            data['rows'] != null &&
            data['rows'].isNotEmpty) {
          final points = data['rows'][0]['totalpoints']?.toString() ?? "0";
          final Name = data['rows'][0]['NAME']?.toString() ?? "unknown";
          await prefs.setString('Namee', Name);

          // Base64 image save karo
          final base64Image = data['rows'][0]['CCCXITLIMAGE']?.toString() ?? '';

          await prefs.setString('totalPoints', points);

          // Base64 image ko SharedPreferences me save karo
          if (base64Image.isNotEmpty) {
            await prefs.setString('user_profile_base64', base64Image);
          }

          if (mounted) {
            setState(() {
              userName = Name;
              totalPoints = points;
              profileImagePath = base64Image;
              _isLoading = false;
              _hasError = false;
            });
          }
          return;
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

      // Fixed URI parsing - removed duplicate "https://"
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
        String responseBody = await decodeGreekResponseBytes(response.bodyBytes);
        final data = jsonDecode(responseBody);
        if (data is Map<String, dynamic> &&
            data['success'] == true &&
            data['rows'] != null) {
          final List<dynamic> bannerData = data['rows'];

          // Process and debug each banner
          debugPrint("📊 Found ${bannerData.length} banners");
          for (var banner in bannerData) {
            debugPrint("Banner data: $banner");
          }

          final List<BannerModel> loadedBanners = bannerData
              .map((banner) => BannerModel.fromJson(banner))
              .toList();

          if (mounted) {
            setState(() {
              banners = loadedBanners;
            });
          }

          // Debug the formatted URLs
          for (var banner in loadedBanners) {
            debugPrint("Formatted banner URL: ${banner.imageUrl}");
          }
        } else {
          debugPrint("❌ Invalid response format or no banners found");
        }
      } else {
        debugPrint("❌ HTTP Error: ${response.statusCode}");
      }
    } catch (e) {
      debugPrint("❗ Error loading banners: $e");
      // Show error in UI if needed
      if (mounted) {
        setState(() {
          _hasError = true;
          _errorMessage = "Failed to load banners: $e";
        });
      }
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
        String responseBody = await decodeGreekResponseBytes(response.bodyBytes);
        final data = jsonDecode(responseBody);
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

  // Future<void> _loadProfileImage() async {
  //   final prefs = await SharedPreferences.getInstance();

  //   // Pehle base64 image check karo
  //   final base64Image = prefs.getString('user_profile_base64');
  //   final filePath = prefs.getString('user_profile_image');

  //   debugPrint(
  //     "📸 Loading profile - Base64: ${base64Image?.isNotEmpty}, File: $filePath",
  //   );

  //   if (mounted) {
  //     setState(() {
  //       profileImagePath = filePath;
  //       // Base64 image ko bhi state me store kar sakte hain agar chahiye
  //     });
  //   }
  // }
  @override
  Widget build(BuildContext context) {
    super.build(context);
    final localizations = AppLocalizations.of(context)!;

    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      body: RefreshIndicator(
        onRefresh: () async {
          await _loadAllHomeData();
        },
        color: const Color(0xFFEC7103),
        backgroundColor: Colors.white,
        strokeWidth: 2.0,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            children: [
              _buildHeaderSection(context, localizations),
              _buildBalanceSection(localizations),
              _buildContentSection(
                localizations,
              ), // This now shows dynamic message
              _buildBannerSlider(),
              // _buildDiscoverButton(), // REMOVE THIS LINE
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
              localizations.specialOffers,
              style: GoogleFonts.jura(
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
                          // Format the image URL before using it
                          CachedNetworkImage(
                            imageUrl: banner.imageUrl.startsWith('http')
                                ? banner.imageUrl
                                : 'https://${banner.imageUrl}',
                            height: 280,
                            width: double.infinity,
                            fit: BoxFit.contain,
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
                            errorWidget: (context, url, error) {
                              // Print error for debugging
                              print("Image load error: $error");
                              print("Failed URL: $url");

                              return Container(
                                color: Colors.grey.shade200,
                                child: const Center(
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(
                                        Icons.image_not_supported,
                                        color: Colors.grey,
                                        size: 40,
                                      ),
                                      SizedBox(height: 8),
                                      Text(
                                        'Image not available',
                                        style: TextStyle(
                                          color: Colors.grey,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            },
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
                                  style: GoogleFonts.jura(
                                    color: Colors.white,
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
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
          ],
        ),
      ),
    );
  }

  Widget _buildHeaderSection(
    BuildContext context,
    AppLocalizations localizations,
  ) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 13, 20, 10),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFFEC7103), Color(0xFFFF8A3D)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ✅ Top Logo
            Center(
              child: Image.asset(
                'assets/images/home-logo.png', // apna logo yahan rakho
                width: 230,
                fit: BoxFit.contain,
              ),
            ),

            const SizedBox(height: 5),

            // ✅ Existing Row (Profile + Welcome + Language)
            Row(
              children: [
                // Profile Image
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
                            profileImagePath!.isNotEmpty)
                        ? Image.memory(
                            base64Decode(profileImagePath!),
                            fit: BoxFit.cover,
                            gaplessPlayback:
                                true, // ✅ Ye add karo smooth loading ke liye
                            errorBuilder: _defaultImageErrorBuilder,
                          )
                        : Image.asset(
                            'assets/images/profile_temp.jpg',
                            fit: BoxFit.cover,
                            gaplessPlayback: true, // ✅ Ye bhi add karo
                            errorBuilder: _defaultImageErrorBuilder,
                          ),
                  ),
                ),

                const SizedBox(width: 15),

                // Welcome text
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        localizations.welcome,
                        style: GoogleFonts.jura(
                          fontSize: 16,
                          fontWeight: FontWeight.w500,
                          color: Colors.white,
                        ),
                      ),
                      Text(
                        userName, // SharedPreferences se dynamic
                        style: GoogleFonts.jura(
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
          ],
        ),
      ),
    );
  }

  Widget _buildProfileImage() {
    return FutureBuilder<String?>(
      future: _getBase64Image(),
      builder: (context, snapshot) {
        // Pehle base64 image try karo
        if (snapshot.hasData && snapshot.data!.isNotEmpty) {
          try {
            final bytes = base64Decode(snapshot.data!);
            return Image.memory(
              bytes,
              fit: BoxFit.cover,
              errorBuilder: _defaultImageErrorBuilder,
            );
          } catch (e) {
            debugPrint("Base64 decode error: $e");
          }
        }

        // Phir file path try karo
        if (profileImagePath != null &&
            profileImagePath!.isNotEmpty &&
            File(profileImagePath!).existsSync()) {
          return Image.file(
            File(profileImagePath!),
            fit: BoxFit.cover,
            errorBuilder: _defaultImageErrorBuilder,
          );
        }

        // Default image
        return Image.asset(
          'assets/images/profile_temp.jpg',
          fit: BoxFit.cover,
          errorBuilder: _defaultImageErrorBuilder,
        );
      },
    );
  }

  Future<String?> _getBase64Image() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('user_profile_base64');
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

  Widget _buildBalanceSection(AppLocalizations localizations) {
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 10, 20, 0),
      child: Column(
        children: [
          Text(
            localizations.myBalance,
            style: GoogleFonts.jura(
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
                  _isLoading
                      ? localizations.loading
                      : '$totalPoints ${localizations.points}',
                  style: GoogleFonts.jura(
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
      return const SizedBox.shrink();
    }

    return Container(
      margin: const EdgeInsets.fromLTRB(20, 10, 20, 20),
      height: 280,
      child: Column(
        children: [
          // Banner PageView
          Expanded(
            child: PageView.builder(
              controller: _bannerPageController,
              onPageChanged: (index) {
                if (_currentBannerIndex != index) {
                  setState(() {
                    _currentBannerIndex = index;
                  });
                }
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
                    child: GestureDetector(
                      onTap: () => _onBannerTap(banner),
                      child: Stack(
                        fit: StackFit.expand,
                        children: [
                          // Banner Image
                          banner.imageUrl.isNotEmpty
                              ? Image.network(
                                  banner.imageUrl,
                                  fit: BoxFit.contain,
                                  errorBuilder: (context, error, stackTrace) {
                                    return Container(
                                      color: Colors.grey.shade200,
                                      child: const Center(
                                        child: Icon(
                                          Icons.broken_image,
                                          size: 40,
                                          color: Colors.grey,
                                        ),
                                      ),
                                    );
                                  },
                                  loadingBuilder:
                                      (context, child, loadingProgress) {
                                        if (loadingProgress == null) {
                                          return child;
                                        }
                                        return Container(
                                          color: Colors.grey.shade200,
                                          child: const Center(
                                            child: CircularProgressIndicator(
                                              color: Color(0xFFEC7103),
                                            ),
                                          ),
                                        );
                                      },
                                )
                              : Container(
                                  color: Colors.grey.shade200,
                                  child: const Center(
                                    child: Icon(
                                      Icons.image_not_supported,
                                      size: 40,
                                      color: Colors.grey,
                                    ),
                                  ),
                                ),

                          // Banner title - ONLY show if it's NOT a URL
                          if (banner.title.isNotEmpty && !_isUrl(banner.title))
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
                                      Colors.black.withOpacity(0.6),
                                      Colors.transparent,
                                    ],
                                  ),
                                ),
                                child: Text(
                                  banner.title,
                                  style: GoogleFonts.jura(
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
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 10),
          // Dots indicator
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(
              banners.length,
              (index) => AnimatedContainer(
                duration: const Duration(milliseconds: 300),
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
          SizedBox(height: 10),
        ],
      ),
    );
  }

  void _onBannerTap(BannerModel banner) async {
    String url = '';

    // Check if title contains URL (but don't display it)
    if (_isUrl(banner.title)) {
      url = banner.title;
    }

    if (url.isNotEmpty) {
      await _launchUrl(url);
    } else {
      debugPrint("❗ No valid URL found for banner");
    }
  }

  bool _isUrl(String text) {
    return text.startsWith('http://') ||
        text.startsWith('https://') ||
        text.contains('www.') ||
        text.contains('.com') ||
        text.contains('.gr');
  }

  String _extractUrlFromString(String input) {
    // Simple URL extraction - modify based on your data format
    if (input.contains('xit.gr')) {
      return 'https://xit.gr';
    }

    // Add more URL extraction logic if needed
    RegExp urlRegex = RegExp(r'https?://[^\s]+');
    Match? match = urlRegex.firstMatch(input);
    return match?.group(0) ?? input;
  }

  Future<void> _launchUrl(String url) async {
    try {
      // Ensure URL has protocol
      if (!url.startsWith('http://') && !url.startsWith('https://')) {
        url = 'https://$url';
      }

      final Uri uri = Uri.parse(url);

      if (await canLaunchUrl(uri)) {
        await launchUrl(
          uri,
          mode: LaunchMode.externalApplication, // Opens in browser
        );
        debugPrint("✅ Launched URL: $url");
      } else {
        debugPrint("❗ Could not launch URL: $url");
      }
    } catch (e) {
      debugPrint("❗ Error launching URL: $e");
    }
  }

  Widget _buildContentSection(AppLocalizations localizations) {
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 10, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // Dynamic message from API call 9704
          if (homeMessage != null)
            GestureDetector(
              onTap: () async {
                // Redirect to URL if available
                if (homeMessage!.clickUrl != null &&
                    homeMessage!.clickUrl!.isNotEmpty) {
                  await _launchUrl(homeMessage!.clickUrl!);
                }
              },
              child: Container(
                padding: const EdgeInsets.all(13),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.1),
                      blurRadius: 8,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    Text(
                      homeMessage!.message,
                      style: GoogleFonts.jura(
                        fontSize: 22,
                        fontWeight: FontWeight.w600,
                        color: Colors.black87,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    if (homeMessage!.subtitle != null &&
                        homeMessage!.subtitle!.isNotEmpty)
                      Padding(
                        padding: const EdgeInsets.only(top: 8),
                        child: Text(
                          homeMessage!.subtitle!,
                          style: GoogleFonts.jura(
                            fontSize: 16,
                            fontWeight: FontWeight.w400,
                            color: Colors.grey.shade600,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ),
                    // Show tap indicator if there's a URL
                    if (homeMessage!.clickUrl != null &&
                        homeMessage!.clickUrl!.isNotEmpty)
                      Padding(
                        padding: const EdgeInsets.only(top: 8),
                        child: Icon(
                          Icons.touch_app,
                          color: Color(0xFFEC7103),
                          size: 20,
                        ),
                      ),
                  ],
                ),
              ),
            )
          else
            // Fallback if no message loaded
            Text(
              localizations.welcomeToLoyaltyRewards,
              style: GoogleFonts.jura(
                fontSize: 20,
                fontWeight: FontWeight.w500,
                color: Colors.black87,
              ),
              textAlign: TextAlign.center,
            ),
        ],
      ),
    );
  }

  // Widget _buildDiscoverButton() {
  //   return LayoutBuilder(
  //     builder: (context, constraints) {
  //       double screenWidth = MediaQuery.of(context).size.width;
  //       double fontSize = screenWidth * 0.06;
  //       double imageSize = screenWidth * 0.1;

  //       return Container(
  //         margin: const EdgeInsets.symmetric(horizontal: 0),
  //         width: double.infinity,
  //         height: 40,
  //         child: Container(
  //           decoration: BoxDecoration(
  //             gradient: const LinearGradient(
  //               colors: [Color(0xFFEC7103), Color(0xFFF3DECB)],
  //             begin: Alignment.centerLeft,
  //             end: Alignment.centerRight,
  //             ),
  //             borderRadius: BorderRadius.circular(0),
  //             boxShadow: [
  //               BoxShadow(
  //                 color: const Color(0xFFEC7103).withOpacity(0.3),
  //                 blurRadius: 15,
  //                 offset: const Offset(0, 5),
  //               ),
  //             ],
  //           ),
  //           child: Material(
  //             color: Colors.transparent,
  //             child: InkWell(
  //               onTap: () {
  //                 // Handle discover button tap
  //               },
  //               child: Padding(
  //                 padding: const EdgeInsets.symmetric(horizontal: 35),
  //                 child: Row(
  //                   mainAxisAlignment: MainAxisAlignment.spaceBetween,
  //                   children: [
  //                     Flexible(
  //                       child: Text(
  //                         'Discover More',
  //                         style: GoogleFonts.jura(
  //                           fontSize: fontSize.clamp(24, 38),
  //                           fontWeight: FontWeight.bold,
  //                           color: Colors.black87,
  //                         ),
  //                         overflow: TextOverflow.ellipsis,
  //                       ),
  //                     ),
  //                     Image.asset(
  //                       'assets/icons/arrow-icon.png',
  //                       width: imageSize.clamp(30, 44),
  //                       height: imageSize.clamp(30, 44),
  //                       fit: BoxFit.contain,
  //                     ),
  //                   ],
  //                 ),
  //               ),
  //             ),
  //           ),
  //         ),
  //       );
  //     },
  //   );
  // }

  @override
  /// Enhanced Greek text decoder - handles multiple encoding scenarios
  ///
  ///
  // Replace your _decodeApiResponse method with this async version:
  Future<String> _decodeApiResponseAsync(http.Response response) async {
    try {
      // Check content type first
      String? contentType = response.headers['content-type'];

      if (contentType != null) {
        if (contentType.contains('charset=windows-1253')) {
          return _convertWindows1253ToUtf8(
            String.fromCharCodes(response.bodyBytes),
          );
        } else if (contentType.contains('charset=iso-8859-7')) {
          return await _convertIso88597ToUtf8(
            String.fromCharCodes(response.bodyBytes),
          );
        }
      }

      // Try UTF-8 first
      try {
        String responseBody = utf8.decode(response.bodyBytes);
        if (_containsGreekUnicode(responseBody) ||
            !_containsLatinExtended(responseBody)) {
          return responseBody;
        }
      } catch (e) {
        debugPrint('UTF-8 decoding failed: $e');
      }

      // Fallback to Latin-1 then convert
      try {
        String latin1Decoded = latin1.decode(response.bodyBytes);
        String converted = _decodeGreekText(latin1Decoded);
        if (_containsGreekUnicode(converted)) {
          return converted;
        }
      } catch (e) {
        debugPrint('Latin-1 decoding failed: $e');
      }

      // Ultimate fallback
      return _decodeGreekText(response.body);
    } catch (e) {
      return response.body;
    }
  }

  String _decodeGreekText(dynamic value) {
    if (value == null) return '';

    String text = value.toString().trim();
    if (text.isEmpty) return '';

    try {
      // Method 1: Check if text contains Greek Unicode characters (properly encoded)
      if (_containsGreekUnicode(text)) {
        return text; // Already properly encoded
      }

      // Method 2: Handle Windows-1253 to UTF-8 conversion (most common case)
      if (_isWindows1253Encoded(text)) {
        return _convertWindows1253ToUtf8(text);
      }

      // Method 3: Try byte-level Windows-1253 conversion
      String converted = _convertBytesToGreek(text);
      if (_containsGreekUnicode(converted)) {
        return converted;
      }

      // Method 4: Handle HTML entities and numeric character references
      text = _decodeHtmlEntities(text);
      text = _decodeNumericEntities(text);

      return text;
    } catch (e) {
      if (kDebugMode) {
        print('Greek text decoding error: $e');
        print('Original text: $text');
      }
      return text; // Return original if all methods fail
    }
  }

  /// Enhanced Windows-1253 detection
  bool _isWindows1253Encoded(String text) {
    // Check for common Windows-1253 Greek character patterns
    final windows1253Patterns = [
      'Áí',
      'ìï',
      'êÝ',
      'ðñ',
      'óå',
      'ôá',
      'êá',
      'Üò',
      'íô',
      'Þò',
      'ýø',
      'ôå',
      'ñÝ',
      'óö',
      'ïñ',
      'ëë',
      'õí',
      'éê',
      'ðþ',
      'íõ',
    ];

    return windows1253Patterns.any((pattern) => text.contains(pattern)) ||
        text.codeUnits.any((unit) => unit >= 0xC0 && unit <= 0xFF);
  }

  /// Enhanced Windows-1253 to Greek Unicode conversion
  String _convertWindows1253ToUtf8(String text) {
    // Complete Windows-1253 to Greek Unicode mapping table
    final Map<int, String> windows1253ToGreek = {
      // Greek uppercase letters (0xC1-0xD9)
      0xC1: 'Α',
      0xC2: 'Β',
      0xC3: 'Γ',
      0xC4: 'Δ',
      0xC5: 'Ε',
      0xC6: 'Ζ',
      0xC7: 'Η',
      0xC8: 'Θ',
      0xC9: 'Ι',
      0xCA: 'Κ',
      0xCB: 'Λ',
      0xCC: 'Μ',
      0xCD: 'Ν',
      0xCE: 'Ξ',
      0xCF: 'Ο',
      0xD0: 'Π',
      0xD1: 'Ρ',
      0xD3: 'Σ',
      0xD4: 'Τ',
      0xD5: 'Υ',
      0xD6: 'Φ',
      0xD7: 'Χ',
      0xD8: 'Ψ',
      0xD9: 'Ω',

      // Greek lowercase letters (0xE1-0xF9)
      0xE1: 'α',
      0xE2: 'β',
      0xE3: 'γ',
      0xE4: 'δ',
      0xE5: 'ε',
      0xE6: 'ζ',
      0xE7: 'η',
      0xE8: 'θ',
      0xE9: 'ι',
      0xEA: 'κ',
      0xEB: 'λ',
      0xEC: 'μ',
      0xED: 'ν',
      0xEE: 'ξ',
      0xEF: 'ο',
      0xF0: 'π',
      0xF1: 'ρ',
      0xF2: 'ς',
      0xF3: 'σ',
      0xF4: 'τ',
      0xF5: 'υ',
      0xF6: 'φ',
      0xF7: 'χ',
      0xF8: 'ψ',
      0xF9: 'ω',

      // Greek accented characters
      0xAA: 'Ί', 0xBA: 'Ό', 0xDA: 'Ύ', 0xDB: 'Ώ', 0xDC: 'ΐ', 0xDD: 'ΰ',
      0xFD: 'ύ', 0xFC: 'ό', 0xFE: 'ώ', 0xFB: 'ή', 0xFA: 'ί', 0xDF: 'ϊ',

      // Additional accented vowels
      0xB6: 'Ά', 0xB8: 'Έ', 0xB9: 'Ή', 0xBC: 'Ό', 0xBE: 'Ύ', 0xBF: 'Ώ',
      0xDC: 'ά',
      0xDD: 'έ',
      0xDE: 'ή',
      0xDF: 'ί',
      0xE0: 'ό',
      0xFC: 'ύ',
      0xFD: 'ώ',
    };

    String converted = '';
    for (int i = 0; i < text.length; i++) {
      int charCode = text.codeUnitAt(i);
      if (windows1253ToGreek.containsKey(charCode)) {
        converted += windows1253ToGreek[charCode]!;
      } else {
        converted += text[i];
      }
    }

    return converted;
  }

  /// Byte-level conversion for stubborn encoding issues
  String _convertBytesToGreek(String text) {
    try {
      List<int> bytes = text.codeUnits;
      String result = '';

      for (int byte in bytes) {
        // Windows-1253 Greek range conversion
        if (byte >= 0xC1 && byte <= 0xD9) {
          // Uppercase Greek letters
          int greekCode = 0x0391 + (byte - 0xC1);
          if (byte == 0xD2) greekCode = 0x03A3; // Sigma special case
          result += String.fromCharCode(greekCode);
        } else if (byte >= 0xE1 && byte <= 0xF9) {
          // Lowercase Greek letters
          int greekCode = 0x03B1 + (byte - 0xE1);
          if (byte == 0xF2) greekCode = 0x03C2; // Final sigma
          result += String.fromCharCode(greekCode);
        } else if (byte == 0xB6) {
          result += 'Ά'; // Alpha with tonos
        } else if (byte == 0xB8) {
          result += 'Έ'; // Epsilon with tonos
        } else if (byte == 0xB9) {
          result += 'Ή'; // Eta with tonos
        } else if (byte == 0xBC) {
          result += 'Ό'; // Omicron with tonos
        } else if (byte == 0xBE) {
          result += 'Ύ'; // Upsilon with tonos
        } else if (byte == 0xBF) {
          result += 'Ώ'; // Omega with tonos
        } else if (byte == 0xDC) {
          result += 'ά'; // alpha with tonos
        } else if (byte == 0xDD) {
          result += 'έ'; // epsilon with tonos
        } else if (byte == 0xDE) {
          result += 'ή'; // eta with tonos
        } else if (byte == 0xDF) {
          result += 'ί'; // iota with tonos
        } else if (byte == 0xFC) {
          result += 'ό'; // omicron with tonos
        } else if (byte == 0xFD) {
          result += 'ύ'; // upsilon with tonos
        } else if (byte == 0xFE) {
          result += 'ώ'; // omega with tonos
        } else {
          result += String.fromCharCode(byte);
        }
      }

      return result;
    } catch (e) {
      if (kDebugMode) print('Byte conversion failed: $e');
      return text;
    }
  }

  /// Check if text contains properly encoded Greek Unicode characters
  bool _containsGreekUnicode(String text) {
    // Greek Unicode range: U+0370–U+03FF and U+1F00–U+1FFF
    return text.runes.any(
      (rune) =>
          (rune >= 0x0370 && rune <= 0x03FF) ||
          (rune >= 0x1F00 && rune <= 0x1FFF),
    );
  }

  /// Check if text is ISO-8859-7 encoded
  bool _isIso88597Encoded(String text) {
    // ISO-8859-7 has specific byte patterns for Greek
    try {
      List<int> bytes = text.codeUnits;
      return bytes.any((byte) => byte >= 0xB6 && byte <= 0xFF);
    } catch (e) {
      return false;
    }
  }

  /// Convert ISO-8859-7 to UTF-8
  Future<String> _convertIso88597ToUtf8(String text) async {
    try {
      // Convert List<int> → Uint8List
      final bytes = Uint8List.fromList(text.codeUnits);

      // Decode from ISO-8859-7 to UTF-8
      return await CharsetConverter.decode('iso-8859-7', bytes);
    } catch (e) {
      if (kDebugMode) print('ISO-8859-7 conversion failed: $e');
      return text;
    }
  }

  /// Check if text contains Latin extended characters
  bool _containsLatinExtended(String text) {
    return text.codeUnits.any((unit) => unit > 127 && unit < 256);
  }

  /// Decode HTML entities
  String _decodeHtmlEntities(String text) {
    return text
        .replaceAll('&amp;', '&')
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .replaceAll('&quot;', '"')
        .replaceAll('&#39;', "'")
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&hellip;', '…')
        .replaceAll('&mdash;', '—')
        .replaceAll('&ndash;', '–')
        .replaceAll('&copy;', '©')
        .replaceAll('&reg;', '®')
        .replaceAll('&trade;', '™');
  }

  /// Decode numeric character references (&#xxx; format)
  String _decodeNumericEntities(String text) {
    return text.replaceAllMapped(RegExp(r'&#(\d+);'), (match) {
      try {
        int charCode = int.parse(match.group(1)!);
        return String.fromCharCode(charCode);
      } catch (e) {
        return match.group(0)!; // Return original if conversion fails
      }
    });
  }

  /// Enhanced API response decoder for Greek content
  Future<String> _decodeApiResponse(http.Response response) async {
    String responseBody;

    try {
      // Method 1: Check if response has charset info in headers
      String? contentType = response.headers['content-type'];
      if (contentType != null) {
        if (contentType.contains('charset=windows-1253')) {
          // Decode as Windows-1253
          responseBody = _convertWindows1253ToUtf8(
            String.fromCharCodes(response.bodyBytes),
          );
          return responseBody;
        } else if (contentType.contains('charset=iso-8859-7')) {
          // Decode as ISO-8859-7
          responseBody = await _convertIso88597ToUtf8(
            String.fromCharCodes(response.bodyBytes),
          );
          return responseBody;
        }
      }

      // Method 2: Try UTF-8 decoding first
      try {
        responseBody = utf8.decode(response.bodyBytes);
        if (_containsGreekUnicode(responseBody) ||
            !_containsLatinExtended(responseBody)) {
          return responseBody;
        }
      } catch (e) {
        if (kDebugMode) print('UTF-8 decoding failed: $e');
      }

      // Method 3: Try Latin-1 then convert to UTF-8
      try {
        String latin1Decoded = latin1.decode(response.bodyBytes);
        responseBody = _decodeGreekText(latin1Decoded);
        if (_containsGreekUnicode(responseBody)) {
          return responseBody;
        }
      } catch (e) {
        if (kDebugMode) print('Latin-1 decoding failed: $e');
      }

      // Method 4: Fallback to response.body
      responseBody = response.body;
      responseBody = _decodeGreekText(responseBody);

      return responseBody;
    } catch (e) {
      return response.body; // Ultimate fallback
    }
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

//fcm token notificatons
class FCMNotificationHelper {
  static const _tokenPrefsKey = "fcm_access_token";
  static const _tokenExpiryKey = "fcm_token_expiry";
  static const _apiTokenKey = "fcm_api_token"; // New key for API token

  // Your Firebase Project ID
  static const String projectId = "lloyalty-application";

  /// Load Service Account JSON from assets
  static Future<Map<String, dynamic>> _loadServiceAccountJson() async {
    try {
      String jsonString = await rootBundle.loadString(
        'assets/service_account.json',
      );
      return json.decode(jsonString);
    } catch (e) {
      debugPrint("❗ Error loading service account JSON: $e");
      rethrow;
    }
  }

  /// Check if current token is expired
  static Future<bool> _isTokenExpired() async {
    final prefs = await SharedPreferences.getInstance();
    final expiryTime = prefs.getInt(_tokenExpiryKey);

    if (expiryTime == null) return true;

    final now = DateTime.now().millisecondsSinceEpoch;
    return now >= expiryTime;
  }

  /// Save API token to SharedPreferences
  static Future<void> saveApiTokenToPrefs(String apiToken) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_apiTokenKey, apiToken);
      debugPrint("✅ API token saved to SharedPreferences");
    } catch (e) {
      debugPrint("❗ Error saving API token: $e");
    }
  }

  /// Get API token from SharedPreferences
  static Future<String?> getApiTokenFromPrefs() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final apiToken = prefs.getString(_apiTokenKey);

      if (apiToken != null && apiToken.isNotEmpty) {
        debugPrint("✅ API token retrieved from SharedPreferences");
        return apiToken;
      } else {
        debugPrint("❗ No API token found in SharedPreferences");
        return null;
      }
    } catch (e) {
      debugPrint("❗ Error getting API token from prefs: $e");
      return null;
    }
  }

  // Remove API token from SharedPreferences
  static Future<void> clearApiTokenFromPrefs() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_apiTokenKey);
      debugPrint("✅ API token cleared from SharedPreferences");
    } catch (e) {
      debugPrint("❗ Error clearing API token: $e");
    }
  }

  // Updated test method with token refresh
  static Future<void> sendTestNotification() async {
    try {
      // Force refresh token
      await FirebaseMessaging.instance.deleteToken();
      String? newToken = await FirebaseMessaging.instance.getToken();

      if (newToken == null) {
        debugPrint("❌ Failed to get new token");
        return;
      }

      debugPrint("🔄 New Token: ${newToken.substring(0, 20)}...");

      // Save new token
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('fcm_token', newToken);

      // Test notification
      final success = await FCMNotificationHelper.sendNotificationToToken(
        fcmToken: newToken,
        title: "Fresh Token Test",
        body: "Testing with newly generated token",
      );

      debugPrint(success ? "✅ Success!" : "❌ Still failed");
    } catch (e) {
      debugPrint("❌ Test failed: $e");
    }
  }

  // Get valid access token (Priority: Prefs -> Generate new)
  static Future<String?> getAccessToken() async {
    try {
      // First try to get API token from SharedPreferences
      String? apiToken = await getApiTokenFromPrefs();

      if (apiToken != null && apiToken.isNotEmpty) {
        debugPrint("✅ Using API token from SharedPreferences");
        return apiToken;
      }

      // If no API token, try cached access token
      final prefs = await SharedPreferences.getInstance();
      String? accessToken = prefs.getString(_tokenPrefsKey);

      // If no token exists or token is expired, generate new one
      if (accessToken == null || await _isTokenExpired()) {
        debugPrint("🔄 Token expired or missing, generating new token...");
        accessToken = await _generateAccessToken();
      } else {
        debugPrint("✅ Using cached access token");
        print('accessTokennnn.$accessToken');
      }

      return accessToken;
    } catch (e) {
      debugPrint("❗ Error getting access token: $e");
      return null;
    }
  }

  /// Generate new Access Token
  static Future<String?> _generateAccessToken() async {
    try {
      final jsonKey = await _loadServiceAccountJson();
      final accountCredentials = ServiceAccountCredentials.fromJson(jsonKey);
      const scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

      final client = await clientViaServiceAccount(accountCredentials, scopes);
      final accessToken = client.credentials.accessToken.data;

      // Token usually expires in 1 hour, save expiry time (55 minutes to be safe)
      final expiryTime = DateTime.now()
          .add(Duration(minutes: 55))
          .millisecondsSinceEpoch;

      // Save to SharedPreferences
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_tokenPrefsKey, accessToken);
      await prefs.setInt(_tokenExpiryKey, expiryTime);

      client.close();

      debugPrint("✅ New access token generated successfully");
      print('✔✔✔✔ access  $accessToken');
      return accessToken;
    } catch (e) {
      debugPrint("❗ Error generating access token: $e");
      return null;
    }
  }

  /// Send notification to specific FCM token
  static Future<bool> sendNotificationToToken({
    required String fcmToken,
    required String title,
    required String body,
    Map<String, dynamic>? data,
  }) async {
    try {
      final accessToken = await getAccessToken();
      if (accessToken == null) {
        debugPrint("❗ Failed to get access token");
        return false;
      }

      final url =
          'https://fcm.googleapis.com/v1/projects/lloyalty-application/messages:send';

      final payload = {
        "message": {
          "token": fcmToken,
          "notification": {"title": title, "body": body},
          if (data != null) "data": data,
          "android": {
            "priority": "HIGH",
            "notification": {
              "sound": "default",
              "channel_id": "high_importance_channel",
            },
          },
          "apns": {
            "payload": {
              "aps": {"sound": "default", "badge": 1},
            },
          },
        },
      };

      debugPrint("🚀 Sending notification to: $fcmToken");
      debugPrint("📝 Payload: ${jsonEncode(payload)}");

      final response = await http
          .post(
            Uri.parse(url),
            headers: {
              'Content-Type': 'application/json',
              'Authorization': 'Bearer $accessToken',
            },
            body: jsonEncode(payload),
          )
          .timeout(Duration(seconds: 10));

      debugPrint("📥 Response Status: ${response.statusCode}");
      debugPrint("📥 Response Body: ${response.body}");

      if (response.statusCode == 200) {
        debugPrint("✅ Notification sent successfully!");
        return true;
      } else {
        debugPrint("❌ Failed to send notification: ${response.statusCode}");
        debugPrint("❌ Error: ${response.body}");
        return false;
      }
    } catch (e) {
      debugPrint("❗ Exception while sending notification: $e");
      return false;
    }
  }

  /// Send notification to multiple tokens
  static Future<int> sendNotificationToMultipleTokens({
    required List<String> fcmTokens,
    required String title,
    required String body,
    Map<String, dynamic>? data,
  }) async {
    int successCount = 0;

    for (String token in fcmTokens) {
      final success = await sendNotificationToToken(
        fcmToken: token,
        title: title,
        body: body,
        data: data,
      );
      if (success) successCount++;

      // Add small delay between requests to avoid rate limiting
      await Future.delayed(Duration(milliseconds: 100));
    }

    debugPrint(
      "📊 Sent notifications to $successCount/${fcmTokens.length} devices",
    );
    return successCount;
  }

  /// Send notification to topic
  static Future<bool> sendNotificationToTopic({
    required String topic,
    required String title,
    required String body,
    Map<String, dynamic>? data,
  }) async {
    try {
      final accessToken = await getAccessToken();
      print('accessToken $accessToken');
      if (accessToken == null) {
        debugPrint("❗ Failed to get access token");
        return false;
      }

      final url =
          'https://fcm.googleapis.com/v1/projects/lloyalty-application/messages:send';

      final payload = {
        "message": {
          "topic": topic,
          "notification": {"title": title, "body": body},
          if (data != null) "data": data,
          "android": {
            "priority": "HIGH",
            "notification": {
              "sound": "default",
              "channel_id": "high_importance_channel",
            },
          },
        },
      };

      final response = await http.post(
        Uri.parse(url),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $accessToken',
        },
        body: jsonEncode(payload),
      );

      if (response.statusCode == 200) {
        debugPrint("✅ Topic notification sent successfully!");
        return true;
      } else {
        debugPrint(
          "❌ Failed to send topic notification: ${response.statusCode}",
        );
        return false;
      }
    } catch (e) {
      debugPrint("❗ Exception while sending topic notification: $e");
      return false;
    }
  }
}

// Usage Example Class
class NotificationService {
  /// Test notification - Manual API call
  static Future<void> sendTestNotification() async {
    // Get stored FCM token from SharedPreferences
    final prefs = await SharedPreferences.getInstance();
    final fcmToken =
        'c72ECWFdTmCiisxp4h17-q:APA91bEMBEY4i3e5xlYudd-HiXbbfe6y-5kmcmYfPb0jpjdzxZhqErDrzJsrbqFGIfey5qix1UZ2VTZv1f-OJYR449KtergzTbJWz8VbF6AAUOgbSsqH814';
    // final fcmToken =
    //     'f-V0lnOcSyi0EkgkoGgTVa:APA91bGd3hTwxhFIhpp6ip3PhpzeisnkLGLyAPZofmRmMbmAYMhePjCOBtsJ-5l1yfJhv-yKrcJD5x78_LN5Y7t6vjHvgVJO6zMCM2OPYDtqzDDCG0-Q5DU';

    if (fcmToken.isEmpty) {
      debugPrint("❗ No FCM token found in SharedPreferences");
      return;
    }

    // Send notification
    final success = await FCMNotificationHelper.sendNotificationToToken(
      fcmToken: fcmToken,
      title: "Test Notification",
      body: "Yeh manual API se bheja gaya notification hai!",
      data: {
        "screen": "home",
        "action": "test",
        "timestamp": DateTime.now().toIso8601String(),
      },
    );

    if (success) {
      debugPrint("🎉 Test notification sent successfully!");
    } else {
      debugPrint("❌ Failed to send test notification");
    }
  }

  /// Send promotional notification
  static Future<void> sendPromotionalNotification({
    required String userFcmToken,
    required String offerTitle,
    required String offerDescription,
  }) async {
    await FCMNotificationHelper.sendNotificationToToken(
      fcmToken: userFcmToken,
      title: offerTitle,
      body: offerDescription,
      data: {
        "type": "promotion",
        "screen": "offers",
        "timestamp": DateTime.now().toIso8601String(),
      },
    );
  }

  /// Send bulk notifications to all users
  static Future<void> sendBulkNotification({
    required List<String> allUserTokens,
    required String title,
    required String message,
  }) async {
    final successCount =
        await FCMNotificationHelper.sendNotificationToMultipleTokens(
          fcmTokens: allUserTokens,
          title: title,
          body: message,
          data: {"type": "bulk", "timestamp": DateTime.now().toIso8601String()},
        );

    debugPrint("📊 Bulk notification sent to $successCount users");
  }

  /// Save your custom API token
  static Future<void> saveCustomApiToken(String apiToken) async {
    await FCMNotificationHelper.saveApiTokenToPrefs(apiToken);
  }

  /// Get saved API token
  static Future<String?> getSavedApiToken() async {
    return await FCMNotificationHelper.getApiTokenFromPrefs();
  }

  // Clear saved API token
  static Future<void> clearSavedApiToken() async {
    await FCMNotificationHelper.clearApiTokenFromPrefs();
  }
}
