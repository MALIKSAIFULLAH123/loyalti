<?php

/** @noinspection PhpConditionAlreadyCheckedInspection */

namespace App;

require_once __DIR__ . '/ProcessHelper.php';
require_once __DIR__ . '/Setup/State.php';
require_once __DIR__ . '/Setup/SystemRequirements.php';

/*
 * This file run OUT OF laravel framework installed and vendor has exists.
 * So do not import any source code from platform and others.
 *
 * Every post request contains configuration from front ends.
 */

/**
 * Class SetupWizard.
 */
class SetupWizard
{
    /**
     * @var string
     */
    private $projectRoot;

    /**
     * @var string
     */
    private $logFile;

    /**
     * @var string
     */
    private $envFile;

    /**
     * @var string
     */
    private $storeApiUrl;

    /**
     * Check is installed.
     * @var bool
     */
    private $platformInstalled = false;

    /** @var array */
    private $input = [];

    /**
     * @var string
     */
    private $platformVersion = '5.0.0';

    /**
     * @var mixed|null
     */
    private $platformInstalledVersion = null;

    /**
     * @var array
     */
    private $envVars = [];

    /**
     * @var bool
     */
    private $upgrading = false;

    /**
     * @var string
     */
    private $downloadFrameworkFolder;

    /**
     * @var string
     */
    private $downloadAppFolder;

    /**
     * @var string
     */
    private $extractAppFolder;

    /**
     * @var string
     */
    private $extractFrameworkFolder;

    /**
     * "development" | "production".
     * @var string
     */
    private $appChannel = 'production';

    /**
     * @var \App\ProcessHelper
     */
    private $processHelper;

    public function __construct()
    {
        $this->projectRoot   = dirname(__DIR__);
        $this->processHelper = ProcessHelper::factory();

        $this->downloadFrameworkFolder = $this->basePath('storage/install/download-framework');
        $this->downloadAppFolder       = $this->basePath('storage/install/download-apps');
        $this->extractAppFolder        = $this->basePath('storage/install/extract-apps');
        $this->extractFrameworkFolder  = $this->basePath('storage/install/extract-framework');

        $this->ensureDir($this->downloadAppFolder);
        $this->ensureDir($this->extractAppFolder);
        $this->ensureDir($this->downloadFrameworkFolder);
        $this->ensureDir($this->extractFrameworkFolder);
        $this->ensureDir($this->extractFrameworkFolder);

        $this->logFile = $this->basePath('storage/logs/' . sprintf('installation-%s.log', date('Y-m-d')));

        $this->getCurrentPlatformVersion();

        $content = file_get_contents('php://input');

        if ($content) {
            $this->input = json_decode($content, true);
        }

        $this->envFile = implode(DIRECTORY_SEPARATOR, [$this->projectRoot, '.env']);
        if (
            $this->envFile &&
            file_exists($this->envFile) &&
            is_readable($this->envFile)
        ) {
            $this->envVars = $this->parseEnvString(file_get_contents($this->envFile));

            $this->platformInstalledVersion = $this->getOnlyEnvVar('MFOX_APP_VERSION');
            $this->platformInstalled        = (bool) $this->getOnlyEnvVar('MFOX_APP_INSTALLED');
        }

        $this->storeApiUrl = rtrim($this->getEnv('MFOX_STORE_API_URL', 'https://api.phpfox.com'), '/');
        $this->appChannel  = $this->getEnv('MFOX_APP_CHANNEL', 'production');
        $this->upgrading    = $this->platformInstalled && ($this->platformInstalledVersion != $this->platformVersion);
    }

    /**
     * Get project absolute path.
     * @param  string $path
     * @return string
     */
    private function basePath($path)
    {
        return $this->projectRoot . '/' . ltrim($path, '/');
    }

    /**
     * @param       $directory
     * @return void
     */
    public function ensureDir($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * @param  array $data
     * @return void
     */
    public function checkDownloadFrameworkSteps(&$data)
    {
        if (version_compare($this->platformInstalledVersion, $this->platformVersion, '>=')) {
            return;
        }
        $frameworkVersion = $this->getDownloadableFrameworkVersion();

        if (version_compare($this->platformVersion, $frameworkVersion, '>=')) {
            return;
        }
        $data[] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=download-framework',
                'apiMethod' => 'GET',
            ],
            'title' => sprintf('Download MetaFox - v%s', $frameworkVersion),
        ];
        $data[] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=extract-framework',
                'apiMethod' => 'GET',
            ],
            'title' => 'Extract Framework',
        ];
    }

    /**
     * Get existing environment from env var only.
     * @param  string     $name
     * @param  mixed      $default
     * @return mixed|null
     */
    private function getOnlyEnvVar($name, $default = null)
    {
        return array_key_exists($name, $this->envVars) ? $this->envVars[$name] : $default;
    }

    /**
     * @param  string           $method
     * @param  \App\Setup\State $state
     * @return mixed
     */
    private function executeStep($method, $state)
    {
        $this->log(sprintf('executeStep %s', $method));

        if (!method_exists($this, $method)) {
            throw new \RuntimeException('Step not found');
        }

        return $this->{$method}($state);
    }

    /**
     * defined MFOX_ROOT at ./public/index.php.
     *
     * @return void
     */
    public function execute()
    {
        ignore_user_abort(true);
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        header('content-type: application/json');
        ob_start();
        chdir($this->projectRoot);

        $step = $this->arrGet($_REQUEST, 'step', 'start');
        $sid  = $this->arrGet($_REQUEST, 'sid', $step);

        // backward compatible with v5.1.2
        if ('verify-composer-installed' === $step) {
            $step = 'dump-autoload';
            $sid  = 'dump-autoload';
        }

        $state = \App\Setup\State::factory($sid);

        $this->log(json_encode([
            'sid'   => $sid,
            'step'  => $step,
            'state' => $state->get(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // toggle to retry
        if ($state->isFailed()) {
            $state->markRetried(0);
        }

        try {
            $method = 'step' . $this->studlyCase($step);

            $state->markStartIfNeeded();

            $data = $this->executeStep($method, $state);

            ob_get_clean();

            echo json_encode($data);
        } catch (\Throwable $error) {
            // need to retry or not.
            $attemps = $state->get('attemps', 0);
            $retries = $state->get('retries', 0);
            $message = $error->getMessage();
            $this->log($error->getTraceAsString());

            if ($retries <= $attemps) {
                $this->log("Retry $step");
                $state->markRetried(++$retries);
                echo json_encode([
                    'status' => 'success',
                    'data'   => ['retry' => true],
                ]);
            } else {
                ob_get_clean();
                http_response_code(400);
                $state->markFailed();
                echo json_encode([
                    'status' => 'failed',
                    'error'  => $message,
                    'alert'  => [
                        'title'   => 'Alert',
                        'message' => $message,
                    ],
                ]);
            }
        }
        die(0);
    }

    /**
     * @return void
     */
    private function setupComposer()
    {
        $this->log(sprintf('Start %s', __METHOD__));

        $pathToComposer = $this->processHelper->getComposerPath();

        if (!file_exists($pathToComposer)) {
            $installer = self::mf_get_contents('https://getcomposer.org/download/latest-stable/composer.phar');
            file_put_contents($pathToComposer, $installer);
        }

        @chmod($pathToComposer, 0755);
    }

    /**
     * @param  int         $code
     * @param  array|null  $errors
     * @param  string|null $message
     * @param  array|null  $alert
     * @return array
     */
    private function failure($code, $errors, $message = null, $alert = null)
    {
        http_response_code($code);

        $response = [
            'errors'  => $errors,
            'message' => $message,
            'status'  => 'failure',
        ];

        if ($alert) {
            $response['alert'] = $alert;
        }

        return $response;
    }

    /**
     * @param  array       $data
     * @param  string|null $message
     * @return array
     */
    private function success($data, $message = null)
    {
        return [
            'data'    => $data,
            'message' => $message,
            'status'  => 'success',
        ];
    }

    /**
     * @param  \App\Setup\State $state
     * @return array
     */
    public function stepWaitFrontend($state)
    {
        if (($result = $state->checkInProgress())) {
            return $this->success($result);
        }

        // where to get JobId.
        try {
            $this->processHelper->callPhp('artisan frontend:build --check');
        } catch (\Exception $exception) {
            $this->log($exception->getMessage());
        }

        return $this->success(['retry' => true]);
    }

    /**
     * @param  \App\Setup\State $state
     * @return array
     * @link /install/build-frontend
     */
    public function stepBuildFrontend($state)
    {
        $this->processHelper->callPhp('artisan frontend:build');

        return $this->success([]);
    }

    /**
     * @return array
     */
    public function discoverExistedPackages()
    {
        $basePath = $this->projectRoot;
        $files    = [];
        $packages = [];
        $patterns = [
            'packages/*/composer.json',
            'packages/*/*/composer.json',
            'packages/*/*/*/composer.json',
        ];

        array_walk($patterns, function ($pattern) use (&$files, $basePath) {
            $dir = rtrim($basePath, DIRECTORY_SEPARATOR,) . DIRECTORY_SEPARATOR . $pattern;
            foreach (glob($dir) as $file) {
                $files[] = $file;
            }
        });

        array_walk($files, function ($file) use (&$packages, $basePath) {
            try {
                $data  = json_decode(file_get_contents($file), true);
                $extra = $this->arrGet($data, 'extra.metafox');
                if (!$extra || !is_array($extra)) {
                    return;
                }
                $extra = $data['extra']['metafox'];

                $packages[$data['name']] = [
                    'name'    => $data['name'],
                    'version' => $data['version'],
                    'path'    => trim(substr(dirname($file), strlen($basePath)), DIRECTORY_SEPARATOR),
                    'core'    => $this->arrGet($extra, 'core', false),
                ];
            } catch (\Exception $exception) {
                //
            }
        });

        return $packages;
    }

    /**
     * @param  string $string
     * @return string
     */
    private function studlyCase($string)
    {
        return $string ? str_replace(' ', '', ucwords(preg_replace('#([^a-zA-Z\d]+)#m', ' ', $string))) : '';
    }

    /**
     * @param  \PDO  $pdo
     * @param  array $config
     * @return void
     */
    private function verifyDatabaseAvaiable(\PDO $pdo, $config)
    {
        try {
            $version    = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            $minVersion = match ($config['driver']) {
                'mysql' => mb_stripos($version, 'maria') === false ? '8.0' : '10.6', //mysql + mariadb
                default => '13', // pgsql
            };

            if (version_compare($version, $minVersion, '<')) {
                throw new \InvalidArgumentException(sprintf(
                    'MetaFox requires database version >= %s. The current database version is %s.',
                    $minVersion,
                    $version,
                ));
            }

            $prefix    = $this->arrGet($config, 'prefix', '');
            $tableName = $prefix . 'packages';
            $sql       = sprintf('select * from %s LIMIT 1', $tableName);
            $pdo->query($sql)->fetchAll();

            throw new \InvalidArgumentException(
                sprintf(
                    'Database %s is not available, Drop all tables then continue.',
                    $config['name']
                )
            );
        } catch (\PDOException $exception) { // OK, TABLE DOES NOT exist
            // do nothing
        }
    }

    /**
     * There no laravel install here, use this function to help set && get data.
     * @param             $array
     * @param             $name
     * @param             $default
     * @return mixed|null
     */
    private function arrGet($array, $name, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }

        $keys = explode('.', $name);

        while (count($keys)) {
            $key = array_shift($keys);
            if (!array_key_exists($key, $array)) {
                return $default;
            }
            $array = $array[$key];
        }

        return $array;
    }

    /**
     * @param  \App\Setup\State $state
     * @return array
     */
    private function stepVerifyDatabase($state)
    {
        $this->log(sprintf('Start %s', __METHOD__));

        $input = $this->getInput();

        $config = $input['database'];

        $driver = $this->arrGet($input, 'database.driver', 'pgsql');
        $port   = $this->arrGet($input, 'database.port');

        $this->log(sprintf('Start %s', __METHOD__));

        if (!$port) {
            $config['port'] = $driver === 'pgsql' ? 5432 : 3306;
        }

        // the API should prevent attempting and return error immediately
        $state->setAttempt(-1);

        $dns = sprintf('%s:host=%s;port=%s;dbname=%s', $driver, $config['host'], $config['port'], $config['name']);

        try {
            $pdo = new \PDO(
                $dns,
                $config['user'],
                $config['password'],
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );

            $this->verifyDatabaseAvaiable($pdo, $config);
        } catch (\PDOException $exception) {
            $message = sprintf(
                'Could not connect to database server %s %s %s ',
                $dns,
                PHP_EOL,
                $exception->getMessage()
            );

            throw new \InvalidArgumentException($message);
        }

        return $this->success([], 'Configure database successfully.');
    }

    /**
     * @return array|mixed
     */
    private function getInput()
    {
        return $this->input;
    }

    /**
     * @param  \App\Setup\State $state
     * @return array
     * @link  ?step=verify-license
     */
    private function stepVerifyLicense($state)
    {
        $this->log(sprintf('Start %s', __METHOD__));

        $input = $this->getInput();

        if (!$input || !$input['license']) {
            throw new \RuntimeException('Missing license key/id');
        }

        // the API should prevent attempting and return error immediately
        $state->setAttempt(-1);

        $params = [
            'url'               => $input['general']['app_url'],
            'installation_path' => '',
        ];

        $this->httpRequest(
            $this->storeApiUrl . '/verify',
            'post',
            $params,
            ['Accept: application/json'],
            $input['license']
        );

        return $this->success([], 'Configured license key');
    }

    /**
     * @param  \App\Setup\State $state
     * @return array
     */
    private function stepVerifyGeneralInfo($state)
    {
        $this->log(sprintf('Start %s', __METHOD__));

        return $this->success([]);
    }

    /**
     * @param  \App\Setup\State $state
     * @return array
     */
    private function stepCleanup($state)
    {
        $this->processHelper->exec(sprintf('rm -rf %s/storage/install', $this->projectRoot));

        return $this->success([]);
    }

    /**
     * @return array
     */
    private function stepCleanCache()
    {
        $this->processHelper->callPhp('artisan optimize:clear');

        return $this->success([]);
    }

    /**
     * @return array
     */
    private function stepOptimize()
    {
        $this->processHelper->callPhp('artisan optimize');

        return $this->success([]);
    }

    /**
     * @param              $license
     * @return array|mixed
     */
    public function getRecommendAppsForInstall($license)
    {
        $existedApps = $this->discoverExistedPackages();

        $params = [
            'version'         => $this->platformVersion,
            'release_channel' => $this->appChannel,
        ];

        $headers = [
            'Accept: application/json',
        ];

        $payload = $this->httpRequest(
            $this->storeApiUrl . '/purchased',
            'GET',
            $params,
            $headers,
            $license
        ) ?? [];

        foreach ($payload as $index => $latest) {
            if (!$this->shouldRecommendApp($existedApps, $latest)) {
                unset($payload[$index]);
            }
        }

        usort($payload, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $payload;
    }

    private function shouldRecommendApp($existedApps, $latest): bool
    {
        $id                  = $latest['identity'];
        $canInstall          = $latest['can_install'] ?? false;
        $downloadableVersion = $latest['version'] ?? null;

        if (empty($downloadableVersion)) {
            return false;
        }

        if (!$canInstall) {
            return false;
        }

        if (!isset($existedApps[$id])) {
            return true;
        }

        $currentVersion = $existedApps[$id]['version'] ?? null;

        if (empty($currentVersion)) {
            return true;
        }

        return version_compare($currentVersion, $downloadableVersion, '<');
    }

    /**
     * @return array
     */
    public function stepSelectApps()
    {
        $license = $this->getInput()['license'];
        $this->log(sprintf('Start %s', __METHOD__));

        $recommendApps = $this->getRecommendAppsForInstall($license);

        $this->log(var_export($recommendApps, true));

        return $this->success([
            'loadedAppsLoaded' => true,
            'recommendApps'    => $recommendApps,
        ]);
    }

    /**
     * @param       $filename
     * @return bool
     */
    private function ensureIsZipFile($filename)
    {
        if (!file_exists($filename)) {
            return false;
        }

        $archive = new \ZipArchive();

        if (!$archive->open($filename, \ZipArchive::RDONLY)) {
            unlink($filename);
            throw new \RuntimeException('Could not archive file');
        }

        $archive->close();

        return true;
    }

    /**
     * @return array
     */
    public function stepDownloadApp(Setup\State $state)
    {
        $input   = $this->getInput();
        $id      = $input['id'];
        $version = $input['version'];
        $channel = $input['release_channel'];

        $filename = sprintf('%s/%s.zip', $this->downloadAppFolder, preg_replace("#\W+#", '-', $id));

        if ($state->isRetrying() && file_exists($filename)) {
            unlink($filename);
        }

        $verifier = function () use ($filename, $state) {
            if ($this->ensureIsZipFile($filename)) {
                $state->markDone();

                return true;
            }
        };

        if ($result = $state->checkInProgress($verifier)) {
            return $this->success($result);
        }

        $state->markProcessing();

        $json = $this->httpRequest($this->storeApiUrl . '/install', 'post', [
            'id'              => $id,
            'version'         => $this->platformVersion,
            'app_version'     => $version,
            'version_type'    => 'source',
            'release_channel' => $channel,
        ], [], [
            'id'  => $this->getEnv('MFOX_LICENSE_ID'),
            'key' => $this->getEnv('MFOX_LICENSE_KEY'),
        ]);

        $this->log('downloading info ' . $id);
        $this->log($json);

        if (!isset($json['download']) || !$json['download']) {
            throw new \RuntimeException('Could not get download url');
        }

        $temporary = $filename . '.temp';

        register_shutdown_function(function () use ($temporary, $filename) {
            if (file_exists($temporary)) {
                copy($temporary, $filename);
                unlink($temporary);
            }
        });

        $this->processHelper->downloadFile($json['download'], $temporary);

        $state->markDone();

        return $this->success([]);
    }

    /**
     * @param       $message
     * @param       $level
     * @return void
     */
    private function log($message, $level = 'DEBUG')
    {
        if (is_array($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        $message = sprintf('[%s] production:%s: %s', strtoupper($level), date('Y-m-d H:i:s'), $message);

        file_put_contents($this->logFile, $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * Get collections of app to upgrades.
     * @return array
     */
    public function getRecommendAppsToUpgrades()
    {
        $this->log(sprintf('Start %s', __METHOD__));

        $existedApps = $this->discoverExistedPackages();

        $params = [
            'version'         => $this->platformVersion,
            'release_channel' => $this->appChannel,
        ];

        $headers = [
            'Accept: application/json',
        ];

        $payload = $this->httpRequest(
            $this->storeApiUrl . '/purchased',
            'GET',
            $params,
            $headers,
            [
                'id'  => $this->getEnv('MFOX_LICENSE_ID'),
                'key' => $this->getEnv('MFOX_LICENSE_KEY'),
            ]
        ) ?? [];

        foreach ($payload as $index => $latest) {
            $id                  = $latest['identity'];
            $canUpgrade          = $latest['can_upgrade'] ?? false;
            $minCompatibility    = $latest['version_detail']['min_compatibility'] ?? null;
            $downloadableVersion = $latest['version'] ?? null;

            if (!isset($existedApps[$id])) {
                unset($payload[$index]);
                continue;
            }

            if (!$canUpgrade || empty($downloadableVersion)) {
                unset($payload[$index]);
                continue;
            }

            $check = $existedApps[$id];
            if (!version_compare($latest['version'], $check['version'], '>')) {
                unset($payload[$index]);
                continue;
            }

            if ($minCompatibility && version_compare($minCompatibility, $this->platformInstalledVersion, '>')) {
                $payload[$index]['required'] = true;
            }
        }

        usort($payload, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $payload;
    }

    /**
     * @return array
     * @link: /extract-framework
     */
    private function stepExtractFramework()
    {
        $destination = $this->getDownloadFrameworkDestination();

        $archive = new \ZipArchive();

        if ($archive->open($destination) !== true) {
            throw new \RuntimeException('Could not open archive file');
        }

        $found = 'upload.zip';

        if (false === $archive->getFromName($found)) {
            $found = rtrim($archive->getNameIndex(0), '/') . '/' . $found;
        }

        $archive->extractTo($this->extractFrameworkFolder);
        $archive->close();

        $uploadZipFilename = $this->extractFrameworkFolder . '/' . $found;

        if (!file_exists($uploadZipFilename)) {
            throw new \RuntimeException('Missing file ' . $uploadZipFilename);
        }

        $upload = new \ZipArchive();

        if ($upload->open($uploadZipFilename) !== true) {
            throw new \RuntimeException('Could not open archive file');
        }

        for ($index = 0; $index < $upload->numFiles; $index++) {
            $this->log('extract overwrite ' . $upload->getNameIndex($index));
        }

        // overwrite to project root
        $upload->extractTo($this->projectRoot);

        return $this->success([]);
    }

    /**
     * @return string
     */
    private function getDownloadFrameworkDestination()
    {
        return $this->downloadFrameworkFolder . '/metafox.zip';
    }

    /**
     * @return mixed
     */
    private function getDownloadableFrameworkVersion()
    {
        $input = $this->getInput();
        $json  = $this->httpRequest($this->storeApiUrl . '/phpfox-download', 'post', [], [], $input['license']);

        if (!$json['download']) {
            throw new \RuntimeException('Failed getting download url.');
        }

        return $json['version'];
    }

    /**
     * @return array
     * @link ?step=download-framework
     */
    private function stepDownloadFramework($state)
    {
        $filename = $this->getDownloadFrameworkDestination();

        $downloadUrl = $this->getEnv('MFOX_DOWNLOADABLE_FRAMEWORK_URL');

        $verifier = function () use ($filename) {
            return $this->ensureIsZipFile($filename);
        };

        if (($result = $state->checkInProgress($verifier))) {
            return $this->success($result);
        }

        $state->markProcessing();

        if (!$downloadUrl) {
            $json = $this->httpRequest($this->storeApiUrl . '/phpfox-download', 'post', [], [], [
                'id'  => $this->getEnv('MFOX_LICENSE_ID'),
                'key' => $this->getEnv('MFOX_LICENSE_KEY'),
            ]);

            if (!is_array($json) || !$json['download']) {
                throw new \RuntimeException('Failed getting download url.');
            }

            $downloadUrl = $json['download'];
        }

        $temporary = $filename . '.temp';
        register_shutdown_function(function () use ($temporary, $filename) {
            if (file_exists($temporary)) {
                copy($temporary, $filename);
                unlink($temporary);
            }
        });

        // fix issue timeout request etc. request limit 15 sec but download need 30 sec.
        $this->processHelper->downloadFile($downloadUrl, $temporary);

        return $this->success([]);
    }

    /**
     * @param  \App\Setup\State $state
     * @return array
     */
    private function stepProcessInstall($state)
    {
        $this->clearDownloadApps();

        $this->log(sprintf('Start %s', __METHOD__));

        $input = $this->getInput();

        $data = [];

        if ($this->hasEnv('MFOX_LICENSE_ID')) {
            $data['verify-license'] = [
                'attemps'    => 0,
                'dataSource' => [
                    'apiUrl'    => '/install?step=verify-license',
                    'apiMethod' => 'POST',
                ],
                'title' => 'Verify License',
            ];
        }

        if ($this->hasEnv('MFOX_DAT_PW')) {
            $data['verify-database'] = [
                'dataSource' => [
                    'apiUrl'    => '/install?step=verify-database',
                    'apiMethod' => 'POST',
                ],
                'title' => 'Verify Database',
            ];
        }

        $data['configure-env-file'] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=configure-env-file',
                'apiMethod' => 'POST',
            ],
            'attemps' => 0,
            'title'   => 'Verify Environment',
        ];

        $apps = $input['selectedApps'];

        foreach ($apps as $app) {
            $sid        = 'download-app_' . $app['identity'];
            $data[$sid] = [
                'attemps'    => 2,
                'dataSource' => [
                    'apiUrl'    => '/install?step=download-app&sid=' . $sid,
                    'apiMethod' => 'POST',
                ],
                'data' => [
                    'id'              => $app['identity'],
                    'version'         => $app['version'],
                    'name'            => $app['name'],
                    'release_channel' => $app['release_channel'],
                ],
                'title' => 'Download ' . $app['name'] . ' - v' . $app['version'],
            ];
        }

        if (count($apps)) {
            $data['extract-apps'] = [
                'attemps'    => 1,
                'dataSource' => [
                    'apiUrl'    => '/install?step=extract-apps',
                    'apiMethod' => 'POST',
                ],
                'title' => 'Extract Apps',
            ];
        }

        $data['composer-install'] = [
            'attemps'    => 1,
            'dataSource' => [
                'apiUrl'    => '/install?step=composer-install',
                'apiMethod' => 'POST',
            ],
            'title' => 'Install Dependencies',
        ];

        $data['dump-autoload'] = [
            'attemps'    => 1,
            'dataSource' => [
                'apiUrl'    => '/install?step=dump-autoload',
                'apiMethod' => 'GET',
            ],
            'title' => 'Verify Dependencies',
        ];

        $data['metafox-install'] = [
            'attemps'    => 1,
            'dataSource' => [
                'apiUrl'    => '/install?step=metafox-install',
                'apiMethod' => 'POST',
            ],
            'title' => 'Process Install',
        ];

        $data['clean-cache'] = [
            'attemps'    => 1,
            'dataSource' => [
                'apiUrl'    => '/install?step=clean-cache',
                'apiMethod' => 'GET',
            ],
            'title' => 'Clean Cache',
        ];

        $data['optimize'] = [
            'attemps'    => 1,
            'dataSource' => [
                'apiUrl'    => '/install?step=optimize',
                'apiMethod' => 'GET',
            ],
            'title' => 'Generate Bootstrap Files',
        ];

        $data['restart_queue_worker'] = [
            'attemps'    => 1,
            'dataSource' => [
                'apiUrl'    => '/install?step=restart-queue-worker',
                'apiMethod' => 'GET',
            ],
            'title' => 'Restart Queues',
        ];

        $data['build_frontend'] = [
            'attemps'    => 1,
            'dataSource' => [
                'apiUrl'    => '/install?step=build-frontend',
                'apiMethod' => 'GET',
            ],
            'title' => 'Build Frontend',
        ];

        $data['wait_frontend'] = [
            'attemps'    => 1,
            'dataSource' => [
                'apiUrl'    => '/install?step=wait-frontend',
                'apiMethod' => 'GET',
            ],
            'title' => 'Waiting for Frontend',
        ];

        $data['cleanup'] = [
            'attemps'    => 1,
            'dataSource' => [
                'apiUrl'    => '/install?step=cleanup',
                'apiMethod' => 'GET',
            ],
            'title' => 'Clean Files',
        ];

        $state->write($data);

        return $this->success(array_values($data));
    }

    // keep this method for backward compatible <=5.1.2 when upgrade from admincp.

    /**
     * @param  \App\Setup\State $state
     * @return array
     */
    public function stepVerifyComposerInstalled($state)
    {
        if ($result = $state->checkInProgress()) {
            return $this->success($result);
        }

        $state->markProcessing();

        $this->log(sprintf('Start %s', __METHOD__));

        $this->processHelper->verifyComposerInstalled();

        return $this->success([], 'Install dependency successfully');
    }

    /**
     * @param  \App\Setup\State $state
     * @return array
     */
    public function stepDumpAutoload($state)
    {
        return $this->stepVerifyComposerInstalled($state);
    }

    /**
     * @return void
     */
    public function clearDownloadApps()
    {
        $this->processHelper->exec('rm -rf ' . $this->downloadFrameworkFolder);
    }

    /**
     * @param  \App\Setup\State $state
     * @return array
     */
    public function stepExtractApps($state)
    {
        if ($result = $state->checkInProgress()) {
            return $this->success($result);
        }

        $state->markProcessing();

        $files = scandir($this->downloadAppFolder);

        if (!$files) {
            return $this->success([]);
        }

        foreach ($files as $filename) {
            if (!str_ends_with($filename, '.zip')) {
                continue;
            }
            $filename = $this->downloadAppFolder . '/' . $filename;
            $archive  = new \ZipArchive();
            if (true !== $archive->open($filename, \ZipArchive::RDONLY)) {
                throw new \RuntimeException('Could not unzip ' . $filename);
            }
            $archive->extractTo($this->extractAppFolder);
            $archive->close();
            unlink($filename);
        }

        if (is_dir(sprintf('%s/backend', $this->extractAppFolder))) {
            $this->processHelper->exec(
                sprintf('cp -rf %s/backend/* %s', $this->extractAppFolder, $this->projectRoot),
                [],
                true
            );
        }

        $state->markDone();

        return $this->success([]);
    }

    /**
     * @return array
     */
    public function stepRestartQueueWorker()
    {
        $this->processHelper->callPhp('artisan queue:restart');

        return $this->success([]);
    }

    /**
     * @return array
     * @link /install?step=process-upgrade
     */
    private function stepProcessUpgrade()
    {
        $this->clearDownloadApps();
        $this->log(sprintf('Start %s', __METHOD__));

        $input = $this->getInput();

        $data = [];

        if ($this->hasEnv('MFOX_LICENSE_ID')) {
            $data['verify-license'] = [
                'dataSource' => [
                    'apiUrl'    => '/install?step=verify-license',
                    'apiMethod' => 'POST',
                ],
                'title' => 'Verify License',
            ];
        }

        $data['down-site'] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=down-site',
                'apiMethod' => 'GET',
            ],
            'title' => 'Down Site',
        ];

        $this->checkDownloadFrameworkSteps($data);

        $apps = $input['selectedApps'];
        foreach ($apps as $app) {
            $sid                  = 'download-app_' . $app['identity'];
            $data[$sid]           = [
                'attemps'    => 2,
                'dataSource' => [
                    'apiUrl'    => '/install?step=download-app&sid=' . $sid,
                    'apiMethod' => 'POST',
                ],
                'data' => [
                    'id'              => $app['identity'],
                    'version'         => $app['version'],
                    'name'            => $app['name'],
                    'release_channel' => $app['release_channel'],
                ],
                'title' => 'Download ' . $app['name'] . ' - v' . $app['version'],
            ];
        }

        if (count($apps)) {
            $data['extract-apps'] = [
                'dataSource' => [
                    'apiUrl'    => '/install?step=extract-apps',
                    'apiMethod' => 'GET',
                ],
                'title' => 'Extract Apps',
            ];
        }

        $data['composer-install'] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=composer-install',
                'apiMethod' => 'GET',
            ],
            'title' => 'Update Dependencies',
        ];

        $data['dump-autoload'] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=dump-autoload',
                'apiMethod' => 'GET',
            ],
            'title' => 'Verify Dependencies',
        ];

        $data['metafox-upgrade'] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=metafox-upgrade',
                'apiMethod' => 'GET',
            ],
            'title' => 'Upgrade',
        ];

        $data['clean-cache'] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=clean-cache',
                'apiMethod' => 'GET',
            ],
            'title' => 'Clean Cache',
        ];

        $data['optimize'] = [
            'attemps'    => 2,
            'dataSource' => [
                'apiUrl'    => '/install?step=optimize',
                'apiMethod' => 'GET',
            ],
            'title' => 'Generate Bootstrap Files',
        ];

        $data['restart-queue-worker'] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=restart-queue-worker',
                'apiMethod' => 'GET',
            ],
            'title' => 'Restart Queues',
        ];

        $data['build-frontend'] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=build-frontend',
                'apiMethod' => 'GET',
            ],
            'title' => 'Rebuild Frontend',
        ];

        $data['wait-frontend'] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=wait-frontend',
                'apiMethod' => 'GET',
            ],
            'title' => 'Waiting for Frontend',
        ];

        $data['cleanup'] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=cleanup',
                'apiMethod' => 'GET',
            ],
            'title' => 'Clean Files',
        ];

        $data['up-site'] = [
            'dataSource' => [
                'apiUrl'    => '/install?step=up-site',
                'apiMethod' => 'GET',
            ],
            'title' => 'Launch Site',
        ];

        return $this->success(array_values($data));
    }

    /**
     * @param       $name
     * @return bool
     */
    private function hasEnv($name)
    {
        return !empty(getenv($name)) || isset($this->envVars[$name]);
    }

    /**
     * Skip overwrite global to help protected server.
     * @param  string $name
     * @return bool
     */
    private function isGlobalEnv($name)
    {
        return array_key_exists($name, $_SERVER);
    }

    /**
     * @param                          $name
     * @param                          $fallback
     * @return array|mixed|string|null
     */
    private function getEnv($name, $fallback = null)
    {
        $value = getenv($name);

        if (!empty($value)) {
            return $value;
        }

        if (isset($this->envVars[$name])) {
            return $this->envVars[$name];
        }

        return $fallback;
    }

    /**
     * @param  \App\Setup\State $state
     * @return array
     */
    public function stepStart($state)
    {
        $state->reset();
        $data = $this->getDataForStart();
        $requirementPassed = $data['requirement']['result'] ?? false;

        if ($requirementPassed) {
            $this->processHelper->exec(sprintf('rm -rf %s/storage/install/*', $this->projectRoot));
        }

        return $this->success($data);
    }

    /**
     * Get necessary data for stepStart.
     * @return array
     */
    private function getDataForStart(): array
    {
        if ($this->upgrading) {
            return $this->getStartForUpgrade();
        }

        return $this->getStartForInstallation();
    }

    /**
     * @return array|array[]
     */
    private function getRequirements()
    {
        return (new \App\Setup\SystemRequirements())->getRequirements();
    }

    /**
     * @return array
     */
    private function getStartForUpgrade()
    {
        $requirement = $this->getRequirements();

        $steps = [];

        if (!$requirement['result']) {
            $steps[] = ['title' => 'Requirements', 'id' => 'requirements'];
        }

        $recommendApps = $this->getRecommendAppsToUpgrades();
        $selectApps    = array_map(function ($app) {
            return [
                'identity'        => $app['identity'],
                'name'            => $app['name'],
                'version'         => $app['version'],
                'release_channel' => $app['version_detail']['release_channel'],
            ];
        }, $recommendApps);

        $steps = [
            ['title' => 'Prepare', 'id' => 'prepare-upgrade'],
            count($recommendApps) ? ['title' => 'Applications', 'id' => 'choose-upgrade-apps'] : false,
            ['title' => 'Upgrade', 'id' => 'process-upgrade'],
            ['title' => 'Done', 'id' => 'upgraded'],
        ];

        $data = [
            'baseUrl' => $this->getRootUrl(),
            'root'    => $this->projectRoot,
            'license' => [
                'id'  => $this->getEnv('MFOX_LICENSE_ID', ''),
                'key' => $this->getEnv('MFOX_LICENSE_KEY', ''),
            ],
            'recommendAppsLoaded' => true,
            'recommendApps'       => $recommendApps,
            'selectedApps'        => $selectApps,
            'steps'               => array_values(array_filter($steps, function ($step) {
                return (bool) $step;
            })),
            'requirement'     => $requirement,
            'platformVersion' => $this->getPlatformVersion(),
            'legal'           => 'MetaFox',
            'helpBlock'       => $this->getUpgradeHelpBlock(),
            'succeed'         => false,
            'failure'         => false, // return true when error in started,
        ];

        if ($this->platformInstalled) {
            $data['installing'] = true;
        }

        if ($this->platformInstalledVersion == $this->platformVersion) {
            $data['forceStep'] = 'uptodate';
        }

        // prevent leak information whenever the site is installed.
        if ($this->platformInstalled) {
            unset($data['database'], $data['general'], $data['administrator']);
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getStartForInstallation()
    {
        $requirement = $this->getRequirements();

        $driver = extension_loaded('pdo_pgsql') ? 'pgsql' : 'mysql';

        $hasEnv = file_exists($this->getEnvFile());

        if ($this->getEnv('MFOX_DAT_DRIVER')) {
            $driver = $this->getenv('MFOX_DAT_DRIVER');
        }

        $recommendApps       = [];
        $recommendAppsLoaded = false;

        if ($this->getEnv('MFOX_LICENSE_ID')) {
            $recommendAppsLoaded = true;
            $recommendApps       = $this->getRecommendAppsForInstall([
                'id'  => $this->getEnv('MFOX_LICENSE_ID'),
                'key' => $this->getEnv('MFOX_LICENSE_KEY'),
            ]);
        }

        $steps = [
            ['title' => 'Requirements', 'id' => 'requirements'],
            $hasEnv && $this->hasEnv('MFOX_LICENSE_ID') ? false : ['title' => 'License', 'id' => 'license'],
            $hasEnv && $this->hasEnv('MFOX_DAT_PW') ? false : ['title' => 'Database', 'id' => 'database'],
            ['title' => 'Information', 'id' => 'info'],
            ['title' => 'Applications', 'id' => 'apps'],
            ['title' => 'Installation', 'id' => 'process-install'],
            ['title' => 'Done', 'id' => 'installed'],
        ];

        $data = [
            'baseUrl'         => $this->getEnv('APP_URL', $this->getRootUrl()),
            'root'            => $this->projectRoot,
            'release_channel' => $this->appChannel,
            'license'         => [
                'id'  => $this->getEnv('MFOX_LICENSE_ID', ''),
                'key' => $this->getEnv('MFOX_LICENSE_KEY', ''),
            ],
            'administrator' => [
                'username' => $this->getEnv('SITE_USERNAME', 'admin'),
                'password' => $this->getEnv('SITE_PASSWORD', ''),
                'email'    => $this->getEnv('SITE_EMAIL', ''),
            ],
            'general' => [
                'site_name' => $this->getEnv('MFOX_SITE_NAME', 'Social Network'),
                'app_url'   => $this->getEnv('APP_URL', $this->getRootUrl()),
                'app_env'   => $this->getEnv('APP_ENV', 'production'), // available options: production, local
                'app_key'   => $this->getEnv('APP_KEY', ''),
                'app_debug' => $this->getEnv('APP_DEBUG', false),
            ],
            'database' => [
                'driver'   => $this->getEnv('MFOX_DAT_DRIVER', $driver),
                'host'     => $this->getEnv('MFOX_DAT_HOST', 'localhost'),
                'name'     => $this->getEnv('MFOX_DAT_DBNAME', 'metafox'),
                'user'     => $this->getEnv('MFOX_DAT_USR', 'metafox'),
                'password' => $this->getEnv('MFOX_DAT_PW', ''),
                'prefix'   => $this->getEnv('MFOX_DAT_DBPREFIX', ''),
                'socket'   => $this->getEnv('MFOX_DAT_SOCKET', ''),
                'port'     => $this->getEnv('MFOX_DAT_PORT', ''),
            ],
            'steps' => array_values(array_filter($steps, function ($step) {
                return (bool) $step;
            })),
            'recommendAppsLoaded' => $recommendAppsLoaded,
            'recommendApps'       => $recommendApps,
            'selectedApps'        => [],
            'requirement'         => $requirement,
            'platformVersion'     => $this->getPlatformVersion(),
            'legal'               => 'MetaFox',
            'helpBlock'           => $this->getInstallHelpBlock(),
            'succeed'             => $this->platformInstalled,
            'failure'             => false, // return true when error in started,
        ];

        if ($this->platformInstalled) {
            $data['installing'] = true;
        }

        if ($this->platformInstalled && $this->platformInstalledVersion == $this->platformVersion) {
            $data['forceStep'] = 'uptodate';
        }

        // prevent leak information whenever the site is installed.
        if ($this->platformInstalled) {
            unset($data['database'], $data['general'], $data['administrator'], $data['license'], $data['steps']);
        }

        return $data;
    }

    /**
     * @return string
     */
    private function getPlatformVersion()
    {
        $content = file_get_contents($this->projectRoot . '/packages/platform/src/MetaFoxConstant.php');
        preg_match('/public const VERSION\s+=\s+\'(?<version>.+)\';/m', $content, $match);
        $version = is_array($match) ? $match['version'] : '5.0.0';

        return "v$version";
    }

    /**
     * @return string
     */
    private function getInstallHelpBlock()
    {
        return <<<'HELP_BLOCK'
If you encounter any problems with the installation, please feel free to
      <a
        target="_blank" rel="noopener noreferrer"
        href="https://clients.phpfox.com/">
        contact us</a>.
HELP_BLOCK;
    }

    /**
     * @return string
     */
    private function getUpgradeHelpBlock()
    {
        return <<<'HELP_BLOCK'
If you encounter any problems with the upgrading, please feel free to
      <a
        target="_blank" rel="noopener noreferrer"
        href="https://clients.phpfox.com/">
        contact us</a>.
HELP_BLOCK;
    }

    /**
     * @param  \App\Setup\State $state
     * @return array
     */
    private function stepComposerInstall($state)
    {
        if ($result = $state->checkInProgress()) {
            return $this->success($result);
        }

        $state->markProcessing();

        $this->processHelper->installAllDependencies();

        return $this->success([], 'Install dependency successfully');
    }

    /**
     * @return string
     */
    private function getEnvFile()
    {
        return $this->projectRoot . DIRECTORY_SEPARATOR . '.env';
    }

    /**
     * @return array
     */
    public function stepConfigureEnvFile()
    {
        $envFile = $this->getEnvFile();

        $input = $this->getInput();

        $contents = file_exists($envFile) ? file_get_contents($envFile) : '';

        if (!$this->arrGet($input, 'database.port')) {
            $input['database']['port'] = $input['database']['driver'] === 'pgsql' ? 5432 : 3306;
        }

        /** @var array<string,array> $values */
        $values = [
            'APP_ENV'            => @$input['general']['app_env'],
            'APP_DEBUG'          => (bool) @$input['general']['app_debug'],
            'APP_URL'            => @$input['general']['app_url'],
            'APP_KEY'            => '',
            'MFOX_APP_INSTALLED' => false,
            'MFOX_APP_VERSION'   => $this->platformVersion,
            'MFOX_APP_CHANNEL'   => $this->appChannel,
            'MFOX_LICENSE_ID'    => @$input['license']['id'],
            'MFOX_LICENSE_KEY'   => @$input['license']['key'],
            'SITE_USERNAME'      => @$input['administrator']['username'],
            'SITE_EMAIL'         => @$input['administrator']['email'],
            'SITE_PASSWORD'      => @$input['administrator']['password'],
            'MFOX_SITE_NAME'     => @$input['general']['site_name'],
            'MFOX_DAT_DRIVER'    => $input['database']['driver'],
            'MFOX_DAT_HOST'      => $input['database']['host'],
            'MFOX_DAT_PORT'      => $input['database']['port'],
            'MFOX_DAT_DBNAME'    => $input['database']['name'],
            'MFOX_DAT_USR'       => $input['database']['user'],
            'MFOX_DAT_PW'        => $input['database']['password'],
            'MFOX_DAT_DBPREFIX'  => $input['database']['prefix'],
            'MFOX_DAT_SOCKET'    => $input['database']['socket'],
            'BROADCAST_DRIVER'   => $this->getEnv('BROADCAST_DRIVER', 'log'),
        ];

        $contents = $this->updateEnvIfNeeded($contents, $values);

        file_put_contents($envFile, $contents);

        @chmod($envFile, 0644);

        return $this->success([]);
    }

    /**
     * @return array
     */
    public function stepDownSite()
    {
        $this->processHelper->callPhp('artisan down');

        return $this->success([]);
    }

    /**
     * @return array
     */
    public function stepUpSite()
    {
        $this->processHelper->callPhp('artisan up');

        return $this->success([]);
    }

    /**
     * @return array
     */
    private function stepMetafoxInstall($state)
    {
        if (($result = $state->checkInProgress())) {
            return $this->success($result);
        }

        $state->markProcessing();

        $this->processHelper->callComposer('metafox:install');

        $this->log('Installed Successfully');

        return $this->success([], 'Install successfully');
    }

    /**
     * @return array
     * @link /install?step=metafox-upgrade
     */
    private function stepMetafoxUpgrade($state)
    {
        if (($result = $state->checkInProgress())) {
            return $this->success($result);
        }

        $state->markProcessing();

        $this->processHelper->callComposer('metafox:upgrade', [], true);

        return $this->success([], 'Install successfully');
    }

    /**
     * @return string
     */
    private function getRootUrl()
    {
        $https   = false;
        $host    = 'localhost';
        $visitor = null;

        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }

        if (isset($_SERVER['HTTP_CF_VISITOR'])) {
            $visitor = $_SERVER['HTTP_CF_VISITOR'];
        }

        if (
            (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] === 'on') ||
            (array_key_exists('SERVER_PORT', $_SERVER) && $_SERVER['SERVER_PORT']) == 443 ||
            (array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            ($visitor && strpos($visitor, 'https'))
        ) {
            $https = true;
        }

        return sprintf('%s://%s', $https ? 'https' : 'http', $host);
    }

    /**
     * Get host path.
     *
     * @return string
     * @since 4.6.0 fix issue install from https on ec2, ...
     */
    private function getAppUrl()
    {
        $rootUrl = $this->getRootUrl();

        $baseUrl = preg_replace('/(.*)\/(public|install)(\/)*(.*)/m', '$1', $_SERVER['PHP_SELF']);

        return rtrim($rootUrl . '/' . $baseUrl, '/');
    }

    /**
     * @param  string     $url
     * @param  string     $method
     * @param  array|null $params
     * @param  array|null $headers
     * @param  array|null $license
     * @return mixed
     */
    private function httpRequest($url, $method, $params, $headers, $license)
    {
        $this->log(sprintf('Start %s', __METHOD__));

        if (!isset($params['version'])) {
            // it's required to add this parameter in every request
            $params['version'] = $this->platformVersion;
        }

        $method = strtoupper($method);
        $post   = http_build_query($params);

        $curl_url = (($method == 'GET' && !empty($post)) ? $url . (strpos($url, '?') ? '&' : '?') . ltrim(
            $post,
            '&'
        ) : $url);

        // update api versioning
        $headers[] = 'X-Product: metafox';
        $headers[] = 'X-Namespace: phpfox';
        $headers[] = 'X-API-Version: 1.1';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $curl_url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        if ($method != 'GET' || $method != 'POST') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($license) {
            $headers[] = 'Authorization: Basic ' . base64_encode($license['id'] . ':' . $license['key']);
        }

        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, 20);

        if ($method != 'GET') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }

        $response = curl_exec($curl);

        curl_close($curl);

        $response = trim($response);

        $response = json_decode($response, true);

        if (isset($response['error']) && $response['error']) {
            throw new \RuntimeException($response['error']);
        }

        return array_key_exists('data', $response) ? $response['data'] : $response;
    }

    /**
     * @param                   $value
     * @return bool|string|null
     */
    private function formatEnvVar($value)
    {
        $var = trim(trim($value, '"'));

        switch (strtolower($var)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            default:
                return $var;
        }
    }

    /**
     * @param        $str
     * @return array
     */
    private function parseEnvString($str)
    {
        $lines     = explode(PHP_EOL, $str);
        $variables = [];
        $re        = '/^(?<name>\w+)\s*=\s*(?<value>[^\n]+)$/';
        foreach ($lines as $line) {
            if (preg_match($re, $line, $match)) {
                $variables[$match['name']] = $this->formatEnvVar($match['value']);
            }
        }

        return $variables;
    }

    /**
     * @param                                   $contents
     * @param                                   $values
     * @return array|mixed|string|string[]|null
     */
    public function updateEnvIfNeeded($contents, $values)
    {
        foreach ($values as $name => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_numeric($value)) {
                $value = sprintf('%s', $value);
            } elseif (null === $value) {
                $value = sprintf('%s', 'null');
            } elseif (!empty($value)) {
                $value = sprintf('"%s"', $value);
            } else {
                $value = '';
            }

            $pattern = sprintf('/^%s *= *([^\n]*)$/m', $name);
            $need    = $name . '=' . $value;

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace(
                    $pattern,
                    $need,
                    $contents
                );
            } else {
                $contents = $contents . PHP_EOL . $need;
            }
        }

        return $contents;
    }

    /**
     * @return void
     */
    private function getCurrentPlatformVersion()
    {
        $constFile = $this->basePath('packages/platform/src/MetaFoxConstant.php');

        if (!file_exists($constFile)) {
            throw new \RuntimeException('Could not find ' . $constFile);
        }

        preg_match(
            '/(.*)public const VERSION\s*=\s*\'(?<version>[^\']+)\'/mi',
            file_get_contents($constFile),
            $matches
        );

        if (!empty($matches)) {
            $this->platformVersion = $matches['version'];
        }
    }

    /**
     * @param string $path
     *
     * @return string|false
     */
    static function mf_get_contents(string $path)
    {
        if (filter_var($path, FILTER_VALIDATE_URL) === false) {
            return file_get_contents($path);
        }

        if (!extension_loaded('curl')) {
            return false;
        }

        $curl = curl_init($path);

        if ($curl === false) {
            return false;
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $content = curl_exec($curl);

        $error = curl_errno($curl);

        if ($error) {
            return false;
        }

        curl_close($curl);

        if (!is_string($content)) {
            return false;
        }

        return $content;
    }
}
