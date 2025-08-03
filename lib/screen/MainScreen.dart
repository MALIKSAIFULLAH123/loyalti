import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:loyalty_app/Auth/CustomBottomNav.dart';
import 'package:loyalty_app/Auth/scan_receipt_screen.dart';
import 'package:loyalty_app/Controller/NavController.dart';
import 'package:loyalty_app/screen/home_screen.dart';
import 'package:loyalty_app/screen/notifications_screen.dart';
import 'package:loyalty_app/screen/rewards_screen.dart';
import 'package:loyalty_app/Auth/Profile.dart';

// class MainScreen extends StatelessWidget {
//   const MainScreen({super.key});

//   @override
//   Widget build(BuildContext context) {
//     final NavController navController = Get.put(NavController());

//     return Obx(() => Scaffold(
//           body: IndexedStack(
//             index: navController.selectedIndex.value,
//             children: [
//               HomeScreen(
//                 onNavItemTapped: (index) => navController.changePage(index),
//                 currentIndex: navController.selectedIndex.value,
//               ),
//               const RewardsScreen(),
//               ScanReceiptScreen(), // Placeholder for QR screen
//               const NotificationsScreen(),
//               const Profile(),
//             ],
//           ),
//           bottomNavigationBar: CustomBottomNav(
//             currentIndex: navController.selectedIndex.value,
//             onTap: (index) => navController.changePage(index),
//           ),
//         ));
//   }
// }


// import 'package:flutter/material.dart';
// import 'package:get/get.dart';
// import 'package:loyalty_app/Auth/CustomBottomNav.dart';
// import 'package:loyalty_app/Auth/scan_receipt_screen.dart';
// import 'package:loyalty_app/Controller/NavController.dart';
// import 'package:loyalty_app/screen/home_screen.dart';
// import 'package:loyalty_app/screen/notifications_screen.dart';
// import 'package:loyalty_app/screen/rewards_screen.dart';
// import 'package:loyalty_app/Auth/Profile.dart';

// // SOLUTION 1: IndexedStack with Proper Preloading
// // class MainScreen extends StatefulWidget {
// //   const MainScreen({super.key});

// //   @override
// //   State<MainScreen> createState() => _MainScreenState();
// // }

// // class _MainScreenState extends State<MainScreen> {
// //   final NavController navController = Get.put(NavController());
  
// //   // Track which screens have been initialized
// //   final Set<int> _initializedScreens = {};
// //   bool _allScreensPreloaded = false;
  
// //   // Create screens lazily
// //   Widget? _homeScreen;
// //   Widget? _rewardsScreen;
// //   Widget? _scanScreen;
// //   Widget? _notificationsScreen;
// //   Widget? _profileScreen;
  
// //   @override
// //   void initState() {
// //     super.initState();
    
// //     // Preload all screens after first frame
// //     WidgetsBinding.instance.addPostFrameCallback((_) {
// //       _preloadAllScreens();
// //     });
// //   }
  
// //   void _preloadAllScreens() {
// //     if (_allScreensPreloaded) return;
    
// //     setState(() {
// //       // Initialize all screens
// //       _homeScreen = HomeScreen(
// //         onNavItemTapped: (index) => navController.changePage(index),
// //         currentIndex: navController.selectedIndex.value,
// //       );
// //       _rewardsScreen = const RewardsScreen();
// //       _scanScreen = ScanReceiptScreen();
// //       _notificationsScreen = const NotificationsScreen();
// //       _profileScreen = const Profile();
      
// //       _allScreensPreloaded = true;
// //     });
    
// //     debugPrint("🔄 All screens preloaded and initialized");
// //   }
  
// //   Widget _getScreen(int index) {
// //     switch (index) {
// //       case 0:
// //         return _homeScreen ?? HomeScreen(
// //           onNavItemTapped: (index) => navController.changePage(index),
// //           currentIndex: navController.selectedIndex.value,
// //         );
// //       case 1:
// //         return _rewardsScreen ?? const RewardsScreen();
// //       case 2:
// //         return _scanScreen ?? ScanReceiptScreen();
// //       case 3:
// //         return _notificationsScreen ?? const NotificationsScreen();
// //       case 4:
// //         return _profileScreen ?? const Profile();
// //       default:
// //         return Container();
// //     }
// //   }

// //   @override
// //   Widget build(BuildContext context) {
// //     return Scaffold(
// //       body: Obx(() => IndexedStack(
// //         index: navController.selectedIndex.value,
// //         children: [
// //           _getScreen(0),
// //           _getScreen(1),
// //           _getScreen(2),
// //           _getScreen(3),
// //           _getScreen(4),
// //         ],
// //       )),
// //       bottomNavigationBar: Obx(() => CustomBottomNav(
// //         currentIndex: navController.selectedIndex.value,
// //         onTap: (index) => navController.changePage(index),
// //       )),
// //     );
// //   }
// // }

// // // SOLUTION 2: Using PageView (Recommended)


class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  final NavController navController = Get.put(NavController());
  final PageController pageController = PageController();

  @override
  void initState() {
    super.initState();

    // Listen to navController changes and sync with PageView
    navController.selectedIndex.listen((index) {
      if (pageController.hasClients &&
          pageController.page?.round() != index) {
        pageController.animateToPage(
          index,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeInOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Obx(() => PageView(
            controller: pageController,
            onPageChanged: (index) {
              navController.changePage(index);
            },
            children: [
              HomeScreen(
                onNavItemTapped: (index) =>
                    navController.changePage(index),
                currentIndex: navController.selectedIndex.value,
              ),
              const RewardsScreen(),
              ScanReceiptScreen(),
              const NotificationsScreen(),
              const Profile(),
            ],
          )),
      bottomNavigationBar: Obx(() => CustomBottomNav(
            currentIndex: navController.selectedIndex.value,
            onTap: (index) {
              if (index != navController.selectedIndex.value) {
                navController.changePage(index);
              }
            },
          )),
    );
  }

  @override
  void dispose() {
    pageController.dispose();
    super.dispose();
  }
}


// // // SOLUTION 3: Force All Screens to Initialize (Alternative)
// class MainScreen extends StatefulWidget {
//   const MainScreen({super.key});

//   @override
//   State<MainScreen> createState() => _MainScreenState();
// }

// class _MainScreenState extends State<MainScreen> {
//   final NavController navController = Get.put(NavController());
//   late final List<Widget> screens;
  
//   @override
//   void initState() {
//     super.initState();
    
//     // Initialize all screens immediately
//     screens = [
//       HomeScreen(
//         onNavItemTapped: (index) => navController.changePage(index),
//         currentIndex: navController.selectedIndex.value,
//       ),
//       const RewardsScreen(),
//       ScanReceiptScreen(),
//       const NotificationsScreen(),
//       const Profile(),
//     ];
//   }

//   @override
//   Widget build(BuildContext context) {
//     return Scaffold(
//       body: Stack(
//         children: [
//           // Hidden screens to force initialization
//           ...screens.asMap().entries.map((entry) {
//             final index = entry.key;
//             final screen = entry.value;
            
//             return Obx(() => Offstage(
//               offstage: navController.selectedIndex.value != index,
//               child: screen,
//             ));
//           }).toList(),
//         ],
//       ),
//       bottomNavigationBar: Obx(() => CustomBottomNav(
//         currentIndex: navController.selectedIndex.value,
//         onTap: (index) => navController.changePage(index),
//       )),
//     );
//   }
// }