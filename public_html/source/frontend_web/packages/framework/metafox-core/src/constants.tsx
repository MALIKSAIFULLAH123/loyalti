export const FORM_SUBMIT = '@form/SUBMIT';

export const FORM_UPLOAD_FILE = '@form/uploadFile';

export const IS_DEV = process.env.NODE_ENV === 'development';

export const MFOX_LOCALE = process.env.MFOX_LOCALE;

export const MFOX_BUILD_TYPE = process.env.MFOX_BUILD_TYPE || 'web';

// define this issue then use front dev.
export const MFOX_SKIP_FRONT_DEV = process.env.MFOX_SKIP_FRONT_DEV || false;

export const IS_ADMINCP = MFOX_BUILD_TYPE === 'admincp' ? 1 : 0;

export const IS_INSTALLATION = process.env.MFOX_BUILD_TYPE === 'installation';

export const IS_WEB = process.env.MFOX_BUILD_TYPE === 'web';

export const MFOX_API_URL = process.env.MFOX_API_URL || '/api/v1';

export const MFOX_ADMINCP_URL = process.env.MFOX_ADMINCP_URL || '/admincp';

export const RTL_LOCALE = [
  'ar',
  'arc',
  'arz',
  'dv',
  'fa',
  'ha',
  'he',
  'khw',
  'ks',
  'ku',
  'ps',
  'sd',
  'ur',
  'uz_AF',
  'yi'
];

export const BUNDLE_DIR = `bundle-${MFOX_BUILD_TYPE}`;

export const FORM_SEARCH_SUBMIT = '@form/search/SUBMIT';

export const THEME_KEY = IS_ADMINCP ? 'themeId1' : 'themeId';

export const DEFAULT_THEME = IS_ADMINCP
  ? process.env.MFOX_ADMINCP_THEME
  : process.env.MFOX_SITE_THEME;

export const DEFAULT_THEME_TYPE = process.env.MFOX_SITE_THEME_TYPE;

export const ACTIVE_THEMES = process.env.MFOX_ACTIVE_THEMES;

export const FORM_ADMIN_SEARCH_SUBMIT = '@formAdmin/search/SUBMIT';

export const ABORT_CONTROL_CANCEL = '@abortControl/cancel';

export const ABORT_CONTROL_START = '@abortControl/start';

export const FETCH_DETAIL = '@fetchDetail';

export const ENTITY_REFRESH = '@entity/REFRESH';

export const ENTITY_DELETE = '@entity/DELETE';

export const ENTITY_PATCH = '@entity/PATCH';

export const ENTITY_PUT = '@entity/PUT';

export const ENTITY_FULFILL = '@entity/FULFILL';

export const PAGINATION = '@pagination';

export const PAGINATION_UPDATE_LAST_READ: string =
  '@pagination/UPDATE_LAST_READ';

export const PAGINATION_MODIFIED = '@pagination/modified';

export const PAGINATION_INIT: string = '@pagination/INIT';

export const PAGINATION_SUCCESS = '@pagination/SUCCESS';

export const PAGINATION_UNSHIFT = '@pagination/UNSHIFT';

export const PAGINATION_PUSH = '@pagination/PUSH';

export const PAGINATION_PUSH_INDEX = '@pagination/PUSH_INDEX';

export const PAGINATION_SWAP = '@pagination/SWAP';

export const PAGINATION_START = '@pagination/START';

export const PAGINATION_DELETE = '@pagination/DELETE';

export const PAGINATION_REFRESH = '@pagination/REFRESH';

export const PAGINATION_FULFILL_PAGE = '@pagination/FULFILL/PAGE';

export const PAGINATION_FAILED = '@pagination/FAILED';

export const PAGINATION_CLEAR = '@pagination/CLEAR';

export const PAGINATION_UN_LIST = '@pagination/UN_LIST';

export const PAGINATION_RESET_ALL = '@pagination/resetAll';

export const LOGGED_OUT = 'session/logout';

export const REFRESH_TOKEN = 'session/refresh_token';

export const STRATEGY_REFRESH_TOKEN = 'session/strategy_refresh_token';

export const ACTION_UPDATE_TOKEN = 'session/updateToken';

export const CLOSE_MENU = 'closeMenu';

export const APP_BOOTSTRAP = '@bootstrap';

export const APP_BOOTSTRAP_DONE = '@bootstrap/DONE';

export const RELOAD_USER = '@reloadUser';

export const LAYOUT_EDITOR_TOGGLE = '@layout/toggleEditor';

export const LS_AUTH_NAME = 'authUser';

export const LS_GUEST_NAME = 'authGuest';

export const LS_ACCOUNT_NAME = 'accounts';

/**
 * load page meta data action name
 */
export const LOAD_PAGE_META = '@core/loadPageMeta';

export const RELOAD_PAGE_META = '@core/reloadPageMeta';

/**
 * Default locale group to load from frontend
 * etc: /api/v1/core/translation/{LOCALE_GROUP}/en
 */
export const LOCALE_GROUP = 'web';

export const APP_SERVICE_CONTEXT = 'useGlobal';

export const CACHE_SETTING_KEY = IS_ADMINCP ? 'settings.admin' : 'settings';

/**
 * Load data from prefer remote server when bootrap.
 */
export const USE_BOOTSTRAP_CACHE = true;

export const GET_STATICS = '@getStatics';

export const FORM_SUBMIT_ACTION = '@form/submitAction';

export const ACP_LICENSE_RECHECK = 'license/recheck';

export const FETCH_PREVIEW_PHOTO = '@fetch/preview/photo';

export const CORE_GOOGLE_GOOGLE_MAP_API_KEY =
  process.env.MFOX_CORE_GOOGLE_GOOGLE_MAP_API_KEY;

export const CAPTCHA_RECAPTCHA_V3_SITE_KEY =
  process.env.MFOX_CAPTCHA_RECAPTCHA_V3_SITE_KEY;
// Pusher
export const BROADCAST_CONNECTIONS_PUSHER_DRIVER =
  process.env.MFOX_BROADCAST_CONNECTIONS_PUSHER_DRIVER;
export const BROADCAST_CONNECTIONS_PUSHER_KEY =
  process.env.MFOX_BROADCAST_CONNECTIONS_PUSHER_KEY;
export const BROADCAST_CONNECTIONS_PUSHER_OPTIONS_CLUSTER =
  process.env.MFOX_BROADCAST_CONNECTIONS_PUSHER_OPTIONS_CLUSTER;
// Firebase
export const FIREBASE_API_KEY = process.env.MFOX_FIREBASE_API_KEY;
export const FIREBASE_AUTH_DOMAIN = process.env.MFOX_FIREBASE_AUTH_DOMAIN;
export const FIREBASE_PROJECT_ID = process.env.MFOX_FIREBASE_PROJECT_ID;
export const FIREBASE_STORAGE_BUCKET = process.env.MFOX_FIREBASE_STORAGE_BUCKET;
export const FIREBASE_SENDER_ID = process.env.MFOX_FIREBASE_SENDER_ID;
export const FIREBASE_APP_ID = process.env.MFOX_FIREBASE_APP_ID;
export const LINK_TRACKING_CLICK = 'link/trackingClick';
export const TRACKING_IN_VIEW = 'tracking/inViewItem';
export const TRACKING_IN_VIEW_EXIT = 'tracking/exitInViewItem';
export const LINK_OFFLINE_MODE = `${process.env.MFOX_ADMINCP_URL}/core/setting/site-mode`;
export const MFOX_FAVICON_URL = process.env.MFOX_FAVICON_URL;
export const MFOX_PWA_APP_NAME = process.env.MFOX_PWA_APP_NAME;
export const MFOX_PWA_DISPLAY = process.env.MFOX_PWA_DISPLAY;
export const MFOX_PWA_INSTALL_DESCRIPTION =
  process.env.MFOX_PWA_INSTALL_DESCRIPTION;
export const MFOX_PWA_APP_DESCRIPTION = process.env.MFOX_PWA_APP_DESCRIPTION;
export const ASSET_PATH = process.env.ASSET_PATH;
export const PAGEMETA_REFRESH = '@pageMeta/REFRESH';
