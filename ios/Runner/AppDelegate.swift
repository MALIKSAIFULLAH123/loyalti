import UIKit
import Flutter

@main
@objc class AppDelegate: FlutterAppDelegate {

  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {

    // ✅ Flutter plugins register karo
    // Firebase will be auto-initialized by firebase_core plugin
    GeneratedPluginRegistrant.register(with: self)

    // ✅ Call super (CRITICAL for Flutter lifecycle)
    let result = super.application(application, didFinishLaunchingWithOptions: launchOptions)

    // ✅ Ensure window is ready for Firebase Auth reCAPTCHA
    if let window = self.window {
      if !window.isKeyWindow {
        window.makeKeyAndVisible()
      }
    }

    return result
  }

  // ✅ URL handling for Firebase Phone Auth / reCAPTCHA / Google Sign-In
  override func application(
    _ app: UIApplication,
    open url: URL,
    options: [UIApplication.OpenURLOptionsKey : Any] = [:]
  ) -> Bool {
    return super.application(app, open: url, options: options)
  }
}
