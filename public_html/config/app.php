<?php

$apiUrl      = env('MFOX_API_URL', '/api/v1');
$apiUrlInfo  =  parse_url($apiUrl);
$apiRootUrl  = isset($apiUrlInfo['scheme']) && isset($apiUrlInfo['host']) ? $apiUrlInfo['scheme'] . '://' . $apiUrlInfo['host'] : '';
$isInstalled = env('MFOX_APP_INSTALLED', false);

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('MFOX_SITE_NAME', 'Social Network'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => $isInstalled ? (bool) env('APP_DEBUG', false) : true,

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => rtrim(env('APP_URL', 'http://localhost'), '/'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        // 'store'  => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        // Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\TranslationServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App'          => Illuminate\Support\Facades\App::class,
        'Arr'          => Illuminate\Support\Arr::class,
        'Artisan'      => Illuminate\Support\Facades\Artisan::class,
        'Auth'         => Illuminate\Support\Facades\Auth::class,
        'Blade'        => Illuminate\Support\Facades\Blade::class,
        'Broadcast'    => Illuminate\Support\Facades\Broadcast::class,
        'Bus'          => Illuminate\Support\Facades\Bus::class,
        'Cache'        => Illuminate\Support\Facades\Cache::class,
        'Config'       => Illuminate\Support\Facades\Config::class,
        'Cookie'       => Illuminate\Support\Facades\Cookie::class,
        'Crypt'        => Illuminate\Support\Facades\Crypt::class,
        'DB'           => Illuminate\Support\Facades\DB::class,
        'Eloquent'     => Illuminate\Database\Eloquent\Model::class,
        'Event'        => Illuminate\Support\Facades\Event::class,
        'File'         => Illuminate\Support\Facades\File::class,
        'Gate'         => Illuminate\Support\Facades\Gate::class,
        'Hash'         => Illuminate\Support\Facades\Hash::class,
        'Http'         => Illuminate\Support\Facades\Http::class,
        'Lang'         => Illuminate\Support\Facades\Lang::class,
        'Log'          => Illuminate\Support\Facades\Log::class,
        'Mail'         => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password'     => Illuminate\Support\Facades\Password::class,
        'Queue'        => Illuminate\Support\Facades\Queue::class,
        'Redirect'     => Illuminate\Support\Facades\Redirect::class,
        // 'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request'   => Illuminate\Support\Facades\Request::class,
        'Response'  => Illuminate\Support\Facades\Response::class,
        'Route'     => Illuminate\Support\Facades\Route::class,
        'Schema'    => Illuminate\Support\Facades\Schema::class,
        'Session'   => Illuminate\Support\Facades\Session::class,
        'Storage'   => Illuminate\Support\Facades\Storage::class,
        'Str'       => Illuminate\Support\Str::class,
        'URL'       => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View'      => Illuminate\Support\Facades\View::class,

    ],

    'upload_file_folder' => env('UPLOAD_FILE_FOLDER', 'storage'),
    'api_key'            => env('MFOX_API_KEY', 2),
    'api_secret'         => env('MFOX_API_SECRET', '738ab5b83c902a7b81860e05811fd5cd65e95f72'),

    // Config store.
    'store_url'     => rtrim(env('MFOX_STORE_URL', 'https://store.phpfox.com'), '/'),
    'store_api_url' => rtrim(env('MFOX_STORE_API_URL', 'https://api.phpfox.com'), '/'),

    'export_path'      => env('MFOX_EXPORT_PATH', 'export'),
    'export_data_path' => env(
        'MFOX_EXPORT_DATA_PATH',
        'src' .
        DIRECTORY_SEPARATOR . 'Database' .
        DIRECTORY_SEPARATOR . 'Export'
    ),

    /*
     * Configure allure report
     */
    'allure_server'     => env('ALLURE_SERVER'),
    'allure_project_id' => env('ALLURE_PROJECT', 'default'),

    /*
     * is ci
     */
    'ci' => env('MFOX_CI', env('CI', false)),

    /*
     * Site title.
     */
    'site_title'          => env('MFOX_SITE_TITLE', 'Social Network Community'),
    'site_username'       => env('SITE_USERNAME', 'admin'),
    'site_password'       => env('SITE_PASSWORD', 'bubble666'),
    'site_email'          => env('SITE_EMAIL', 'developer@phpfox.com'),
    'backup_module_path'  => env('MFOX_BACKUP_PACKAGE_PATH', 'backup-module'),
    'import_module_path'  => env('MFOX_IMPORT_PACKAGE_PATH', 'import-module'),
    'import_phrase_path'  => env('MFOX_IMPORT_PHRASE_PATH', 'import-phrase'),
    'install_module_path' => env('MFOX_INSTALL_PACKAGE_PATH', 'install-module'),
    'trusted_proxies'     => env('APP_TRUSTED_PROXIES', '**'),

    // custom domain and url.
    'mfox_api_url'          => $apiUrl,
    'mfox_api_root_url'     => $apiRootUrl,
    'mfox_api_route_prefix' => env('MFOX_API_ROUTE_PREFIX', '/api/{ver}'),
    'mfox_public_html'      => env('MFOX_PUBLIC_HTML', ''),
    'mfox_admincp_url'      => env('MFOX_ADMINCP_URL', '/admincp'),
    'mfox_site_theme'       => 'a0:a0',

    'mfox_license_id'                     => env('MFOX_LICENSE_ID', ''),
    'mfox_license_key'                    => env('MFOX_LICENSE_KEY', ''),
    'mfox_installed'                      => env('MFOX_APP_INSTALLED', false),
    'mfox_frontend_root'                  => env('MFOX_FRONTEND_ROOT', ''),
    'mfox_app_channel'                    => env('MFOX_APP_CHANNEL', 'production'),
    'mfox_store_api_token'                => env('MFOX_STORE_API_TOKEN', ''),
    'mfox_downloadable_framework_version' => env('MFOX_DOWNLOADABLE_FRAMEWORK_VERSION', null),
    'mfox_downloadable_framework_url'     => env('MFOX_DOWNLOADABLE_FRAMEWORK_URL', null),
    'mfox_bundle_service_url'             => env('MFOX_BUNDLE_SERVICE_URL', 'https://build-service.phpfox.com'),
    // Down load app from store when build front end.
    'mfox_bundle_service_download_app' => env('MFOX_BUNDLE_SERVICE_DOWNLOAD_APP', true),
    'mfox_default_video_service'       => env('MFOX_VIDEO_PROVIDER', 'ffmpeg'),
    'enable_profiler'                  => env('MFOX_PROFILER', false),
    'enable_octane'                    => env('MFOX_ENABLE_OCTANE', false),
    'enable_load_reduce'               => env('MFOX_LOAD_REDUCE', true),
    'cache_anonymous_request_tll'      => env('CACHE_ANONYMOUSE_REQUEST_TTL', 60),
    /*
     * Configure locale
     */
    'localize' => [
        'country_iso' => env('MFOX_DEFAULT_COUNTRY_ISO', 'US'),
        'state_iso'   => env('MFOX_DEFAULT_COUNTRY_STATE_ISO', 'US-AR'),
        'city_iso'    => env('MFOX_DEFAULT_CITY_CODE', '1580578'),
    ],

    /*
     * force prototol
     * fix issue for laravel route() ...
     */

    'force_protocol' => str_starts_with(env('APP_URL', 'http://localhost'), 'https://') ? 'https' : 'http',
];
