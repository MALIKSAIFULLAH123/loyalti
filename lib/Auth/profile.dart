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
import 'dart:typed_data';

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
  String? profileImagePath;
  int _selectedIndex = 4;
  final ImagePicker _picker = ImagePicker();
  String? profileImageBase64;
  Uint8List? profileImageBytes;
  String fcmToken = "Loading...";

  @override
  void initState() {
    super.initState();
    _loadUserData();
    _loadProfileImageFromBase64();
  }

  Future<void> _loadProfileImageFromBase64() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final base64Image = prefs.getString('profile_image_base64');

      if (base64Image != null && base64Image.isNotEmpty) {
        if (base64Image.length < 100) {
          debugPrint("⚠️ Base64 string seems too short, might be corrupted");
          await prefs.remove('profile_image_base64');
          return;
        }

        Uint8List imageBytes = base64Decode(base64Image);

        if (mounted) {
          setState(() {
            profileImageBase64 = base64Image;
            profileImageBytes = imageBytes;
          });
        }

        debugPrint(
          "📥 Profile image loaded successfully from base64 (${(imageBytes.length / 1024).round()}KB)",
        );
      } else {
        debugPrint("ℹ️ No profile image found in storage");
      }
    } catch (e) {
      debugPrint("❌ Error loading base64 image: $e");
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('profile_image_base64');

      if (mounted) {
        setState(() {
          profileImageBase64 = null;
          profileImageBytes = null;
        });
      }
    }
  }

  Future<void> _saveProfileImageAsBase64(String imagePath) async {
    try {
      final file = File(imagePath);

      if (!await file.exists()) {
        throw Exception(AppLocalizations.of(context)!.imageFileNotFound);
      }

      final bytes = await file.readAsBytes();

      if (bytes.length > 2 * 1024 * 1024) {
        throw Exception(AppLocalizations.of(context)!.imageSizeTooLarge);
      }

      String base64Image = base64Encode(bytes);

      if (base64Image.isEmpty) {
        throw Exception(AppLocalizations.of(context)!.imageEncodingFailed);
      }

      final prefs = await SharedPreferences.getInstance();
      bool saved = await prefs.setString('profile_image_base64', base64Image);

      if (!saved) {
        throw Exception(AppLocalizations.of(context)!.failedSaveImage);
      }

      if (mounted) {
        setState(() {
          profileImageBase64 = base64Image;
          profileImageBytes = bytes;
        });
      }

      debugPrint(
        "✅ Profile image saved successfully as base64 (${(bytes.length / 1024).round()}KB)",
      );
    } catch (e) {
      debugPrint("❌ Error saving profile image: $e");
      rethrow;
    }
  }

  void _showImagePickerOptions() {
    final localizations = AppLocalizations.of(context)!;

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
                localizations.selectProfilePicture,
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              SizedBox(height: 20),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  _buildImagePickerOption(
                    icon: Icons.camera_alt,
                    label: localizations.camera,
                    onTap: () => _pickImage(ImageSource.camera),
                  ),
                  _buildImagePickerOption(
                    icon: Icons.photo_library,
                    label: localizations.gallery,
                    onTap: () => _pickImage(ImageSource.gallery),
                  ),
                  if (profileImageBase64 != null &&
                      profileImageBase64!.isNotEmpty)
                    _buildImagePickerOption(
                      icon: Icons.delete,
                      label: localizations.remove,
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
            child: Icon(icon, size: 30, color: color ?? Colors.black),
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

  Future<void> _pickImage(ImageSource source) async {
    final localizations = AppLocalizations.of(context)!;

    try {
      final XFile? image = await _picker.pickImage(
        source: source,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 85,
      );

      if (image != null) {
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => Center(
            child: Container(
              padding: EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: Color(0xFFEC7103)),
                  SizedBox(height: 16),
                  Text(localizations.processingImage),
                ],
              ),
            ),
          ),
        );

        await _saveProfileImageAsBase64(image.path);

        if (Navigator.canPop(context)) Navigator.pop(context);
        if (Navigator.canPop(context)) Navigator.pop(context);

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  Icon(Icons.check_circle, color: Colors.white, size: 20),
                  SizedBox(width: 8),
                  Text(localizations.profilePictureUpdated),
                ],
              ),
              backgroundColor: Colors.green,
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      }
    } catch (e) {
      while (Navigator.canPop(context)) {
        Navigator.pop(context);
      }

      _showError('${localizations.failedUpdateProfile}: ${e.toString()}');
    }
  }

  Future<void> _removeProfileImage() async {
    final localizations = AppLocalizations.of(context)!;

    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('profile_image_base64');

      setState(() {
        profileImageBase64 = null;
        profileImageBytes = null;
      });

      Navigator.pop(context);

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              Icon(Icons.check_circle, color: Colors.white, size: 20),
              SizedBox(width: 8),
              Text(localizations.profilePictureRemoved),
            ],
          ),
          backgroundColor: Colors.orange,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
          margin: EdgeInsets.all(16),
        ),
      );

      debugPrint("🗑️ Profile image removed successfully");
    } catch (e) {
      Navigator.pop(context);
      _showError('${localizations.failedUpdateProfile}: ${e.toString()}');
    }
  }

  Future<void> _loadUserData() async {
    final localizations = AppLocalizations.of(context)!;

    setState(() {
      isLoading = true;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      trdr = prefs.getString('TRDR') ?? '';
      clientID = prefs.getString('clientID') ?? '';
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');

      if (trdr.isEmpty ||
          clientID.isEmpty ||
          companyUrl == null ||
          softwareType == null) {
        _showError(localizations.missingUserCredentials);
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

          String name = userInfo['NAME']?.toString().isNotEmpty == true
              ? userInfo['NAME'].toString()
              : localizations.fullName;

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

          String userAddress =
              userInfo['ADDRESS']?.toString().isNotEmpty == true
              ? userInfo['ADDRESS'].toString()
              : localizations.address;

          String userCity = userInfo['CITY']?.toString().isNotEmpty == true
              ? userInfo['CITY'].toString()
              : localizations.city;

          String userZip = userInfo['ZIP']?.toString().isNotEmpty == true
              ? userInfo['ZIP'].toString()
              : localizations.zip;

          String profileImageUrl = userInfo['IMAGE']?.toString() ?? '';

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
          _showError(localizations.failedLoadUserData);
          if (mounted) {
            setState(() => isLoading = false);
          }
        }
      } else {
        _showError('${localizations.serverError}: ${response.statusCode}');
        if (mounted) {
          setState(() => isLoading = false);
        }
      }
    } catch (e) {
      _showError('${localizations.connectionError}: ${e.toString()}');
      if (mounted) {
        setState(() => isLoading = false);
      }
    }
  }

  String _getServicePath(String softwareType) {
    return softwareType == "TESAE"
        ? "/pegasus/a_xit/connector.php"
        : "/s1services";
  }

  Uri _buildApiUri(String companyUrl, String servicePath) {
    return Uri.parse("${ApiConstants.baseUrl}https://$companyUrl$servicePath");
  }

  Future<void> _updateUserData() async {
    final localizations = AppLocalizations.of(context)!;

    try {
      setState(() {
        isLoading = true;
      });

      final prefs = await SharedPreferences.getInstance();
      final companyUrl = prefs.getString('company_url');
      final softwareType = prefs.getString('software_type');
      final fcmToken = prefs.getString('fcm_token');

      if (companyUrl == null || softwareType == null) {
        _showError(localizations.missingConfiguration);
        return;
      }

      final servicePath = _getServicePath(softwareType);
      final uri = _buildApiUri(companyUrl, servicePath);

      Map<String, dynamic> userData = {
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
      };

      if (fcmToken != null && fcmToken.isNotEmpty) {
        userData["GLNCODE"] = fcmToken;
      }

      if (profileImageBase64 != null &&
          profileImageBase64!.isNotEmpty &&
          _validateBase64Image(profileImageBase64!)) {
        userData["CCCXITLIMAGE"] = profileImageBase64!;
        debugPrint("📤 Sending validated image with API call");
      } else if (profileImageBase64 != null && profileImageBase64!.isNotEmpty) {
        debugPrint("⚠️ Image exists but failed validation, not sending");
      }

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
            "CUSTOMER": [userData],
          },
        }),
      );

      debugPrint("🌐 API Response Status: ${response.statusCode}");
      debugPrint("📝 API Response Body: ${response.body}");

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);

        if (data['success'] == true) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  Icon(Icons.check_circle, color: Colors.white, size: 20),
                  SizedBox(width: 8),
                  Text(localizations.profileUpdatedSuccessfully),
                ],
              ),
              backgroundColor: Colors.green,
              behavior: SnackBarBehavior.floating,
            ),
          );
        } else {
          _showError(
            '${localizations.failedUpdateProfile}: ${data['message'] ?? localizations.unknownError}',
          );
        }
      } else {
        _showError('${localizations.serverError}: ${response.statusCode}');
      }
    } catch (e) {
      debugPrint("❌ Error in _updateUserData: $e");
      _showError('${localizations.connectionError}: ${e.toString()}');
    } finally {
      if (mounted) {
        setState(() {
          isLoading = false;
        });
      }
    }
  }

  bool _validateBase64Image(String base64String) {
    try {
      if (base64String.isEmpty || base64String.length < 100) {
        return false;
      }

      base64Decode(base64String);

      String header = base64String.substring(0, 20).toLowerCase();
      bool isValidImageType =
          header.contains('data:image/') ||
          base64String.startsWith('/9j/') ||
          base64String.startsWith('iVBOR') ||
          base64String.startsWith('R0lGO');

      return true;
    } catch (e) {
      debugPrint("❌ Invalid base64 image: $e");
      return false;
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
          AppLocalizations.of(context)!.rewards,
          Icons.card_giftcard,
        );
        break;
      case 2:
        _showComingSoon(
          AppLocalizations.of(context)!.qrScanner,
          Icons.qr_code_scanner,
        );
        break;
      case 3:
        _showComingSoon(
          AppLocalizations.of(context)!.notifications,
          Icons.notifications,
        );
        break;
      case 4:
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
              '${localizations.featureComingSoon} $feature!',
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
      text: initialValue == AppLocalizations.of(context)!.loading
          ? ""
          : initialValue,
    );

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
        title: Text(
          "${localizations.edit} $title",
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        content: TextField(
          controller: controller,
          decoration: InputDecoration(
            hintText: "${localizations.enter} $title",
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
            filled: true,
            fillColor: Colors.grey.shade50,
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              localizations.cancel,
              style: TextStyle(color: Colors.grey.shade600),
            ),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Color(0xFFEC7103),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
            onPressed: () {
              if (controller.text.trim().isNotEmpty) {
                onSave(controller.text.trim());
                Navigator.pop(context);
              }
            },
            child: Text(
              localizations.save,
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
        backgroundColor: Color(0xFFEC7103),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          localizations.account,
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
            tooltip: localizations.changeLanguage,
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
                    GestureDetector(
                      onTap: _showImagePickerOptions,
                      child: Stack(
                        children: [
                          Container(
                            padding: EdgeInsets.all(4),
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              gradient: LinearGradient(
                                colors: [
                                  Color.fromARGB(255, 212, 101, 3),
                                  Color.fromARGB(155, 255, 151, 61),
                                ],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                            ),
                            child: CircleAvatar(
                              radius: 50,
                              backgroundColor: Colors.white,
                              backgroundImage: profileImageBytes != null
                                  ? MemoryImage(profileImageBytes!)
                                  : null,
                              child: profileImageBytes == null
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
                                border: Border.all(
                                  color: Colors.white,
                                  width: 2,
                                ),
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

              Container(
                width: double.infinity,
                padding: EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      Color.fromARGB(255, 236, 88, 3),
                      Color.fromARGB(255, 255, 167, 89),
                    ],
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
                            localizations.pointsEarned,
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
                    Container(height: 40, width: 1, color: Colors.white30),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            localizations.pointsRedeemed,
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

              _buildInfoCard(localizations.personalDetails, [
                _buildDetailRow(
                  localizations.fullName,
                  fullName,
                  Icons.person_outline,
                  editable: true,
                  onTap: () {
                    showEditDialog(
                      localizations.fullName,
                      fullName,
                      (value) => setState(() => fullName = value),
                    );
                  },
                ),
                _buildDetailRow(
                  localizations.email,
                  email,
                  Icons.email_outlined,
                ),
                _buildDetailRow(
                  localizations.phone,
                  phone,
                  Icons.phone_outlined,
                  editable: true,
                  onTap: () {
                    showEditDialog(
                      localizations.phone,
                      phone,
                      (value) => setState(() => phone = value),
                    );
                  },
                ),
                _buildDetailRow(
                  localizations.address,
                  address,
                  Icons.location_on_outlined,
                  editable: true,
                  onTap: () {
                    showEditDialog(
                      localizations.address,
                      address,
                      (value) => setState(() => address = value),
                    );
                  },
                ),
                _buildDetailRow(
                  localizations.city,
                  city,
                  Icons.location_city_outlined,
                  editable: true,
                  onTap: () {
                    showEditDialog(
                      localizations.city,
                      city,
                      (value) => setState(() => city = value),
                    );
                  },
                ),
                _buildDetailRow(
                  localizations.zip,
                  zip,
                  Icons.markunread_mailbox_outlined,
                  editable: true,
                  onTap: () {
                    showEditDialog(
                      localizations.zip,
                      zip,
                      (value) => setState(() => zip = value),
                    );
                  },
                ),
                _buildDetailRow(
                  localizations.password,
                  password,
                  Icons.lock_outline,
                  editable: true,
                  onTap: () {
                    showEditDialog(
                      localizations.password,
                      '',
                      (value) => setState(() => password = value),
                    );
                  },
                ),
              ]),

              const SizedBox(height: 16),

              _buildInfoCard(localizations.accountDetails, [
                _buildDetailRow(
                  localizations.accountCreated,
                  createdAt,
                  Icons.calendar_today_outlined,
                ),
                _buildDetailRow("TRDR ID", trdr, Icons.badge_outlined),
              ]),

              const SizedBox(height: 30),

              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Color(0xFFEC7103),
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
                            Icon(
                              Icons.save_outlined,
                              color: Colors.white,
                              size: 20,
                            ),
                            SizedBox(width: 8),
                            Text(
                              localizations.updateProfile,
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
                    final localizations = AppLocalizations.of(context)!;

                    bool shouldLogout =
                        await showDialog(
                          context: context,
                          builder: (context) => AlertDialog(
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(15),
                            ),
                            title: Text(localizations.confirmLogout),
                            content: Text(localizations.logoutConfirmation),
                            actions: [
                              TextButton(
                                onPressed: () => Navigator.pop(context, false),
                                child: Text(
                                  localizations.cancel,
                                  style: TextStyle(color: Colors.grey),
                                ),
                              ),
                              ElevatedButton(
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.red,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                ),
                                onPressed: () => Navigator.pop(context, true),
                                child: Text(
                                  localizations.logout,
                                  style: TextStyle(color: Colors.white),
                                ),
                              ),
                            ],
                          ),
                        ) ??
                        false;

                    if (shouldLogout) {
                      final prefs = await SharedPreferences.getInstance();
                      await prefs.clear();

                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Row(
                            children: [
                              Icon(
                                Icons.check_circle,
                                color: Colors.white,
                                size: 20,
                              ),
                              SizedBox(width: 8),
                              Text(localizations.loggedOutSuccessfully),
                            ],
                          ),
                          backgroundColor: Colors.green,
                          behavior: SnackBarBehavior.floating,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
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
                        localizations.logout,
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
            child: Icon(icon, size: 20, color: Colors.grey.shade600),
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
                  color: Color(0xFFEC7103),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
