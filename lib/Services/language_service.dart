// 1. localization_service.dart - Main localization service
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class LocalizationService extends ChangeNotifier {
  static const String _languageKey = 'selected_language';
  Locale _currentLocale = const Locale('el', 'GR'); // Default Greek

  Locale get currentLocale => _currentLocale;

  // Supported locales
  static const List<Locale> supportedLocales = [
    Locale('el', 'GR'), // Greek
    Locale('en', 'US'), // English
    Locale('ro', 'RO'), // Romanian
  ];

  // Initialize and load saved language
  Future<void> initialize() async {
    final prefs = await SharedPreferences.getInstance();
    final savedLanguageCode = prefs.getString(_languageKey) ?? 'el';

    _currentLocale = supportedLocales.firstWhere(
      (locale) => locale.languageCode == savedLanguageCode,
      orElse: () => const Locale('el', 'GR'),
    );

    notifyListeners();
  }

  // Change language
  Future<void> changeLanguage(String languageCode) async {
    final newLocale = supportedLocales.firstWhere(
      (locale) => locale.languageCode == languageCode,
      orElse: () => const Locale('el', 'GR'),
    );

    if (newLocale != _currentLocale) {
      _currentLocale = newLocale;

      // Save to SharedPreferences
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_languageKey, languageCode);

      notifyListeners();
    }
  }
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  bool isSupported(Locale locale) {
    return ['el', 'en', 'ro'].contains(locale.languageCode);
  }

  @override
  Future<AppLocalizations> load(Locale locale) async {
    return AppLocalizations(locale);
  }

  @override
  bool shouldReload(covariant LocalizationsDelegate<AppLocalizations> old) {
    return false;
  }
}

// 2. app_localizations.dart - Translations class
class AppLocalizations {
  final Locale locale;

  AppLocalizations(this.locale);

  static AppLocalizations? of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations);
  }

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  // All translations
  static final Map<String, Map<String, String>> _localizedStrings = {
    'el': {
      // Login Screen
      'app_title': 'ANGELOPOULOS',
      'app_subtitle': 'REWARDS',
      'sign_in': 'ΣΥΝΔΕΣΗ',
      'sign_in_subtitle': 'Εισάγετε το email και τον κωδικό σας για σύνδεση!',
      'email': 'Email:',
      'password': 'Κωδικός:',
      'email_hint': 'email@domain.com',
      'password_hint': 'Κωδικός',
      'sign_in_button': 'Σύνδεση',
      'sign_up_button': 'Σύνδεση',
      'forgetpassword_button': 'Ξεχάσατε τον κωδικό',
      'full_name': 'Ονοματεπώνυμο', // for 'el'
      'phone': 'Τηλέφωνο', // for 'el'
      'no_account': 'Δεν έχω λογαριασμό ακόμα, ',
      'sign_up': 'Εγγραφή',
      'change_language': 'Αλλαγή Γλώσσας',
      'developed_by': 'Developed by XIT',
      // profile screen
      'account': 'Λογαριασμός',
      'personal_details': 'Προσωπικά Στοιχεία',
      'account_details': 'Στοιχεία Λογαριασμού',
      'full_name': 'Ονοματεπώνυμο',
      'email': 'Email',
      'phone': 'Τηλέφωνο',
      'password': 'Κωδικός',
      'account_created': 'Δημιουργία Λογαριασμού',
      'points_earned': 'Συγκεντρωμένοι Πόντοι',
      'points_redeemed': 'Εξαργυρωμένοι Πόντοι',
      'edit': 'Επεξεργασία',
      'enter': 'Εισαγωγή',
      'save': 'Αποθήκευση',
      'cancel': 'Ακύρωση',
      'feature_coming_soon': 'Η λειτουργία',
      'qr_scanner': 'Σαρωτής QR',
      // notification screen
      'notifications': 'Ειδοποιήσεις',
      'newsletter_title': 'Ενημερωτικό Δελτίο',
      'new_rewards_added': 'Νέες Ανταμοιβές Προστέθηκαν!',
      'successful_redemption': 'Επιτυχής Εξαργύρωση',
      'redeemed_20_points':
          'Εξαργυρώσατε 20 πόντους από\nτην τελευταία σας απόδειξη!',
      'redeemed_100_points':
          'Εξαργυρώσατε 100 πόντους από\nτην τελευταία σας απόδειξη!',
      'redeemed_75_points':
          'Εξαργυρώσατε 75 πόντους από\nτην τελευταία σας απόδειξη!',
      'redeemed_50_points':
          'Εξαργυρώσατε 50 πόντους από\nτην τελευταία σας απόδειξη!',

      // Language Selection
      'language_selection': 'Επιλογή Γλώσσας',
      'choose_language': 'Επιλέξτε γλώσσα',
      'confirm': 'Επιβεβαίωση',
      'back': 'Πίσω',
      'language_changed': 'Η γλώσσα άλλαξε επιτυχώς',

      // Home Screen
      'my_balance': 'Το Υπόλοιπό Μου',
      'points': 'ΠΌΝΤΟΙ',
      'good_morning': 'Καλημέρα!',
      'good_afternoon': 'Καλησπέρα!',
      'good_evening': 'Καλησπέρα!',
      'glow_more_earn_more': 'Λάμψε Περισσότερο, Κέρδισε Περισσότερο!',
      'daily_beauty_rewards': 'Καθημερινές Ανταμοιβές Ομορφιάς',
      'discover_beauty_services': 'Ανακαλύψτε Υπηρεσίες Ομορφιάς',
      'image_not_found': 'Η εικόνα δεν βρέθηκε',

      // Language Names
      'greek': 'Ελληνικά',
      'english': 'English',
      'romanian': 'Română',
    },
    'en': {
      // Login Screen
      'app_title': 'ANGELOPOULOS',
      'app_subtitle': 'REWARDS',
      'sign_in': 'SIGN IN',
      'sign_in_subtitle': 'Enter your email and password to sign in!',
      'email': 'Email:',
      'password': 'Password:',
      'email_hint': 'email@domain.com',
      'password_hint': 'Password',
      'sign_in_button': 'Sign In',
      'sign_up_button': 'Sign Up',
      'forgetpassword_button': 'Forget Password',
      'no_account': 'I don\'t have an account yet, ',
      'sign_up': 'Sign me up',
      'change_language': 'Change Language',
      'developed_by': 'Developed by XIT',
      // profile screen
      'account': 'Account',
      'personal_details': 'Personal Details',
      'account_details': 'Account Details',
      'full_name': 'Full Name',
      'email': 'Email',
      'phone': 'Phone',
      'password': 'Password',
      'account_created': 'Account Created',
      'points_earned': 'Points Earned',
      'points_redeemed': 'Points Redeemed',
      'edit': 'Edit',
      'enter': 'Enter',
      'save': 'Save',
      'cancel': 'Cancel',
      'feature_coming_soon': 'Feature',
      'qr_scanner': 'QR Scanner',
      // notification screen
      'notifications': 'Notifications',
      'newsletter_title': 'Newsletter',
      'new_rewards_added': 'New Rewards Have Been Added!',
      'successful_redemption': 'Successful Redemption',
      'redeemed_20_points': 'You redeemed 20 points from\nyour last receipt!',
      'redeemed_100_points': 'You redeemed 100 points from\nyour last receipt!',
      'redeemed_75_points': 'You redeemed 75 points from\nyour last receipt!',
      'redeemed_50_points': 'You redeemed 50 points from\nyour last receipt!',
      // Language Selection
      'language_selection': 'Language Selection',
      'choose_language': 'Select language',
      'confirm': 'Confirm',
      'back': 'Back',
      'language_changed': 'Language changed successfully',

      // Home Screen
      'my_balance': 'My Balance',
      'points': 'POINTS',
      'good_morning': 'Good Morning!',
      'good_afternoon': 'Good Afternoon!',
      'good_evening': 'Good Evening!',
      'glow_more_earn_more': 'Glow More, Earn More!',
      'daily_beauty_rewards': 'Daily Beauty Rewards',
      'discover_beauty_services': 'Discover Beauty Services',
      'image_not_found': 'Image not found',

      // Language Names
      'greek': 'Ελληνικά',
      'english': 'English',
      'romanian': 'Română',
    },
    'ro': {
      // Login Screen
      'app_title': 'ANGELOPOULOS',
      'app_subtitle': 'REWARDS',
      'sign_in': 'CONECTARE',
      'sign_in_subtitle': 'Introduceți email-ul și parola pentru a vă conecta!',
      'email': 'Email:',
      'password': 'Parolă:',
      'email_hint': 'email@domain.com',
      'password_hint': 'Parolă',
      'sign_in_button': 'Conectare',
      'sign_up_button': 'Conectare',
      'forgetpassword_button': 'Ai uitat parola',

      'no_account': 'Nu am încă un cont, ',
      'sign_up': 'Înregistrează-mă',
      'change_language': 'Schimbă Limba',
      'developed_by': 'Developed by XIT',
      // profile screen
      'account': 'Cont',
      'personal_details': 'Detalii Personale',
      'account_details': 'Detalii Cont',
      'full_name': 'Nume Complet',
      'email': 'Email',
      'phone': 'Telefon',
      'password': 'Parolă',
      'account_created': 'Cont Creat',
      'points_earned': 'Puncte Câștigate',
      'points_redeemed': 'Puncte Răscumpărate',
      'edit': 'Editează',
      'enter': 'Introdu',
      'save': 'Salvează',
      'cancel': 'Anulează',
      'feature_coming_soon': 'Funcționalitatea',
      'qr_scanner': 'Scaner QR',
      // notification screen
      'notifications': 'Notificări',
      'newsletter_title': 'Buletin informativ',
      'new_rewards_added': 'Au fost adăugate noi recompense!',
      'successful_redemption': 'Răscumpărare reușită',
      'redeemed_20_points':
          'Ați răscumpărat 20 de puncte din\nultima dvs. chitanță!',
      'redeemed_100_points':
          'Ați răscumpărat 100 de puncte din\nultima dvs. chitanță!',
      'redeemed_75_points':
          'Ați răscumpărat 75 de puncte din\nultima dvs. chitanță!',
      'redeemed_50_points':
          'Ați răscumpărat 50 de puncte din\nultima dvs. chitanță!',

      // Language Selection
      'language_selection': 'Selectare Limbă',
      'choose_language': 'Selectați limba',
      'confirm': 'Confirmă',
      'back': 'Înapoi',
      'language_changed': 'Limba a fost schimbată cu succes',

      // Home Screen
      'my_balance': 'Soldul Meu',
      'points': 'PUNCTE',
      'good_morning': 'Bună dimineața!',
      'good_afternoon': 'Bună ziua!',
      'good_evening': 'Bună seara!',
      'glow_more_earn_more': 'Strălucește Mai Mult, Câștigă Mai Mult!',
      'daily_beauty_rewards': 'Recompense de Frumusețe Zilnice',
      'discover_beauty_services': 'Descoperă Serviciile de Frumusețe',
      'image_not_found': 'Imaginea nu a fost găsită',

      // Language Names
      'greek': 'Ελληνικά',
      'english': 'English',
      'romanian': 'Română',
    },
  };

  // Get translation method
  String translate(String key) {
    return _localizedStrings[locale.languageCode]?[key] ??
        _localizedStrings['el']![key] ??
        key;
  }

  // Convenience getters for common strings
  String get appTitle => translate('app_title');
  String get appSubtitle => translate('app_subtitle');
  String get signIn => translate('sign_in');
  String get signInSubtitle => translate('sign_in_subtitle');
  String get email => translate('email');
  String get password => translate('password');
  String get emailHint => translate('email_hint');
  String get passwordHint => translate('password_hint');
  String get signInButton => translate('sign_in_button');
  String get signupButton => translate('sign_up_button');
  String get forgetpassword => translate('forgetpassword_button');
  String get noAccount => translate('no_account');
  String get signUp => translate('sign_up');
  String get changeLanguage => translate('change_language');
  String get developedBy => translate('developed_by');
  String get languageSelection => translate('language_selection');
  String get chooseLanguage => translate('choose_language');
  String get confirm => translate('confirm');
  String get back => translate('back');
  String get languageChanged => translate('language_changed');
  String get greek => translate('greek');
  String get english => translate('english');
  String get romanian => translate('romanian');

  // Home Screen getters
  String get myBalance => translate('my_balance');
  String get Name => translate('Name');
  String get phone => translate('phone');

  String get points => translate('points');
  String get goodMorning => translate('good_morning');
  String get goodAfternoon => translate('good_afternoon');
  String get goodEvening => translate('good_evening');
  String get glowMoreEarnMore => translate('glow_more_earn_more');
  String get dailyBeautyRewards => translate('daily_beauty_rewards');
  String get discoverBeautyServices => translate('discover_beauty_services');
  String get imageNotFound => translate('image_not_found');
  String get updateprofile => translate('image_not_found');

  // Dynamic greeting based on time
  String get greeting {
    final hour = DateTime.now().hour;
    if (hour < 12) {
      return goodMorning;
    } else if (hour < 17) {
      return goodAfternoon;
    } else {
      return goodEvening;
    }
  }
}




// // Add these to your _localizedStrings maps in AppLocalizations class
// static final Map<String, Map<String, String>> _localizedStrings = {
//   'el': {
//     // ... existing keys ...
//     'rewards': 'Ανταμοιβές',
//     'points': 'ΠΟΝΤΟΙ',
//     'lip_serum_1': 'B.Fresh Kiss My Sass Nourishing Lip Serum 15ml',
//     'lip_serum_2': 'B.Fresh Butter Than Ever Conditioning Lip Serum 15ml',
//     'gloss_1': 'MKM Gloss For Pavlova 16ml',
//     'gloss_2': 'MKM Gloss For All CHILI PEPPER 16ml',
//     'face_tool': 'Face Lifting Tool',
//     'led_mask': 'LED Face Mask',
//   },
//   'en': {
//     // ... existing keys ...
//     'rewards': 'Rewards',
//     'points': 'POINTS',
//     'lip_serum_1': 'B.Fresh Kiss My Sass Nourishing Lip Serum 15ml',
//     'lip_serum_2': 'B.Fresh Butter Than Ever Conditioning Lip Serum 15ml',
//     'gloss_1': 'MKM Gloss For Pavlova 16ml',
//     'gloss_2': 'MKM Gloss For All CHILI PEPPER 16ml',
//     'face_tool': 'Face Lifting Tool',
//     'led_mask': 'LED Face Mask',
//   },
//   'ro': {
//     // ... existing keys ...
//     'rewards': 'Recompense',
//     'points': 'PUNCTE',
//     'lip_serum_1': 'B.Fresh Kiss My Sass Nourishing Lip Serum 15ml',
//     'lip_serum_2': 'B.Fresh Butter Than Ever Conditioning Lip Serum 15ml',
//     'gloss_1': 'MKM Gloss For Pavlova 16ml',
//     'gloss_2': 'MKM Gloss For All CHILI PEPPER 16ml',
//     'face_tool': 'Unealtă pentru Ridicare a Feței',
//     'led_mask': 'Mască LED pentru Față',
//   },
// };