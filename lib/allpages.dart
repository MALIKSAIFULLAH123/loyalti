import 'package:flutter/material.dart';

import 'package:loyalty_app/Auth/profile.dart';
import 'package:loyalty_app/Auth/SignIn.dart';
import 'package:loyalty_app/Auth/scan_receipt_screen.dart';
import 'package:loyalty_app/Auth/signup2.dart';
import 'package:loyalty_app/screen/MainScreen.dart';
import 'package:loyalty_app/screen/notifications_screen.dart';
import 'package:loyalty_app/screen/rewards_screen.dart';

class Allpages extends StatelessWidget {
  const Allpages({super.key});
  // var emailcontroller;

  @override
  Widget build(BuildContext context) {
    final List<Map<String, dynamic>> screens = [
      {'name': 'login', 'screen': SignInScreen()},
      // {'name': 'sign up', 'screen': SignUpScreen()},
      {'name': 'sign up2', 'screen': SignUpScreen2()},
      {'name': 'scaner ', 'screen': const ScanReceiptScreen()},
      // {'name': 'home  ', 'screen': const HomeScreen()},
      // {'name': 'scaner2 ', 'screen': QRViewExample()},
      {'name': ' profile ', 'screen': Profile()},
      {'name': ' notification screen ', 'screen': NotificationsScreen()},
      {'name': ' reward screen ', 'screen': RewardsScreen()},
      // {'name': ' home screen ', 'screen': HomeScreen()},
      {'name': ' Main screen ', 'screen': MainScreen()},
    ];    

    return Scaffold(
      appBar: AppBar(
        title: const Text("Navigation Screen"),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
      ),
      backgroundColor: Colors.black,
      body: ListView.builder(
        itemCount: screens.length,
        itemBuilder: (context, index) {
          return Card(
            color: Colors.grey[900],
            margin: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            child: ListTile(
              title: Text(
                screens[index]['name'],
                style: const TextStyle(color: Colors.white),
              ),
              trailing: const Icon(
                Icons.arrow_forward_ios,
                color: Colors.white,
              ),
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => screens[index]['screen'],
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
