<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

use Brick\VarExporter\VarExporter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\BanWord;
use MetaFox\Platform\Contracts\Input;
use MetaFox\Platform\Contracts\MetaFoxFileTypeInterface;
use MetaFox\Platform\Contracts\Output;
use MetaFox\Platform\Contracts\UploadFile;
use MetaFox\Platform\Contracts\UrlUtilityInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\PackageManager;
use MetaFox\SEO\Repositories\MetaRepositoryInterface;

if (!defined('STDIN')) {
    /*
     * this is required when run php in cli command from ci mode.
     */
    define('STDIN', fopen('php://stdin', 'r'));
}

if (!function_exists('set_installation_lock')) {
    /**
     * @deprecated 5.1.3
     */
    function set_installation_lock($name, $value)
    {
    }
}
if (!function_exists('get_installation_lock')) {
    /**
     * @deprecated 5.1.3
     */
    function get_installation_lock($name, $default = null)
    {
        return $default;
    }
}

if (!function_exists('export_to_file', )) {
    function export_to_file(string $filename, mixed $data, bool $lock = false): void
    {
        if (!is_dir(dirname($filename))) {
            @mkdir(dirname($filename), 0755, true);
        }

        app('files')
            ->put($filename, '<?php' . PHP_EOL . '/* this is auto generated file */'
                . PHP_EOL . VarExporter::export(
                    $data,
                    VarExporter::ADD_RETURN | VarExporter::INLINE_NUMERIC_SCALAR_ARRAY
                ), $lock);
    }
}

if (!function_exists('export_to_json_file', )) {
    function export_to_json_file(string $filename, mixed $data, bool $lock = false): void
    {
        if (!is_dir(dirname($filename))) {
            @mkdir(dirname($filename), 0755, true);
        }

        app('files')
            ->put(
                $filename,
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                $lock
            );
    }
}

if (!function_exists('export_to_csv', )) {
    function export_to_csv(string $filename, array $data, array $fields): void
    {
        if (!is_dir(dirname($filename))) {
            @mkdir(dirname($filename), 0755, true);
        }

        $stream = fopen($filename, 'w');

        if (!$stream) {
            throw new \RuntimeException('Could not open ' . $filename);
        }

        fputcsv($stream, $fields);

        foreach ($data as $row) {
            fputcsv($stream, array_map(function ($field) use ($row) {
                return $row[$field] ?? '';
            }, $fields));
        }

        fclose($stream);
    }
}

if (!function_exists('apiUrl')) {
    /**
     * Get api url return to client.
     *
     * @param  string $name
     * @param  array  $params
     * @param  bool   $absolute
     * @return string
     */
    function apiUrl(string $name, array $params = [], bool $absolute = false): string
    {
        $prefix =  config('app.mfox_api_route_prefix');

        if (!isset($params['ver'])) {
            $params['ver'] = 'v1';
        }

        if ($absolute) {
            return route($name, $params);
        }

        $url  = route($name, $params, false);

        if ('/' == $prefix || '' == $prefix) {
            return $url;
        }

        return substr($url, 7);
    }
}

if (!function_exists('downloadUrl')) {
    /**
     * Get api url return to client.
     *
     * @param  string $name
     * @param  array  $params
     * @return string
     */
    function downloadUrl(string $name, array $params = []): string
    {
        return config('app.mfox_api_root_url') . route($name, $params, false);
    }
}

if (!function_exists('arrayToTree')) {
    /**
     * Transform flat array to tree.
     *
     * @param array<string, mixed> $array
     * @param string               $keyName
     * @param string               $parentName
     * @param string               $childrenName
     *
     * @return array<string,mixed>
     */
    function arrayToTree(
        array $array,
        string $keyName = 'name',
        string $parentName = 'parent_name',
        string $childrenName = 'items'
    ): array {
        $grouped = [];
        foreach ($array as $node) {
            $grouped[$node[$parentName]][] = $node;
        }

        $fnBuilder = function ($siblings) use (&$fnBuilder, $grouped, $keyName, $childrenName) {
            foreach ($siblings as $k => $sibling) {
                $id = $sibling[$keyName];
                if (isset($grouped[$id])) {
                    $items                  = $fnBuilder($grouped[$id]);
                    $sibling[$childrenName] = $items;
                }
                $siblings[$k] = $sibling;
            }

            return $siblings;
        };

        if (!isset($grouped[''])) {
            return [];
        }

        return $fnBuilder($grouped['']);
    }
}

if (!function_exists('localCacheStore')) {
    /**
     * In order to categorize cache high load data to local
     * do not push all cache to network base cache system because it's phrase issue of 1Gb/s network traffic.
     * keep in internal
     * by default using `apcu` via apc wrapper as local cache data.
     *
     * @return Repository
     */
    function localCacheStore()
    {
        return Cache::store(config('cache.local_store', 'array'));
    }
}
if (!function_exists('user')) {
    function user(): User
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            //todo add phrase
            throw new AuthenticationException();
        }

        return $user;
    }
}

if (!function_exists('parse_input')) {
    /**
     * @return Input
     * @ignore
     * @codeCoverageIgnore
     * @deprecated
     */
    function parse_input(): Input
    {
        return resolve(Input::class);
    }
}

if (!function_exists('parse_output')) {
    /**
     * @return Output
     * @deprecated
     * @ignore
     * @codeCoverageIgnore
     */
    function parse_output(): Output
    {
        return resolve(Output::class);
    }
}

if (!function_exists('ban_word')) {
    /**
     * @return BanWord
     * @deprecated
     * @ignore
     * @codeCoverageIgnore
     */
    function ban_word(): BanWord
    {
        return resolve(BanWord::class);
    }
}

if (!function_exists('random_value')) {
    /**
     * Get random number.
     *
     * @param int $rate
     * @param int $under
     * @param int $over
     *
     * @return int
     * @ignore
     * @codeCoverageIgnore
     */
    function random_value(int $rate, int $under = 1, int $over = 0): int
    {
        return mt_rand(0, 100) < $rate ? $under : $over;
    }
}

if (!function_exists('random_privacy')) {
    /**
     * Get random privacy for test.
     *
     * @param string $ownerType
     *
     * @return int
     * @codeCoverageIgnore
     * @ignore
     */
    function random_privacy(string $ownerType = 'user'): int
    {
        $intRand = rand(0, 100);

        if ('user' != $ownerType) {
            return $intRand < 90 ? 1 : 3;
        }

        if ($intRand < 80) {
            return 0;
        } elseif ($intRand < 90) {
            return 1;
        } elseif ($intRand < 95) {
            return 2;
        } elseif ($intRand < 99) {
            return 4;
        }

        return 3;
    }
}

if (!function_exists('upload')) {
    /**
     * UploadFile helper.
     *
     * @return UploadFile
     */
    function upload(): UploadFile
    {
        return resolve(UploadFile::class);
    }
}

if (!function_exists('convertImagePath')) {
    /**
     * @param string $imagePath
     *
     * @return string
     */
    function convertImagePath(string $imagePath): string
    {
        return mb_pathinfo($imagePath, PATHINFO_DIRNAME)
            . DIRECTORY_SEPARATOR
            . mb_pathinfo($imagePath, PATHINFO_FILENAME)
            . '%s.'
            . mb_pathinfo($imagePath, PATHINFO_EXTENSION);
    }
}

if (!function_exists('getFilePath')) {
    /**
     * @param string $path
     * @param string $serverId
     *
     * @return string
     */
    function getFilePath(string $path, string $serverId): string
    {
        return Storage::disk($serverId)->path($path);
    }
}

if (!function_exists('isImageUrl')) {
    /**
     * @param string $url
     *
     * @return bool
     */
    function isImageUrl(string $url): bool
    {
        $result = getimagesize($url);
        if ($result != false && !empty($result[0]) && $result[1]) {
            return true;
        }

        return false;
    }
}

if (!function_exists('app_active')) {
    function app_active(string $appName): bool
    {
        return PackageManager::checkActive($appName);
    }
}

if (!function_exists('url_utility')) {
    function url_utility(): UrlUtilityInterface
    {
        return resolve(UrlUtilityInterface::class);
    }
}

if (!function_exists('faker_image_path')) {
    function faker_image_path(string $resourceName): string
    {
        $cacheName = 'faker_image_folder_' . $resourceName;

        $files = Cache::rememberForever($cacheName, function () use ($resourceName) {
            return Storage::disk('asset')->files($resourceName);
        });

        if (count($files)) {
            return Storage::disk('asset')->url(Arr::random($files));
        }

        return '';
    }
}

if (!function_exists('getDayOfLeapYearNumber')) {
    /**
     * @param Carbon $date
     *
     * @return int
     */
    function getDayOfLeapYearNumber(Carbon $date): int
    {
        $leapYearNumber = 0;

        if ($date->format('L') == '0' && $date->format('m') > 2) { //not leap year
            $leapYearNumber = 1;
        }

        return $leapYearNumber;
    }
}

if (!function_exists('is_running_unit_test')) {
    /**
     * Check is in CI or unitest mode.
     * @return bool
     */
    function is_running_unit_test(): bool
    {
        if (!defined('PHPUNIT_COMPOSER_INSTALL') && !defined('__PHPUNIT_PHAR__')) {
            // is not PHPUnit run
            return false;
        }

        return true;
    }
}

if (!function_exists('mf_get_contents')) {
    /**
     * @param string $path
     *
     * @return string|false
     */
    function mf_get_contents(string $path, array $options = [])
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

        $defaultOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_CONNECTTIMEOUT => 30,
        ];

        foreach ($options as $key => $value) {
            $defaultOptions[$key] = $value;
        }

        curl_setopt_array($curl, $defaultOptions);

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

if (!function_exists('database_driver')) {
    function database_driver(): string
    {
        // return DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
        // performance fix.
        return config('database.default');
    }
}

if (!function_exists('policy_authorize')) {
    /**
     * @param string $policyClass
     * @param string $policyMethod
     * @param mixed  ...$params
     *
     * @throws AuthorizationException
     */
    function policy_authorize(string $policyClass, string $policyMethod, ...$params): void
    {
        $check = policy_check($policyClass, $policyMethod, ...$params);

        if ($check === false) {
            $exception = new PermissionDeniedException();
            $exception->setContextData(array_merge([
                'policy_class'  => $policyClass,
                'policy_method' => $policyMethod,
            ], $params));

            throw $exception;
        }
    }
}

/*
 * $model to get policy
 */
if (!function_exists('gate_authorize')) {
    /**
     * @param User   $context
     * @param string $policyMethod
     * @param mixed  $model
     * @param mixed  ...$params
     *
     * @throws AuthorizationException
     */
    function gate_authorize(User $context, string $policyMethod, $model, ...$params): void
    {
        if (!$context->can($policyMethod, [$model, ...$params])) {
            throw new AuthorizationException();
        }
    }
}

if (!function_exists('policy_check')) {
    /**
     * @param string $policyClass
     * @param string $policyMethod
     * @param mixed  ...$params
     *
     * @return bool
     */
    function policy_check(string $policyClass, string $policyMethod, ...$params): bool
    {
        $policy = app($policyClass);

        return $policy->{$policyMethod}(...$params);
    }
}

if (!function_exists('calculatorExpiredDay')) {
    function calculatorExpiredDay(string $date): int
    {
        $day = Carbon::now()->diffInDays($date, false) + 1;

        return max($day, 0);
    }
}

if (!function_exists('file_type')) {
    function file_type(): MetaFoxFileTypeInterface
    {
        return resolve(MetaFoxFileTypeInterface::class);
    }
}

if (!function_exists('hasCaptcha')) {
    /**
     * @param string $settingName
     *
     * @return bool
     * @deprecated
     * @ignore
     * @codeCoverageIgnore
     */
    function hasCaptcha(string $settingName): bool
    {
        if (config('app.ci')) {
            return false;
        }

        return Settings::get($settingName, false);
    }
}

if (!function_exists('mb_pathinfo')) {
    /**
     * @param string     $path
     * @param int|string $options
     *
     * @return string[]|string
     * @ignore
     * @codeCoverageIgnore
     */
    function mb_pathinfo(string $path, $options = null)
    {
        $ret      = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
        $pathinfo = [];
        if (preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\\.([^\\.\\\\/]+?)|))[\\\\/\\.]*$%im', $path, $pathinfo)) {
            if (array_key_exists(1, $pathinfo)) {
                $ret['dirname'] = $pathinfo[1];
            }
            if (array_key_exists(2, $pathinfo)) {
                $ret['basename'] = $pathinfo[2];
            }
            if (array_key_exists(5, $pathinfo)) {
                $ret['extension'] = $pathinfo[5];
            }
            if (array_key_exists(3, $pathinfo)) {
                $ret['filename'] = $pathinfo[3];
            }
        }

        return match ($options) {
            PATHINFO_DIRNAME, 'dirname' => $ret['dirname'],
            PATHINFO_BASENAME, 'basename' => $ret['basename'],
            PATHINFO_EXTENSION, 'extension' => $ret['extension'],
            PATHINFO_FILENAME, 'filename' => $ret['filename'],
            default => $ret,
        };
    }
}

if (!function_exists('getFrontendAliasByEntityType')) {
    // todo: performance issues. stop doing it.
    function getFrontendAliasByEntityType(string $entityType): ?string
    {
        [,,, $packageId] = resolve(DriverRepositoryInterface::class)->loadDriver(
            Constants::DRIVER_TYPE_ENTITY,
            $entityType
        );

        return PackageManager::getFrontendAlias($packageId);
    }
}

if (!function_exists('getMobileAliasByEntityType')) {
    function getMobileAliasByEntityType(string $entityType): ?string
    {
        [,,, $packageId] = resolve(DriverRepositoryInterface::class)->loadDriver(
            Constants::DRIVER_TYPE_ENTITY,
            $entityType
        );

        return PackageManager::getAliasFor($packageId, 'mobile');
    }
}

if (!function_exists('getAliasByEntityType')) {
    function getAliasByEntityType(string $entityType): ?string
    {
        return PackageManager::getAliasForEntityType($entityType);
    }
}

if (!function_exists('array_trim_null')) {
    function array_trim_null(array $array, array $strips = []): array
    {
        $result = [];
        foreach ($array as $name => $value) {
            if (
                array_key_exists($name, $strips)
                and $value === $strips[$name]
            ) {
                continue;
            }
            if ($value === null) {
                continue;
            }
            $result[$name] = $value;
        }

        return $result;
    }
}

if (!function_exists('strip_tag_content')) {
    function strip_tag_content(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        $encoding = '<?xml encoding="UTF-8">';
        $dom      = new DOMDocument();
        $dom->loadHTML($encoding . $content, LIBXML_NOERROR);

        if (!$dom->hasChildNodes()) {
            return $content;
        }

        return htmlspecialchars_decode($dom->textContent);
    }
}

if (!function_exists('curl_download_file')) {
    /**
     * Downloads a file from a given URL or file path and saves it to a specified local path.
     * This function is memory efficient and works even when `allow_url_fopen` is turned off.
     *
     * @param string $savePath   The local file path where the downloaded file should be saved. NOTE: this method will only accept local path.
     * @param string $targetPath The URL or file path of the file to download.
     *
     * @return string     The local file path after successfully downloaded the file
     * @throws \Exception If the file could not be downloaded or saved.
     */
    function curl_download_file(string $savePath, string $targetPath): string
    {
        $curlHandle = curl_init($targetPath);

        $filePointer = fopen($savePath, 'w');
        if ($filePointer === false) {
            throw new Exception('Failed to open the file for writing');
        }

        curl_setopt($curlHandle, CURLOPT_FILE, $filePointer);    // Write the data to the file
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);     // Fail the request if an error occurs
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 3600);         // Timeout after 1 hour

        $result = curl_exec($curlHandle);

        if ($result === false) {
            $error = curl_error($curlHandle);
            curl_close($curlHandle);
            fclose($filePointer);
            throw new Exception('cURL Error: ' . $error);
        }

        curl_close($curlHandle);
        fclose($filePointer);

        return $savePath;
    }
}

if (!function_exists('stream_download_file')) {
    /**
     * Downloads a file from a given URL or file path and saves it to a specified local path.
     * This function is memory efficient and works only when `allow_url_fopen` is turned on.
     *
     * @param string          $savePath   The local file path where the downloaded file should be saved. NOTE: this method will only accept local path.
     * @param string|resource $targetPath The URL or file path of the file to download.
     *
     * @return string|false      The local file path after successfully downloaded the file or false on failure
     * @throws \RuntimeException If allow_url_fopen is not turn on.
     * @throws \Exception        If the file could not be downloaded or saved.
     */
    function stream_download_file(string $savePath, mixed $targetPath): string
    {
        if ('1' !== ini_get('allow_url_fopen')) {
            throw new \RuntimeException('`allow_url_fopen` must be turned on to use this function');
        }

        $isTargetStream = is_resource($targetPath) && get_resource_type($targetPath) === 'stream';

        $remoteStream = ($isTargetStream) ? $targetPath : fopen($targetPath, 'rb');
        if ($remoteStream === false) {
            throw new \Exception("Failed to read file at: $targetPath");
        }

        $localStream = fopen($savePath, 'wb');
        if ($localStream === false) {
            throw new \Exception("Failed to open file at: $savePath");
        }

        $bytesCopied = stream_copy_to_stream($remoteStream, $localStream);

        fclose($remoteStream);
        fclose($localStream);

        return $bytesCopied ? $savePath : false;
    }
}
