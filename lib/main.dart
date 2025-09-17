import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:loyalty_app/Auth/auth_chacker.dart';
import 'package:loyalty_app/Services/language_service.dart';
import 'package:provider/provider.dart';
import 'firebase_options.dart';
// WebView imports
import 'package:webview_flutter/webview_flutter.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';
import 'package:webview_flutter_wkwebview/webview_flutter_wkwebview.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Firebase
  await Firebase.initializeApp(options: DefaultFirebaseOptions.currentPlatform);

  // Initialize WebView platform
  WebViewPlatform.instance ??= AndroidWebViewPlatform();

  final localizationService = LocalizationService();
  await localizationService.initialize();

  runApp(MyApp(localizationService: localizationService));
}

class MyApp extends StatelessWidget {
  final LocalizationService localizationService;

  const MyApp({super.key, required this.localizationService});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider<LocalizationService>(
      create: (context) => localizationService,
      child: Consumer<LocalizationService>(
        builder: (context, localizationService, child) {
          return MaterialApp(
            title: 'Angelopoulos Rewards',
            locale: localizationService.currentLocale,
            localizationsDelegates: const [
              AppLocalizations.delegate,
              GlobalMaterialLocalizations.delegate,
              GlobalWidgetsLocalizations.delegate,
              GlobalCupertinoLocalizations.delegate,
            ],
            supportedLocales: LocalizationService.supportedLocales,
            home: const AuthChecker(),
            theme: ThemeData(
              fontFamily: 'NotoSans', // 👈 global font
            ),
            debugShowCheckedModeBanner: false,
          );
        },
      ),
    );
  }
}
