import 'package:flutter/material.dart';
import 'package:loyalty_app/Auth/SignIn.dart';
import 'package:loyalty_app/screen/MainScreen.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AuthChecker extends StatefulWidget {
  const AuthChecker({super.key});

  @override
  State<AuthChecker> createState() => _AuthCheckerState();
}

class _AuthCheckerState extends State<AuthChecker> {
  String? phone;

  @override
  void initState() {
    super.initState();
    checkLoginStatus();
  }

  Future<void> checkLoginStatus() async {
    final prefs = await SharedPreferences.getInstance();
    final userPhone = prefs.getString('user_phone');

    setState(() {
      phone = userPhone;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (phone == null) {
      // Loading jab tak check ho raha hai
            return const SignInScreen(); // ❌ User is not logged in

      // return const Scaffold(
      //   body: Center(child: CircularProgressIndicator()),
      // );
    } else {
      return const MainScreen(); // ✅ User is logged in
    } 
    
  }
}
