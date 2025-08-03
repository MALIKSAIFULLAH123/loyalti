import 'package:flutter/material.dart';
import 'package:loyalty_app/Auth/LanguageSelectionPage.dart';
import 'package:loyalty_app/Auth/SignIn.dart';
import 'package:loyalty_app/Services/language_service.dart';
import 'package:loyalty_app/utils/api_constants.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:image_picker/image_picker.dart';
import 'dart:io';

class Profile extends StatefulWidget {
  const Profile({super.key});

  @override
  _ProfileState createState() => _ProfileState();
}

class _ProfileState extends State<Profile> {
  String fullName = "Loading...";
  String phone = "Loading...";
  String password = "••••••••••••";
  String email = "Loading...";
  String createdAt = "Loading...";
  String totalPoints = "0";
  String redeemedPoints = "0";
  String address = "Loading...";
  String city = "Loading...";
  String zip = "Loading...";
  String trdr = "";
  String clientID = "";
  bool isLoading = true;
  String? profileImagePath; // Store image path

  int _selectedIndex = 4; // 👈 Account tab active
  final ImagePicker _picker = ImagePicker();

  @override
  void initState() {
    super.initState();
    _loadUserData();
    _loadProfileImage(); // Load saved profile image
  }

  // Load saved profile image from SharedPreferences
  Future<void> _loadProfileImage() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      profileImagePath = prefs.getString('profile_image_path');
    });
  }

  // Save profile image path to SharedPreferences
  Future<void> _saveProfileImage(String imagePath) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('profile_image_path', imagePath);
    setState(() {
      profileImagePath = imagePath;
    });
  }

  // Show image picker options
  void _showImagePickerOptions() {
    showModalBottomSheet(
      context: context,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (BuildContext context) {
        return Container(
          padding: EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              SizedBox(height: 20),
              Text(
                'Select Profile Picture',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
              SizedBox(height: 20),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  _buildImagePickerOption(
                    icon: Icons.camera_alt,
                    label: 'Camera',
                    onTap: () => _pickImage(ImageSource.camera),
                  ),
                  _buildImagePickerOption(
                    icon: Icons.photo_library,
                    label: 'Gallery',
                    onTap: () => _pickImage(ImageSource.gallery),
                  ),
                  if (profileImagePath != null)
                    _buildImagePickerOption(
                      icon: Icons.delete,
                      label: 'Remove',
                      onTap: _removeProfileImage,
                      color: Colors.red,
                    ),
                ],
              ),
              SizedBox(height: 20),
            ],
          ),
        );
      },
    );
  }

  Widget _buildImagePickerOption({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
    Color? color,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            padding: EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: (color ?? Colors.black).withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(
              icon,
              size: 30,
              color: color ?? Colors.black,
            ),
          ),
          SizedBox(height: 8),
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w500,
              color: color ?? Colors.black87,
            ),
          ),
        ],
      ),
    );
  }

  // Pick image from camera or gallery
  Future<void> _pickImage(ImageSource source) async {
    try {
      final XFile? image = await _picker.pickImage(
        source: source,
        maxWidth: 800,
        maxHeight: 800,
        imageQuality: 80,
      );
      
      Navigator.pop(context); // Close bottom sheet
      
      if (image != null) {
        await _saveProfileImage(image.path);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                Icon(Icons.check_circle, color: Colors.white, size: 20),
                SizedBox(width: 8),
                Text('Profile picture updated!'),
              ],
            ),
            backgroundColor: Colors.green,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            margin: EdgeInsets.all(16),
          ),
        );
      }
    } catch (e) {
      Navigator.pop(context); // Close bottom sheet
      _showError('Failed to pick image: ${e.toString()}');
    }
  }

  // Remove profile image
  Future<void> _removeProfileImage() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('profile_image_path');
    setState(() {
      profileImagePath = null;
    });
    Navigator.pop(context); // Close bottom sheet
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(Icons.check_circle, color: Colors.white, size: 20),
            SizedBox(width: 8),
            Text('Profile picture removed!'),
          ],
        ),
        backgroundColor: Colors.orange,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        margin: EdgeInsets.all(16),
      ),
    );
  }

  // Load user data from API
  Future<void> _loadUserData() async {
  setState(() {
    isLoading = true;
  });

  try {
    final prefs = await SharedPreferences.getInstance();
    trdr = prefs.getString('TRDR') ?? '';
    clientID = prefs.getString('clientID') ?? '';
    final companyUrl = prefs.getString('company_url');
    final softwareType = prefs.getString('software_type');

    if (trdr.isEmpty || clientID.isEmpty || companyUrl == null || softwareType == null) {
      _showError('Missing user credentials. Please login again.');
      return;
    }

    final servicePath = _getServicePath(softwareType);
    final uri = _buildApiUri(companyUrl, servicePath);

    final response = await http.post(
      uri,
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        "service": "SqlData",
        "clientID": clientID,
        "appId": "1001",
        "SqlName": "9700",
        "trdr": int.parse(trdr),
      }),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);

      if (data['success'] == true &&
          data['rows'] != null &&
          data['rows'].isNotEmpty) {
        final userInfo = data['rows'][0];

        // Handle fields safely
        String name = userInfo['NAME']?.toString().isNotEmpty == true
            ? userInfo['NAME'].toString()
            : userInfo['NAME']?.toString() ?? 'Full Name';

        String userEmail = userInfo['EMAIL']?.toString().isNotEmpty == true
            ? userInfo['EMAIL'].toString()
            : 'email@domain.gr';

        String userPhone = userInfo['PHONE01']?.toString().isNotEmpty == true
            ? userInfo['PHONE01'].toString()
            : '+30 6912345678';

        String rawDate = userInfo['INSDATE']?.toString() ?? '';
        String formattedDate = 'DD/MM/YYYY';
        if (rawDate.isNotEmpty && rawDate.contains(' ')) {
          String datePart = rawDate.split(' ')[0];
          try {
            DateTime parsedDate = DateTime.parse(datePart);
            formattedDate =
                "${parsedDate.day.toString().padLeft(2, '0')}/${parsedDate.month.toString().padLeft(2, '0')}/${parsedDate.year}";
          } catch (_) {
            formattedDate = datePart;
          }
        }

        String total = userInfo['totalpoints']?.toString() ?? '0';
        String redeemed = userInfo['redeemedpoints']?.toString() ?? '0';

        String userAddress = userInfo['ADDRESS']?.toString().isNotEmpty == true
            ? userInfo['ADDRESS'].toString()
            : 'ADDRESS';

        String userCity = userInfo['CITY']?.toString().isNotEmpty == true
            ? userInfo['CITY'].toString()
            : 'CITY';

        String userZip = userInfo['ZIP']?.toString().isNotEmpty == true
            ? userInfo['ZIP'].toString()
            : 'ZIP';

        String profileImageUrl = userInfo['IMAGE']?.toString() ?? '';

        // ✅ Save to SharedPreferences
        await prefs.setString('user_fullname', name);
        await prefs.setString('user_email', userEmail);
        await prefs.setString('user_phone', userPhone);
        await prefs.setString('user_created_at', formattedDate);
        await prefs.setString('user_total_points', total);
        await prefs.setString('user_redeemed_points', redeemed);
        await prefs.setString('user_address', userAddress);
        await prefs.setString('user_city', userCity);
        await prefs.setString('user_zip', userZip);
        await prefs.setString('user_profile_image', profileImageUrl);

        // ✅ Update UI
        if (mounted) {
          setState(() {
            fullName = name;
            email = userEmail;
            phone = userPhone;
            createdAt = formattedDate;
            totalPoints = total;
            redeemedPoints = redeemed;
            address = userAddress;
            city = userCity;
            zip = userZip;
            isLoading = false;
          });
        }
      } else {
        _showError('Failed to load user data');
        if (mounted) {
          setState(() => isLoading = false);
        }
      }
    } else {
      _showError('Server error: ${response.statusCode}');
      if (mounted) {
        setState(() => isLoading = false);
      }
    }
  } catch (e) {
    _showError('Network error: ${e.toString()}');
    if (mounted) {
      setState(() => isLoading = false);
    }
  }
}


  /// Returns appropriate service path based on software type
  String _getServicePath(String softwareType) {
    return softwareType == "TESAE" 
        ? "/pegasus/a_xit/connector.php"
        : "/s1services";
  }

  /// Builds API URI with CORS proxy
  Uri _buildApiUri(String companyUrl, String servicePath) {
    return Uri.parse(
      "${ApiConstants.baseUrl}https://$companyUrl$servicePath",
    );
  }

  // Update user data via API
  Future<void> _updateUserData() async {
    try {
      setState(() {
        isLoading = true;
      });

      final prefs = await SharedPreferences.getInstance();
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');

      if (companyUrl == null || softwareType == null) {
        _showError('Missing configuration. Please restart the app.');
        return;
      }

      final servicePath = _getServicePath(softwareType);
      final uri = _buildApiUri(companyUrl, servicePath);

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          "service": "setData",
          "clientID": clientID,
          "appId": "1001",
          "OBJECT": "CUSTOMER[FORM=WEB]",
          "KEY": trdr,
          "data": {
            "CUSTOMER": [
              {
                "CODE": phone,
                "NAME": fullName,
                "EMAIL": email,
                "PHONE01": phone,
                "ADDRESS": address,
                "CITY": city,
                "ZIP": zip,
                "CCCXITUSERNAME": "username",
                "CCCXITPASSWORD": password,
                "REMARKS": "Updated via app",
              },
            ],
          },
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);

        if (data['success'] == true) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  Icon(Icons.check_circle, color: Colors.white, size: 20),
                  SizedBox(width: 8),
                  Text('Profile updated successfully!'),
                ],
              ),
              backgroundColor: Colors.green,
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              margin: EdgeInsets.all(16),
            ),
          );
        } else {
          _showError('Failed to update profile');
        }
      } else {
        _showError('Server error: ${response.statusCode}');
      }
    } catch (e) {
      _showError('Network error: ${e.toString()}');
    } finally {
      setState(() {
        isLoading = false;
      });
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(Icons.error_outline, color: Colors.white, size: 20),
            SizedBox(width: 8),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: Colors.red,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        margin: EdgeInsets.all(16),
      ),
    );
  }

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });

    switch (index) {
      case 0:
        Navigator.pushReplacementNamed(context, '/home');
        break;
      case 1:
        _showComingSoon(
          AppLocalizations.of(context)!.translate('rewards'),
          Icons.card_giftcard,
        );
        break;
      case 2:
        _showComingSoon(
          AppLocalizations.of(context)!.translate('qr_scanner'),
          Icons.qr_code_scanner,
        );
        break;
      case 3:
        _showComingSoon(
          AppLocalizations.of(context)!.translate('notifications'),
          Icons.notifications,
        );
        break;
      case 4:
        // Already on Profile, do nothing
        break;
    }
  }

  void _showComingSoon(String feature, IconData icon) {
    final localizations = AppLocalizations.of(context)!;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(icon, color: Colors.white, size: 20),
            const SizedBox(width: 12),
            Text(
              '${localizations.translate('feature_coming_soon')} $feature!',
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
          ],
        ),
        backgroundColor: Colors.orange,
        duration: const Duration(seconds: 3),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

  void showEditDialog(
    String title,
    String initialValue,
    Function(String) onSave,
  ) {
    final localizations = AppLocalizations.of(context)!;
    TextEditingController controller = TextEditingController(
      text: initialValue == "Loading..." ? "" : initialValue,
    );

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
        title: Text(
          "${localizations.translate('edit')} $title",
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        content: TextField(
          controller: controller,
          decoration: InputDecoration(
            hintText: "${localizations.translate('enter')} $title",
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
            filled: true,
            fillColor: Colors.grey.shade50,
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              localizations.translate('cancel'),
              style: TextStyle(color: Colors.grey.shade600),
            ),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor:  Color(0xFFEC7103),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () {
              if (controller.text.trim().isNotEmpty) {
                onSave(controller.text.trim());
                Navigator.pop(context);
              }
            },
            child: Text(
              localizations.translate('save'),
              style: TextStyle(color: Colors.white),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final localizations = AppLocalizations.of(context)!;

    return Scaffold(
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        backgroundColor:  Color(0xFFEC7103),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          localizations.translate('account'),
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
        actions: [
          IconButton(
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => const LanguageSelectionPage(),
                ),
              );
            },
            icon: const Icon(Icons.g_translate, color: Colors.white, size: 24),
            tooltip: localizations.translate('change_language'),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadUserData,
        color: Colors.black,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // Profile Header
              Container(
                width: double.infinity,
                padding: EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(15),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.05),
                      blurRadius: 10,
                      offset: Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    // Profile Picture with Edit Option
                    GestureDetector(
                      onTap: _showImagePickerOptions,
                      child: Stack(
                        children: [
                          Container(
                            padding: EdgeInsets.all(4),
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              gradient: LinearGradient(
                                colors: [ Color.fromARGB(255, 212, 101, 3), Color.fromARGB(155, 255, 151, 61)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                            ),
                            child: CircleAvatar(
                              radius: 50,
                              backgroundColor: Colors.white,
                              backgroundImage: profileImagePath != null 
                                  ? FileImage(File(profileImagePath!))
                                  : null,
                              child: profileImagePath == null
                                  ? Icon(
                                      Icons.person,
                                      size: 60,
                                      color: Colors.grey.shade600,
                                    )
                                  : null,
                            ),
                          ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              padding: EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color: Color(0xFFEC7103),
                                shape: BoxShape.circle,
                                border: Border.all(color: Colors.white, width: 2),
                              ),
                              child: Icon(
                                Icons.camera_alt,
                                size: 16,
                                color: Colors.white,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    isLoading
                        ? SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.black,
                            ),
                          )
                        : Column(
                            children: [
                              Text(
                                fullName,
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 20,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                email,
                                style: TextStyle(
                                  color: Colors.grey.shade600,
                                  fontSize: 14,
                                ),
                              ),
                            ],
                          ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              // Points Card
              Container(
                width: double.infinity,
                padding: EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [  Color.fromARGB(255, 236, 88, 3),  Color.fromARGB(255, 255, 167, 89)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(15),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.1),
                      blurRadius: 10,
                      offset: Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            localizations.translate('points_earned'),
                            style: TextStyle(
                              color: Colors.white70,
                              fontSize: 12,
                            ),
                          ),
                          SizedBox(height: 4),
                          Text(
                            totalPoints,
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      height: 40,
                      width: 1,
                      color: Colors.white30,
                    ),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            localizations.translate('points_redeemed'),
                            style: TextStyle(
                              color: Colors.white70,
                              fontSize: 12,
                            ),
                          ),
                          SizedBox(height: 4),
                          Text(
                            redeemedPoints,
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              // Personal Details
              _buildInfoCard(
                localizations.translate('personal_details'),
                [
                  _buildDetailRow(
                    localizations.translate('full_name'),
                    fullName,
                    Icons.person_outline,
                    
                    editable: true,
                    onTap: () {
                      showEditDialog(
                        localizations.translate('full_name'),
                        fullName,
                        (value) => setState(() => fullName = value),
                      );
                    },
                  ),
                  _buildDetailRow(
                    localizations.translate('email'),
                    email,
                    Icons.email_outlined,
                  ),
                  _buildDetailRow(
                    localizations.translate('phone'),
                    phone,
                    Icons.phone_outlined,
                    editable: true,
                    onTap: () {
                      showEditDialog(
                        localizations.translate('phone'),
                        phone,
                        (value) => setState(() => phone = value),
                      );
                    },
                  ),
                  _buildDetailRow(
                    localizations.translate('address'),
                    address,
                    Icons.location_on_outlined,
                    editable: true,
                    onTap: () {
                      showEditDialog(
                        localizations.translate('address'),
                        address,
                        (value) => setState(() => address = value),
                      );
                    },
                  ),
                  _buildDetailRow(
                    localizations.translate('city'),
                    city,
                    Icons.location_city_outlined,
                    editable: true,
                    onTap: () {
                      showEditDialog(
                        localizations.translate('city'),
                        city,
                        (value) => setState(() => city = value),
                      );
                    },
                  ),
                  _buildDetailRow(
                    localizations.translate('zip'),
                    zip,
                    Icons.markunread_mailbox_outlined,
                    editable: true,
                    onTap: () {
                      showEditDialog(
                        localizations.translate('zip'),
                        zip,
                        (value) => setState(() => zip = value),
                      );
                    },
                  ),
                  _buildDetailRow(
                    localizations.translate('password'),
                    password,
                    Icons.lock_outline,
                    editable: true,
                    onTap: () {
                      showEditDialog(
                        localizations.translate('password'),
                        '',
                        (value) => setState(() => password = value),
                      );
                    },
                  ),
                ],
              ),

              const SizedBox(height: 16),

              // Account Details
              _buildInfoCard(
                localizations.translate('account_details'),
                [
                  _buildDetailRow(
                    localizations.translate('account_created'),
                    createdAt,
                    Icons.calendar_today_outlined,
                  ),
                  _buildDetailRow(
                    "TRDR ID",
                    trdr,
                    Icons.badge_outlined,
                  ),
                ],
              ),

              const SizedBox(height: 30),

              // Update Profile Button
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor:  Color(0xFFEC7103),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 2,
                  ),
                  onPressed: isLoading ? null : _updateUserData,
                  child: isLoading
                      ? SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.save_outlined, color: Colors.white, size: 20),
                            SizedBox(width: 8),
                            Text(
                              localizations.translate('update_profile'),
                              style: const TextStyle(
                                fontSize: 16,
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                ),
              ),

              const SizedBox(height: 12),

              // Logout Button
              SizedBox(
                width: double.infinity,
                height: 50,
                child: OutlinedButton(
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Colors.red, width: 1.5),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  onPressed: () async {
                    // Show confirmation dialog
                    bool shouldLogout = await showDialog(
                      context: context,
                      builder: (context) => AlertDialog(
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
                        title: Text('Confirm Logout'),
                        content: Text('Are you sure you want to logout?'),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(context, false),
                            child: Text('Cancel', style: TextStyle(color: Colors.grey)),
                          ),
                          ElevatedButton(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.red,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            ),
                            onPressed: () => Navigator.pop(context, true),
                            child: Text('Logout', style: TextStyle(color: Colors.white)),
                          ),
                        ],
                      ),
                    ) ?? false;

                    if (shouldLogout) {
                      final prefs = await SharedPreferences.getInstance();
                      await prefs.clear();

                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Row(
                            children: [
                              Icon(Icons.check_circle, color: Colors.white, size: 20),
                              SizedBox(width: 8),
                              Text('Logged out successfully'),
                            ],
                          ),
                          backgroundColor: Colors.green,
                          behavior: SnackBarBehavior.floating,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          margin: EdgeInsets.all(16),
                        ),
                      );

                      Navigator.pushAndRemoveUntil(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const SignInScreen(),
                        ),
                        (route) => false,
                      );
                    }
                  },
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.logout_outlined, color: Colors.red, size: 20),
                      SizedBox(width: 8),
                      Text(
                        localizations.translate('logout'),
                        style: const TextStyle(
                          fontSize: 16,
                          color: Colors.red,
                          fontWeight: FontWeight.w600,
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
      ),
    );
  }

  Widget _buildInfoCard(String title, List<Widget> children) {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(15),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 16),
          ...children,
        ],
      ),
    );
  }

  Widget _buildDetailRow(
    String title,
    String value,
    IconData icon, {
    bool editable = false,
    VoidCallback? onTap,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        children: [
          Container(
            padding: EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: Colors.grey.shade100,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(
              icon,
              size: 20,
              color: Colors.grey.shade600,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey.shade600,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: Colors.black87,
                  ),
                ),
              ],
            ),
          ),
          if (editable)
            GestureDetector(
              onTap: onTap,
              child: Container(
                padding: EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.05),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(
                  Icons.edit_outlined,
                  size: 18,
                  color:  Color(0xFFEC7103),
                ),
              ),
            ),
        ],
      ),
    );
  }
}