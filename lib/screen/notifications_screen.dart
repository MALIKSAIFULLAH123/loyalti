import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:loyalty_app/Services/language_service.dart';
import 'package:loyalty_app/Auth/LanguageSelectionPage.dart';
import 'package:loyalty_app/utils/api_constants.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter/foundation.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<NotificationItem> notifications = [];
  bool isLoading = true;
  String? errorMessage;
  bool isRefreshing = false;
  String userName = "User";
  String? profileImagePath;
  Timer? _autoMarkReadTimer;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
    _loadUserName();
    _loadProfileImage();
    
    // Start timer to mark all as read after 3 seconds
    _autoMarkReadTimer = Timer(const Duration(seconds: 3), () {
      _markAllNotificationsAsRead();
    });
  }

  @override
  void dispose() {
    _autoMarkReadTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadNotifications() async {
    try {
      if (!isRefreshing) {
        setState(() {
          isLoading = true;
          errorMessage = null;
        });
      }

      final prefs = await SharedPreferences.getInstance();
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');
      final clientID = prefs.getString('clientID');
      final trdr = prefs.getString('TRDR');

      if (companyUrl == null ||
          softwareType == null ||
          clientID == null ||
          trdr == null) {
        throw Exception("Required settings are missing. Please login again.");
      }

      final servicePath = softwareType == "TESAE"
          ? "/pegasus/a_xit/connector.php"
          : "/s1services";

      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://$companyUrl$servicePath",
      );

      final response = await http
          .post(
            uri,
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'User-Agent': 'LoyaltyApp/1.0',
            },
            body: jsonEncode({
              "service": "SqlData",
              "clientID": clientID,
              "appId": "1001",
              "SqlName": "9702",
              "trdr": trdr,
            }),
          )
          .timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);

        if (data is Map<String, dynamic>) {
          if (data['success'] == true) {
            final List<dynamic> rows = data['rows'] ?? [];

            setState(() {
              notifications = rows
                  .map((row) => NotificationItem.fromApi(row))
                  .toList();
              
              // Sort notifications by date (newest first)
              notifications.sort((a, b) => b.date.compareTo(a.date));
              
              isLoading = false;
              isRefreshing = false;
            });
          } else {
            throw Exception(data['message'] ?? "API request failed");
          }
        } else {
          throw Exception("Invalid response format");
        }
      } else {
        throw Exception("Server error: ${response.statusCode}");
      }
    } catch (e) {
      setState(() {
        errorMessage =
            'Failed to load notifications: ${e.toString().replaceAll(RegExp(r'^Exception: '), '')}';
        isLoading = false;
        isRefreshing = false;
      });

      if (kDebugMode) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')));
      }
    }
  }

  Future<void> _markAllNotificationsAsRead() async {
    if (notifications.isEmpty) return;

    try {
      final prefs = await SharedPreferences.getInstance();
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');
      final clientID = prefs.getString('clientID');

      if (companyUrl == null || softwareType == null || clientID == null) {
        throw Exception("Missing required settings");
      }

      final servicePath = softwareType == "TESAE"
          ? "/pegasus/a_xit/connector.php"
          : "/s1services";

      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://$companyUrl$servicePath",
      );

      List<Future> markReadRequests = [];
      
      for (var notification in notifications) {
        if (!notification.isRead) {
          final requestBody = {
            "service": "setData",
            "clientID": clientID,
            "appId": "1001",
            "OBJECT": "SOACTION",
            "KEY": notification.id,
            "data": {
              "SOACTION": [
                {
                  "ACTSTATUS": "3",
                },
              ],
            },
          };

          markReadRequests.add(
            http.post(
              uri,
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'User-Agent': 'LoyaltyApp/1.0',
              },
              body: jsonEncode(requestBody),
            ).timeout(const Duration(seconds: 10))
          );
        }
      }

      if (markReadRequests.isNotEmpty) {
        await Future.wait(markReadRequests);
        
        setState(() {
          for (var notification in notifications) {
            notification.isRead = true;
          }
        });

        if (kDebugMode) {
          print("✅ All notifications marked as read");
        }
      }
    } catch (e) {
      if (kDebugMode) {
        print("❗ Error marking all as read: $e");
      }
    }
  }

  Future<void> _loadProfileImage() async {
    final prefs = await SharedPreferences.getInstance();
    final path = prefs.getString('user_profile_image');
    setState(() {
      profileImagePath = path;
    });
  }

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
                child: (profileImagePath != null &&
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
                    userName,
                    style: GoogleFonts.dmSans(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            ),
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

  Future<void> _handleRefresh() async {
    setState(() {
      isRefreshing = true;
    });
    await _loadNotifications();
  }

  Future<void> _markAsRead(NotificationItem notification) async {
    if (notification.isRead) return;

    try {
      final prefs = await SharedPreferences.getInstance();
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');
      final clientID = prefs.getString('clientID');

      if (companyUrl == null || softwareType == null || clientID == null) {
        throw Exception("Missing required settings");
      }

      final servicePath = softwareType == "TESAE"
          ? "/pegasus/a_xit/connector.php"
          : "/s1services";

      final uri = Uri.parse(
        "${ApiConstants.baseUrl}https://$companyUrl$servicePath",
      );

      final requestBody = {
        "service": "setData",
        "clientID": clientID,
        "appId": "1001",
        "OBJECT": "SOACTION",
        "KEY": notification.id,
        "data": {
          "SOACTION": [
            {
              "ACTSTATUS": "3",
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

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map<String, dynamic> && data['success'] == true) {
          setState(() {
            notification.isRead = true;
          });
        } else {
          throw Exception(data['message'] ?? "Failed to mark as read");
        }
      } else {
        throw Exception("HTTP ${response.statusCode}");
      }
    } catch (e) {
      if (kDebugMode) {
        print("❗ Mark as read error: $e");
      }
    }
  }

 void _openNotificationDetail(NotificationItem notification) {
  // Mark as read when opening detail
  _markAsRead(notification);
  
  Navigator.push(
    context,
    MaterialPageRoute(
      builder: (context) => NotificationDetailScreen(notification: notification),
    ),
  );
}

  @override
  Widget build(BuildContext context) {
    final localizations = AppLocalizations.of(context)!;

    return WillPopScope(
      onWillPop: () async {
        // Mark all as read when navigating back
        setState(() {
          for (var notification in notifications) {
            notification.isRead = true;
          }
        });
        return true;
      },
      child: Scaffold(
        backgroundColor: const Color(0xFFF5F5F5),
        body: Column(
          children: [
            _buildHeaderSection(),
            Expanded(child: _buildBody(localizations)),
          ],
        ),
      ),
    );
  }

  Widget _buildBody(AppLocalizations localizations) {
    if (isLoading && !isRefreshing) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(Color(0xFFEC7103)),
        ),
      );
    }

    if (errorMessage != null) {
      return RefreshIndicator(
        onRefresh: _handleRefresh,
        color: const Color(0xFFEC7103),
        backgroundColor: Colors.white,
        strokeWidth: 2.0,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: SizedBox(
            height: MediaQuery.of(context).size.height - 100,
            child: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 64, color: Colors.red),
                  const SizedBox(height: 16),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24),
                    child: Text(
                      errorMessage!,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 16,
                        fontFamily: 'Poppins',
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: _loadNotifications,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFEC7103),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 24,
                        vertical: 12,
                      ),
                    ),
                    child: const Text(
                      'Retry',
                      style: TextStyle(fontFamily: 'Poppins'),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    if (notifications.isEmpty) {
      return RefreshIndicator(
        onRefresh: _handleRefresh,
        color: const Color(0xFFEC7103),
        backgroundColor: Colors.white,
        strokeWidth: 2.0,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: SizedBox(
            height: MediaQuery.of(context).size.height - 100,
            child: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(
                    Icons.notifications_none,
                    size: 64,
                    color: Colors.grey,
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'No notifications',
                    style: TextStyle(
                      fontSize: 16,
                      color: Colors.grey,
                      fontFamily: 'Poppins',
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _handleRefresh,
      color: const Color(0xFFEC7103),
      backgroundColor: Colors.white,
      strokeWidth: 2.0,
      child: ListView.separated(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.symmetric(vertical: 8),
        itemCount: notifications.length,
        separatorBuilder: (context, index) => Container(
          height: 1,
          color: const Color(0xFFE5E5E5),
          margin: const EdgeInsets.symmetric(horizontal: 16),
        ),
        itemBuilder: (context, index) {
          final item = notifications[index];
          return NotificationTile(
            item: item, 
            onTap: () => _openNotificationDetail(item),
          );
        },
      ),
    );
  }
}

// New Notification Detail Screen
class NotificationDetailScreen extends StatelessWidget {
  final NotificationItem notification;

  const NotificationDetailScreen({
    super.key,
    required this.notification,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        backgroundColor: const Color(0xFFEC7103),
        foregroundColor: Colors.white,
        title: Text(
          'Notification Details',
          style: GoogleFonts.dmSans(
            fontSize: 18,
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header card with icon, title and date
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.08),
                    blurRadius: 12,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      _getNotificationIcon(notification.title),
                      const SizedBox(width: 20),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              notification.title,
                              style: GoogleFonts.dmSans(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                color: Colors.black,
                              ),
                            ),
                            const SizedBox(height: 12),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEC7103).withOpacity(0.1),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.access_time,
                                    size: 16,
                                    color: Color(0xFFEC7103),
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    notification.formattedDateTime,
                                    style: const TextStyle(
                                      fontSize: 14,
                                      color: Color(0xFFEC7103),
                                      fontFamily: 'Poppins',
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              notification.timeAgo,
                              style: const TextStyle(
                                fontSize: 13,
                                color: Color(0xFF999999),
                                fontFamily: 'Poppins',
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: 24),
            
            // Full message content card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.08),
                    blurRadius: 12,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(
                        Icons.message,
                        color: Color(0xFFEC7103),
                        size: 20,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Message Details',
                        style: GoogleFonts.dmSans(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFFEC7103),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8F9FA),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE9ECEF)),
                    ),
                    child: Text(
                      notification.message.isNotEmpty 
                          ? notification.message 
                          : 'No message content available.',
                      style: const TextStyle(
                        fontSize: 16,
                        color: Colors.black87,
                        fontFamily: 'Poppins',
                        height: 1.6,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: 24),
            
            // Status indicator
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.08),
                    blurRadius: 12,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Icon(
                    notification.isRead ? Icons.mark_email_read : Icons.mark_email_unread,
                    color: notification.isRead ? Colors.green : Colors.orange,
                    size: 24,
                  ),
                  const SizedBox(width: 12),
                  Text(
                    notification.isRead ? 'Read' : 'Unread',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: notification.isRead ? Colors.green : Colors.orange,
                      fontFamily: 'Poppins',
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
   Widget _getNotificationIcon(String title) {
    final lowerTitle = title.toLowerCase();

    if (lowerTitle.contains('points') || lowerTitle.contains('redeem') || lowerTitle.contains('reward')) {
      return Container(
        width: 56,
        height: 56,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [const Color(0xFFEC7103).withOpacity(0.2), const Color(0xFFFF8A3D).withOpacity(0.2)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: const Center(
          child: Icon(Icons.card_giftcard, color: Color(0xFFEC7103), size: 28),
        ),
      );
    } else if (lowerTitle.contains('newsletter') || lowerTitle.contains('mail')) {
      return Container(
        width: 56,
        height: 56,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [const Color(0xFFEC7103).withOpacity(0.2), const Color(0xFFFF8A3D).withOpacity(0.2)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: const Center(
          child: Icon(Icons.mail_outline, color: Color(0xFFEC7103), size: 28),
        ),
      );
    } else if (lowerTitle.contains('welcome') || lowerTitle.contains('hello')) {
      return Container(
        width: 56,
        height: 56,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [const Color(0xFFEC7103).withOpacity(0.2), const Color(0xFFFF8A3D).withOpacity(0.2)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: const Center(
          child: Icon(Icons.emoji_emotions, color: Color(0xFFEC7103), size: 28),
        ),
      );
    } else if (lowerTitle.contains('offer') || lowerTitle.contains('deal') || lowerTitle.contains('discount')) {
      return Container(
        width: 56,
        height: 56,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [const Color(0xFFEC7103).withOpacity(0.2), const Color(0xFFFF8A3D).withOpacity(0.2)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: const Center(
          child: Icon(Icons.local_offer, color: Color(0xFFEC7103), size: 28),
        ),
      );
    } else if (lowerTitle.contains('meeting') || lowerTitle.contains('demo')) {
      return Container(
        width: 56,
        height: 56,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [const Color(0xFFEC7103).withOpacity(0.2), const Color(0xFFFF8A3D).withOpacity(0.2)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: const Center(
          child: Icon(Icons.event, color: Color(0xFFEC7103), size: 28),
        ),
      );
    } else {
      return Container(
        width: 56,
        height: 56,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [const Color(0xFFEC7103).withOpacity(0.2), const Color(0xFFFF8A3D).withOpacity(0.2)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: const Center(
          child: Icon(Icons.notifications, color: Color(0xFFEC7103), size: 28),
        ),
      );
    }
  }
}


  Widget _buildDetailRow(String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 80,
          child: Text(
            '$label:',
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Color(0xFF666666),
              fontFamily: 'Poppins',
            ),
          ),
        ),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(
              fontSize: 14,
              color: Colors.black87,
              fontFamily: 'Poppins',
            ),
          ),
        ),
      ],
    );
  }

  // Widget _getNotificationIcon(String title) {
  //   final lowerTitle = title.toLowerCase();

  //   if (lowerTitle.contains('points') && lowerTitle.contains('redeem')) {
  //     return Container(
  //       width: 48,
  //       height: 48,
  //       decoration: BoxDecoration(
  //         color: const Color(0xFFEC7103).withOpacity(0.1),
  //         borderRadius: BorderRadius.circular(12),
  //       ),
  //       child: Stack(
  //         children: [
  //           Center(
  //             child: Image.asset(
  //               'assets/icons/gift-icon.png',
  //               width: 24,
  //               height: 24,
  //             ),
  //           ),
  //           const Positioned(
  //             top: 8,
  //             right: 8,
  //             child: Icon(Icons.star, color: Colors.amber, size: 12),
  //           ),
  //         ],
  //       ),
  //     );
  //   } else if (lowerTitle.contains('newsletter')) {
  //     return Container(
  //       width: 48,
  //       height: 48,
  //       decoration: BoxDecoration(
  //         color: const Color(0xFFEC7103).withOpacity(0.1),
  //         borderRadius: BorderRadius.circular(12),
  //       ),
  //       child: const Center(
  //         child: Icon(Icons.mail_outline, color: Color(0xFFEC7103), size: 24),
  //       ),
  //     );
  //   } else if (lowerTitle.contains('welcome')) {
  //     return Container(
  //       width: 48,
  //       height: 48,
  //       decoration: BoxDecoration(
  //         color: const Color(0xFFEC7103).withOpacity(0.1),
  //         borderRadius: BorderRadius.circular(12),
  //       ),
  //       child: const Center(
  //         child: Icon(Icons.emoji_emotions, color: Color(0xFFEC7103), size: 24),
  //       ),
  //     );
  //   } else if (lowerTitle.contains('offer') || lowerTitle.contains('reward')) {
  //     return Container(
  //       width: 48,
  //       height: 48,
  //       decoration: BoxDecoration(
  //         color: const Color(0xFFEC7103).withOpacity(0.1),
  //         borderRadius: BorderRadius.circular(12),
  //       ),
  //       child: const Center(
  //         child: Icon(Icons.local_offer, color: Color(0xFFEC7103), size: 24),
  //       ),
  //     );
  //   } else {
  //     return Container(
  //       width: 48,
  //       height: 48,
  //       decoration: BoxDecoration(
  //         color: const Color(0xFFEC7103).withOpacity(0.1),
  //         borderRadius: BorderRadius.circular(12),
  //       ),
  //       child: Center(
  //         child: Image.asset(
  //           'assets/icons/open-mail.png',
  //           width: 24,
  //           height: 24,
  //         ),
  //       ),
  //     );
  //   }
  // }


class NotificationTile extends StatelessWidget {
  final NotificationItem item;
  final VoidCallback? onTap;

  const NotificationTile({super.key, required this.item, this.onTap});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: item.isRead
          ? Colors.white
          : const Color(0xFFEC7103).withOpacity(0.1),
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Red dot for unread notifications
              if (!item.isRead)
                Container(
                  width: 8,
                  height: 8,
                  margin: const EdgeInsets.only(top: 8, right: 12),
                  decoration: const BoxDecoration(
                    color: Colors.red,
                    shape: BoxShape.circle,
                  ),
                ),
              // Icon
              Container(
                width: 48,
                height: 48,
                margin: EdgeInsets.only(left: item.isRead ? 20 : 0),
                child: _getNotificationIcon(item.title),
              ),
              const SizedBox(width: 16),
              // Content
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Title
                    Text(
                      item.title,
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: item.isRead ? Colors.black87 : Colors.black,
                        fontFamily: 'DM Sans',
                      ),
                    ),
                    // Body message as subtitle in gray
                    if (item.message.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        item.message,
                        style: const TextStyle(
                          fontSize: 14,
                          color: Color(0xFF666666),
                          fontFamily: 'Poppins',
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                    const SizedBox(height: 8),
                    // Date and time with better formatting
                    Row(
                      children: [
                        const Icon(
                          Icons.access_time,
                          size: 14,
                          color: Color(0xFF999999),
                        ),
                        const SizedBox(width: 4),
                        Text(
                          item.formattedDateTime,
                          style: const TextStyle(
                            fontSize: 12,
                            color: Color(0xFF999999),
                            fontFamily: 'Poppins',
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              // Time ago and arrow
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    item.timeAgo,
                    style: const TextStyle(
                      fontSize: 12,
                      color: Color(0xFF999999),
                      fontFamily: 'Poppins',
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Icon(
                    Icons.arrow_forward_ios,
                    size: 16,
                    color: Color(0xFFEC7103),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
 Widget _getNotificationIcon(String title) {
    final lowerTitle = title.toLowerCase();

    if (lowerTitle.contains('points') || lowerTitle.contains('redeem') || lowerTitle.contains('reward')) {
      return Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: const Center(
          child: Icon(Icons.card_giftcard, color: Color(0xFFEC7103), size: 24),
        ),
      );
    } else if (lowerTitle.contains('newsletter') || lowerTitle.contains('mail')) {
      return Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: const Center(
          child: Icon(Icons.mail_outline, color: Color(0xFFEC7103), size: 24),
        ),
      );
    } else if (lowerTitle.contains('welcome') || lowerTitle.contains('hello')) {
      return Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: const Center(
          child: Icon(Icons.emoji_emotions, color: Color(0xFFEC7103), size: 24),
        ),
      );
    } else if (lowerTitle.contains('offer') || lowerTitle.contains('deal') || lowerTitle.contains('discount')) {
      return Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: const Center(
          child: Icon(Icons.local_offer, color: Color(0xFFEC7103), size: 24),
        ),
      );
    } else if (lowerTitle.contains('meeting') || lowerTitle.contains('demo')) {
      return Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: const Center(
          child: Icon(Icons.event, color: Color(0xFFEC7103), size: 24),
        ),
      );
    } else {
      return Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: const Center(
          child: Icon(Icons.notifications, color: Color(0xFFEC7103), size: 24),
        ),
      );
    }
  }
}

class NotificationItem {
  final String id;
  final String title;
  final String message;
  final DateTime date;
  bool isRead;

  NotificationItem({
    required this.id,
    required this.title,
    required this.message,
    required this.date,
    required this.isRead,
  });

  factory NotificationItem.fromApi(Map<String, dynamic> json) {
    return NotificationItem(
      id: json['soaction'].toString(),
      title: json['title'] ?? 'No Title',
      message: json['bodymessage'] ?? '',
      date: DateTime.parse(json['fromdate']),
      isRead: json['ACTSTATUS'] == '3',
    );
  }

  String get formattedDateTime {
    final months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    
    final month = months[date.month - 1];
    final day = date.day.toString().padLeft(2, '0');
    final year = date.year;
    final hour = date.hour.toString().padLeft(2, '0');
    final minute = date.minute.toString().padLeft(2, '0');
    
    return '$day $month $year, $hour:$minute';
  }

  String get timeAgo {
    final now = DateTime.now();
    final difference = now.difference(date);

    if (difference.inDays > 0) {
      return '${difference.inDays}d ago';
    } else if (difference.inHours > 0) {
      return '${difference.inHours}h ago';
    } else if (difference.inMinutes > 0) {
      return '${difference.inMinutes}m ago';
    } else {
      return 'Just now';
    }
  }
}