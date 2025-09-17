import {
  Manager,
  FIREBASE_API_KEY,
  FIREBASE_AUTH_DOMAIN,
  FIREBASE_PROJECT_ID,
  FIREBASE_STORAGE_BUCKET,
  FIREBASE_SENDER_ID,
  FIREBASE_APP_ID
} from '@metafox/framework';
import { initializeApp } from 'firebase/app';
import { getFirestore } from 'firebase/firestore';
import {
  getAuth,
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword
} from 'firebase/auth';
import { getMessaging } from 'firebase/messaging/sw';

export default class FirebaseBackend {
  private firestore;
  private config;
  private mounted;
  private settingApp;
  private cloudMessage;
  private firebaseApp;
  private isActive: boolean;
  /**
   * See manager pattern
   */
  private manager: Manager;
  public bootstrap(manager, onSuccess) {
    this.manager = manager;
    this.settingApp = this.manager.getSetting('firebase');

    if (this.settingApp) {
      this.init(this.settingApp, onSuccess);
    }
  }

  public checkActive() {
    return this.isActive;
  }
  public init(config, onSuccess) {
    // const { dispatch } = this.manager;
    if (this.mounted) return this.firebaseApp;

    this.mounted = true;

    const firebaseConfig = {
      apiKey: FIREBASE_API_KEY,
      authDomain: FIREBASE_AUTH_DOMAIN,
      projectId: FIREBASE_PROJECT_ID,
      storageBucket: FIREBASE_STORAGE_BUCKET,
      messagingSenderId: FIREBASE_SENDER_ID,
      appId: FIREBASE_APP_ID
    };

    this.config = config;
    // Initialize Firebase
    try {
      const app = initializeApp(firebaseConfig);
      const auth = getAuth(app);
      const { user_firebase_email, user_firebase_password, requiredSignin } =
        this.settingApp || {};

      if (requiredSignin) {
        signInWithEmailAndPassword(
          auth,
          user_firebase_email,
          user_firebase_password
        )
          .then(data => {})
          .catch(error => {
            console.log(
              'Live Video - Firebase signInWithEmailAndPassword error',
              error
            );

            if (error.code === 'auth/user-not-found') {
              createUserWithEmailAndPassword(
                auth,
                user_firebase_email,
                user_firebase_password
              );
            }
          });
      }

      onSuccess(true);
      this.firebaseApp = app;
      this.isActive = true;

      return app;
    } catch (error) {
      this.isActive = false;
    }
  }
  public getFirebaseApp() {
    return this.firebaseApp;
  }
  public getFirestore() {
    if (!this.firebaseApp) return null;

    if (!this.firestore) {
      const db = getFirestore(this.firebaseApp);

      if (db) {
        this.firestore = db;
      }
    }

    return this.firestore;
  }

  public getMessaging() {
    if (!this.firebaseApp) return null;

    if (!this.cloudMessage) {
      const message = getMessaging(this.firebaseApp);

      if (message) {
        this.cloudMessage = message;
      }
    }

    return this.cloudMessage;
  }
}
