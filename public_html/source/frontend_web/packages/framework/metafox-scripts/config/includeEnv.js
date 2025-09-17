const includeEnv = {};

const isMultiTarget = process.env.MFOX_BUILD_PROFILE === 'multi-target';

// do not override in multi target
const skips = ['MFOX_BUILD_TYPE', 'MFOX_BUILD_TYPE', 'MFOX_BUILD'];

/**
 * PUT DEFAULT CONST TO PREVENT missing `process` from webpack@v5+
 *
 * @type {{MFOX_LOADING_BG: string, PUBLIC_URL: string, NODE_ENV: string, MFOX_BUILD_TYPE: string}}
 */
const defaults = {
  NODE_ENV: 'development',
  ASSET_PATH: '',
  PUBLIC_URL: '',
  MFOX_API_URL: '',
  MFOX_SITE_URL: '',
  MFOX_ADMINCP_URL: '/admincp',
  MFOX_BUILD_TYPE: 'web',
  MFOX_LOADING_BG: '#2d2d2d',
  MFOX_SITE_NAME: 'MetaFox',
  MFOX_SITE_DESCRIPTION: '',
  MFOX_SITE_KEYWORDS: '',
  MFOX_SITE_TITLE: '',
  MFOX_END_HEAD_HTML: '',
  MFOX_END_BODY_HTML: '',
  MFOX_START_BODY_HTML: '',
  MFOX_INIT_BODY_HTML: '',
  MFOX_SKIP_FRONT_DEV: false,
  MFOX_FAVICON_URL: '',
  MFOX_MASK_ICON_URL: '',
  MFOX_APPLE_TOUCH_ICON_URL: '',
  MFOX_LOCALE: 'en',
  MFOX_LOCALE_SUPPORTS: 'en,vi',
  MFOX_COOKIE_PREFIX: 'yA0JuFD6n6zkC1',
  MFOX_ROUTE_BASE_NAME: '',
  MFOX_SITE_THEME: 'a0:a0',
  MFOX_ACTIVE_THEMES: '',
  MFOX_ADMINCP_THEME: 'admincp:admincp',
  MFOX_CORE_GOOGLE_GOOGLE_MAP_API_KEY: '',
  MFOX_CAPTCHA_RECAPTCHA_V3_SITE_KEY: '',
  MFOX_BROADCAST_CONNECTIONS_PUSHER_DRIVER: '',
  MFOX_BROADCAST_CONNECTIONS_PUSHER_KEY: '',
  MFOX_BROADCAST_CONNECTIONS_PUSHER_OPTIONS_CLUSTER: '',
  MFOX_FIREBASE_SENDER_ID: '',
  MFOX_FIREBASE_API_KEY: '',
  MFOX_FIREBASE_AUTH_DOMAIN: '',
  MFOX_FIREBASE_PROJECT_ID: '',
  MFOX_FIREBASE_STORAGE_BUCKET: '',
  MFOX_FIREBASE_APP_ID: '',
  MFOX_MOBILE_GOOGLE_APP_ID: '',
  MFOX_MOBILE_APPLE_APP_ID: '',
  MFOX_BUILD_AT: new Date().toUTCString(), // help for debug frontend on client site.
  MFOX_PWA_DISPLAY: '',
  MFOX_PWA_INSTALL_DESCRIPTION: '',
  MFOX_PWA_APP_NAME: '',
  MFOX_PWA_APP_DESCRIPTION: '',
  MFOX_SITE_THEME_TYPE: 'auto'
};

if (!process.env.PUBLIC_URL) {
  process.env.PUBLIC_URL = '';
}

if (!process.env.ASSET_PATH) {
  process.env.ASSET_PATH = process.env.PUBLIC_URL;
}

if (isMultiTarget) {
  // override process.env
  Object.keys(process.env)
    .filter(name => /^MFOX_/.test(name))
    .filter(name => !skips.includes(name))
    .forEach(name => (process.env[name] = `___${name}___`));
}

Object.keys(process.env)
  .filter(name => /MFOX_/.test(name))
  .forEach(name => (includeEnv[name] = process.env[name]));

const exampleEnv = Object.assign({}, defaults, includeEnv, {
  NODE_ENV: process.env.NODE_ENV || 'development',
  ASSET_PATH: process.env.ASSET_PATH,
  PUBLIC_URL: process.env.PUBLIC_URL || '',
  MFOX_BUILD_TYPE: process.env.MFOX_BUILD_TYPE,
  MFOX_API_URL: process.env.MFOX_API_URL || '/api/v1',
  MFOX_BUILD: true,
  MFOX_BUILT_AT: new Date().toISOString()
});

module.exports = exampleEnv;
