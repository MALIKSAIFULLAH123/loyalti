<?php

namespace MetaFox\App\Http\Controllers\Api\v1;

use App\ProcessHelper;
use App\Setup\State;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use MetaFox\App\Support\MetaFoxStore;
use MetaFox\Platform\Facades\RequestLifecycle;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use RuntimeException;
use ZipArchive;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\App\Http\Controllers\Api\UpgradeAdminController::$controllers;.
 */

/**
 * Class UpgradeAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class UpgradeAdminController extends ApiController
{
    /**
     * @var string
     */
    private string $projectRoot;

    /**
     * @var string
     */
    private string $logFile;

    /**
     * @var string
     */
    private string $envFile;

    /**
     * Check is installed.
     * @var bool
     */
    private bool $platformInstalled = false;

    /** @var array */
    private array $input = [];

    /**
     * @var string
     */
    private string $platformVersion = '5.0.0';

    /**
     * @var string|mixed|null
     */
    private string $platformInstalledVersion = '5.0.0';

    /**
     * @var array
     */
    private array $envVars = [];

    /**
     * @var string
     */
    private string $downloadFrameworkFolder;

    /**
     * @var string
     */
    private string $downloadAppFolder;

    /**
     * @var string
     */
    private string $extractAppFolder;

    /**
     * @var string
     */
    private string $frameworkFilename;

    /**
     * @var string
     */
    private string $extractFrameworkFolder;

    private ProcessHelper $processHelper;

    public function __construct()
    {
        $this->projectRoot             = base_path();
        $this->processHelper           = ProcessHelper::factory();
        $this->downloadFrameworkFolder = base_path('storage/install/download-framework');
        $this->extractFrameworkFolder  = base_path('storage/install/extract-framework');
        $this->extractAppFolder        = base_path('storage/install/extract-apps');
        $this->downloadAppFolder       = base_path('storage/install/download-apps');
        $this->logFile                 = base_path('storage/logs/installation-' . date('Y-m-d') . '.log');
        $this->frameworkFilename       = $this->downloadFrameworkFolder . '/metafox.zip';

        $this->ensureDir($this->downloadAppFolder);
        $this->ensureDir($this->extractAppFolder);
        $this->ensureDir($this->downloadFrameworkFolder);
        $this->ensureDir($this->extractFrameworkFolder);

        $this->getCurrentPlatformVersion();

        $this->envFile = implode(DIRECTORY_SEPARATOR, [$this->projectRoot, '.env']);
        if (
            $this->envFile &&
            file_exists($this->envFile) &&
            is_readable($this->envFile)
        ) {
            $this->envVars = $this->parseEnvString(mf_get_contents($this->envFile));

            $this->platformInstalledVersion = $this->getOnlyEnvVar('MFOX_APP_VERSION');
            $this->platformInstalled        = (bool) $this->getOnlyEnvVar('MFOX_APP_INSTALLED');
        }
    }

    public function stepStart(State $state)
    {
        $state->reset();

        $files = app('files');

        $files->deleteDirectories(base_path('storage/install'));

        $files->makeDirectory($this->downloadFrameworkFolder);
        $files->makeDirectory($this->extractFrameworkFolder);
        $files->makeDirectory($this->extractAppFolder);
        $version = MetaFox::getVersion();

        $latestVersion = $this->getDownloadableFrameworkVersion();

        $canUpgrade = version_compare($version, $latestVersion, '<');

        $recommendApps = $this->getRecommendAppsToUpgrades();

        $mainSteps    = [
            [
                'title'     => 'Prepare Backup',
                'id'        => 'prepare',
                'component' => 'app.step.PrepareUpgrade',
            ],
            count($recommendApps) ? [
                'title'     => 'Applications',
                'id'        => 'selectedApps',
                'component' => 'app.step.SelectApps',
                'props'     => [],
            ] : null,
            [
                'title'     => sprintf('Process Upgrade'),
                'id'        => 'download',
                'component' => 'app.step.ProcessUpgrade',
            ],
            [
                'title'     => sprintf('Done'),
                'id'        => 'done',
                'component' => 'app.step.UpgradeCompleted',
                'props'     => [
                    'baseUrl' => config('app.url'),
                ],
            ],
        ];
        $selectedApps = array_map(function ($app) {
            return [
                'identity'        => $app['identity'],
                'name'            => $app['name'],
                'version'         => $app['version'],
                'release_channel' => $app['version_detail']['release_channel'],
            ];
        }, $recommendApps);

        return $this->success([
            'baseUrl'             => config('app.url'),
            'loaded'              => true,
            'currentVersion'      => $version,
            'latestVersion'       => $latestVersion,
            'canUpgrade'          => $canUpgrade,
            'recommendAppsLoaded' => true,
            'recommendApps'       => $recommendApps,
            'selectedApps'        => $selectedApps,
            'steps'               => array_values(array_filter($mainSteps, function ($step) {
                return (bool) $step;
            })),
        ]);
    }

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

    protected function stepRestartQueueWorker(State $state)
    {
        $this->processHelper->callPhp('artisan queue:restart', [], false);

        $state->markDone();

        return $this->success([]);
    }

    protected function stepDownloadFramework(State $state)
    {
        if (file_exists($this->frameworkFilename)) {
            return $this->success([]);
        }

        $verifier = function () {
            return $this->ensureIsZipFile($this->frameworkFilename);
        };

        if (($result = $state->checkInProgress($verifier))) {
            return $this->success($result);
        }

        $state->markProcessing();

        $channel = config('app.mfox_app_channel');

        app(MetaFoxStore::class)->downloadFramework($channel, $this->frameworkFilename);

        return $this->success([]);
    }

    protected function stepExtractFramework(State $state)
    {
        if (!file_exists($this->frameworkFilename)) {
            return $this->error('Failed loading ' . $this->frameworkFilename);
        }

        $archive = new ZipArchive();

        if (!$archive->open($this->frameworkFilename, ZipArchive::RDONLY)) {
            return $this->error('Failed loading archive ' . $this->frameworkFilename);
        }

        $found = 'upload.zip';

        if (false === $archive->getFromName($found)) {
            $found = rtrim($archive->getNameIndex(0), '/') . '/' . $found;
        }

        $archive->extractTo($this->extractFrameworkFolder);

        $archive->close();

        $uploadFilename = $this->extractFrameworkFolder . '/' . $found;

        if (!file_exists($uploadFilename)) {
            return $this->error('Failed loading archive ' . $uploadFilename);
        }

        $archive = new ZipArchive();

        if (!$archive->open($uploadFilename, ZipArchive::RDONLY)) {
            return $this->error('Failed loading archive ' . $uploadFilename);
        }

        $archive->extractTo(base_path());

        $archive->close();

        if (config('app.enable_octane')) {
            Artisan::call('octane:reload');
        }

        return $this->success([]);
    }

    protected function stepClean(State $state)
    {
        $this->processHelper->callPhp('artisan optimize:clear');

        $files = app('files');
        $files->deleteDirectories(base_path('storage/install'));

        return $this->success([]);
    }

    protected function ensureDir($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Get existing environment from env var only.
     * @param string $name
     * @param mixed  $default
     * @return mixed|null
     */
    protected function getOnlyEnvVar(string $name, mixed $default = null)
    {
        return array_key_exists($name, $this->envVars) ? $this->envVars[$name] : $default;
    }

    /**
     * @param string $method
     * @param State  $state
     * @return mixed
     */
    protected function executeStep(string $method, State $state)
    {
        $this->log(sprintf('Start %s (%s)', __METHOD__, $method));

        if (!method_exists($this, $method)) {
            return $this->error('Step not found');
        }

        return $this->{$method}($state);
    }

    public function execute(string $step, Request $request)
    {
        $this->input = $request->all();
        $this->log('---------------------------------------------');
        $this->log(sprintf('Start %s', __METHOD__));

        ignore_user_abort(true);
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $sid = array_key_exists('sid', $this->input) ? $this->input['sid'] : $step;
        if (!$sid) {
            $sid = $step;
        }

        $state = State::factory($sid);

        try {
            $step = 'step' . $this->studlyCase($step);

            $state->markStartIfNeeded();

            $data = $this->executeStep($step, $state);

            $this->log(sprintf('End %s', __METHOD__));

            return $data;
        } catch (Exception $error) {
            // need to retry or not.
            $attemps = $state->get('attemps', 0);
            $retries = $state->get('retries', 0);

            if ($retries <= $attemps) {
                $this->log("Retry $step");
                $state->markRetried(++$retries);

                return $this->success([
                    'retry' => true,
                ]);
            }

            $message = $error->getMessage();
            $this->alert([
                'title'   => 'Alert',
                'message' => $message,
                'debug'   => $error->getTraceAsString(),
            ]);

            return $this->error($error->getMessage(), 400);
        }
    }

    protected function stepWaitFrontend(State $state)
    {
        if (($result = $state->checkInProgress())) {
            return $this->success($result);
        }

        // where to get JobId.
        try {
            $this->processHelper->callPhp('artisan frontend:build --check', [], true);
        } catch (\Throwable $exception) {
            $this->log($exception->getMessage());
        }

        return $this->success(['retry' => true]);
    }

    /**
     * @link /install/build-frontend
     */
    protected function stepBuildFrontend(State $state)
    {
        $this->processHelper->callPhp('artisan frontend:build', [], false);

        return $this->success([]);
    }

    protected function ensureWritable($dirOrFileName)
    {
        $path = $this->projectRoot . $dirOrFileName;

        if (!is_dir($path) && !file_exists($path)) {
            return is_writable(dirname($path));
        }

        if (is_writable($path)) {
            return true;
        }

        return is_writable($path);
    }

    /**
     * @return array
     */
    protected function getRecommendations()
    {
        $this->log(sprintf('Start %s', __METHOD__));
        $hasAPC = extension_loaded('apc') || extension_loaded('apcu');

        $items = [
            [
                'label'    => 'APC User Cache',
                'value'    => $hasAPC,
                'url'      => 'https://www.php.net/manual/en/book.apcu.php',
                'severity' => 'warning',
            ],
            [
                'label'    => 'Redis Cache',
                'value'    => class_exists('Redis'),
                'url'      => 'https://github.com/phpredis/phpredis',
                'severity' => 'warning',
            ],
            [
                'label'    => 'ImageMagick PHP Extension',
                'value'    => extension_loaded('imagick'),
                'url'      => 'https://www.php.net/manual/en/book.imagick.php',
                'severity' => 'warning',
            ],
        ];

        return [
            'title' => 'Recommendations',
            'items' => $items,
        ];
    }

    protected function discoverExistedPackages(): array
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
                $data = json_decode(mf_get_contents($file), true);
                if (
                    !isset($data['extra']) ||
                    !isset($data['extra']['metafox'])
                    || !is_array($data['extra']['metafox'])
                ) {
                    return;
                }
                $extra = $data['extra']['metafox'];

                $packages[$data['name']] = [
                    'name'    => $data['name'],
                    'version' => $data['version'],
                    'path'    => trim(substr(dirname($file), strlen($basePath)), DIRECTORY_SEPARATOR),
                    'core'    => $extra['core'] ?? false,
                ];
            } catch (Exception $exception) {
                Log::channel('dev')->debug($exception->getMessage());
            }
        });

        return $packages;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function studlyCase(string $name)
    {
        return $name ? str_replace(' ', '', ucwords(preg_replace('#([^a-zA-Z\d]+)#m', ' ', $name))) : '';
    }

    protected function stepDownloadApp(State $state)
    {
        $this->log(sprintf('Start %s', __METHOD__));
        $id              = Arr::get($this->input, 'id');
        $version         = Arr::get($this->input, 'version');
        $platformVersion = Arr::get($this->input, 'platformVersion');
        $channel         = Arr::get($this->input, 'release_channel');

        $filename = sprintf('%s/%s.zip', $this->downloadAppFolder, preg_replace("#\W+#", '-', $id));

        $verifier = function () use ($filename) {
            return $this->ensureIsZipFile($filename);
        };

        if (($result = $state->checkInProgress($verifier))) {
            return $this->success($result);
        }

        $state->markProcessing();

        $json = $this->httpRequest(config('app.store_api_url') . '/install', 'post', [
            'id'              => $id,
            'version'         => $platformVersion,
            'app_version'     => $version,
            'version_type'    => 'source',
            'release_channel' => $channel,
        ]);

        if (!$json['download']) {
            throw new RuntimeException('Could not get download url');
        }

        $temporary = $filename . '.temp';
        register_shutdown_function(function () use ($temporary, $filename) {
            if (file_exists($temporary)) {
                copy($temporary, $filename);
                @unlink($temporary);
            }
        });

        // fix issue timeout request etc. request limit 15 sec but download need 30 sec.
        $this->processHelper->downloadFile($json['download'], $temporary);

        return $this->success([]);
    }

    protected function log($message, $level = 'DEBUG')
    {
        $message = sprintf('[%s] production:%s: %s', strtoupper($level), date('Y-m-d H:i:s'), $message);

        file_put_contents($this->logFile, $message . PHP_EOL, FILE_APPEND);
    }

    protected function stepProcessUpgrade(State $state)
    {
        $canUpgrade    = Arr::get($this->input, 'canUpgrade');
        $latestVersion = Arr::get($this->input, 'latestVersion');

        $selectedApps             = Arr::get($this->input, 'selectedApps');
        $downloadFrameworkVersion = $this->getDownloadableFrameworkVersion();
        $upgradeSteps             = [];

        // remove public/install to help "api/v1/instal" continue process
        $this->ensureDir(base_path('public/install'));

        if ($canUpgrade) {
            $upgradeSteps['download-framework'] = [
                'title'      => sprintf('Download MetaFox - v%s', $latestVersion),
                'dataSource' => [
                    'apiUrl'    => '/admincp/app/upgrade/download-framework',
                    'apiMethod' => 'GET',
                ],
            ];
        }

        foreach ($selectedApps as $app) {
            $sid                = 'download-app_' . $app['identity'];
            $upgradeSteps[$sid] = [
                'attemps'    => 2,
                'title'      => 'Download ' . $app['name'] . ' - v' . $app['version'],
                'dataSource' => [
                    'apiUrl'    => '/admincp/app/upgrade/download-app?sid=' . $sid,
                    'apiMethod' => 'POST',
                ],
                'data'       => [
                    'version'         => $app['version'],
                    'id'              => $app['identity'],
                    'platformVersion' => $downloadFrameworkVersion,
                    'release_channel' => Arr::get($app, 'release_channel'),
                ],
            ];
        }

        if ($canUpgrade) {
            $upgradeSteps['extract-framework'] = [
                'title'      => 'Extract Framework',
                'dataSource' => [
                    'apiUrl' => '/admincp/app/upgrade/extract-framework',
                ],
            ];
        }

        if (count($selectedApps)) {
            $upgradeSteps['extract-apps'] = [
                'title'      => 'Extract Apps',
                'dataSource' => [
                    'apiUrl' => '/api/v1/install?step=extract-apps',
                ],
            ];
        }

        $upgradeSteps['composer-install'] = [
            'title'      => 'Update Dependencies',
            'dataSource' => [
                'apiUrl' => '/api/v1/install?step=composer-install',
            ],
        ];

        $upgradeSteps['verify-composer-installed'] = [
            'title'      => 'Verify Dependencies',
            'dataSource' => [
                'apiUrl'    => '/api/v1/install?step=verify-composer-installed',
                'apiMethod' => 'GET',
            ],
        ];

        $upgradeSteps['metafox-upgrade'] = [
            'title'      => 'Upgrade',
            'dataSource' => [
                'apiUrl' => '/api/v1/install?step=metafox-upgrade',
            ],
        ];

        $upgradeSteps['clean-cache']          = [
            'title'      => 'Clean Files',
            'dataSource' => [
                'apiUrl'    => '/api/v1/install?step=clean-cache',
                'apiMethod' => 'GET',
            ],
        ];
        $upgradeSteps['restart-queue-worker'] = [
            'title'      => 'Restart Queues',
            'attemps'    => 2,
            'dataSource' => [
                'apiUrl'    => '/api/v1/install?step=restart-queue-worker',
                'apiMethod' => 'GET',
            ],
        ];

        $upgradeSteps[] = [
            'title'      => 'Build Frontend',
            'dataSource' => [
                'apiUrl'    => '/api/v1/install?step=build-frontend',
                'apiMethod' => 'GET',
            ],
        ];

        $upgradeSteps['wait-frontend'] = [
            'title'      => 'Waiting for Frontend',
            'dataSource' => [
                'apiUrl'    => '/api/v1/install?step=wait-frontend',
                'apiMethod' => 'GET',
            ],
        ];

        $upgradeSteps['cleanup'] = [
            'title'      => 'Clear temporary file',
            'dataSource' => [
                'apiUrl'    => '/api/v1/install?step=cleanup',
                'apiMethod' => 'GET',
            ],
        ];
        $upgradeSteps['up-site'] = [
            'dataSource' => [
                'apiUrl'    => '/api/v1/install?step=up-site',
                'apiMethod' => 'GET',
            ],
            'title'      => 'Launch Site',
        ];

        return $this->success([
            'upgradeSteps' => array_values(array_filter($upgradeSteps, function ($step) {
                return (bool) $step;
            })),
        ]);
    }

    protected function stepCleanup(State $state)
    {
        $this->processHelper->exec(sprintf('rm -rf %s/storage/install', $this->projectRoot));
        $this->processHelper->exec(sprintf('rm -rf %s/public/install', $this->projectRoot));

        $state->reset();

        return $this->success([]);
    }

    /**
     * Get collections of app to upgrades.
     */
    protected function getRecommendAppsToUpgrades()
    {
        $this->log(sprintf('Start %s', __METHOD__));

        $existedApps = $this->discoverExistedPackages();

        $params = [
            'version'         => $this->getDownloadableFrameworkVersion(),
            'release_channel' => config('app.mfox_app_channel'),
        ];

        $headers = [
            'Accept: application/json',
        ];

        $payload = $this->httpRequest(
            config('app.store_api_url') . '/purchased',
            'GET',
            $params,
            $headers
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

    protected function getDownloadableFrameworkVersion()
    {
        if (config('app.mfox_store_downloadable_framework_version')) {
            return config('app.mfox_store_downloadable_framework_version');
        }

        $json = $this->httpRequest(config('app.store_api_url') . '/phpfox-download', 'post');

        if ($json) {
            return $json['version'];
        }

        return $this->platformVersion;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array  $params
     * @param array  $headers
     * @return mixed
     */
    protected function httpRequest(string $url, string $method, array $params = [], array $headers = [])
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

        $licenseId  = config('app.mfox_license_id');
        $licenseKey = config('app.mfox_license_key');
        $headers[]  = 'Authorization: Basic ' . base64_encode($licenseId . ':' . $licenseKey);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);

        if ($method != 'GET') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }

        $response = curl_exec($curl);

        curl_close($curl);

        if ($response === false) {
            $message = sprintf('%s: %s', 'An error has occurred when trying to retrieve data from Store API', curl_error($curl));

            $this->log($message);

            throw new \RuntimeException($message);
        }

        $this->log($response);

        $response = trim($response);

        $response = json_decode($response, true);

        if (isset($response['error']) && $response['error']) {
            throw new RuntimeException($response['error']);
        }

        $this->log(sprintf('End %s', __METHOD__));

        return $response['data'] ?? $response;
    }

    protected function formatEnvVar($value)
    {
        $var = trim(trim($value, '"'));

        return match (strtolower($var)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            default            => $var,
        };
    }

    protected function parseEnvString($str)
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

    protected function getCurrentPlatformVersion()
    {
        $constFile = implode(
            DIRECTORY_SEPARATOR,
            [$this->projectRoot, 'packages', 'platform', 'src', 'MetaFoxConstant.php']
        );

        if (!file_exists($constFile)) {
            throw new RuntimeException('Could not find ' . $constFile);
        }

        preg_match(
            '/(.*)public const VERSION\s*=\s*\'(?<version>[^\']+)\'/mi',
            mf_get_contents($constFile),
            $matches
        );

        if (!empty($matches)) {
            $this->platformVersion = $matches['version'];
        }
    }
}
