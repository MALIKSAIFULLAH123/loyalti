<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\App\Support;

use App\ProcessHelper;
use Carbon\Carbon;
use Composer\Package\Version\VersionParser;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Core\Support\Facades\License;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\PackageManager;

/**
 * Class MetaFoxStore.
 */
class MetaFoxStore
{
    private const TIMEOUT = 30;

    private ?string $licenseId;

    private ?string $licenseKey;

    private string $baseUrl;

    private array $apiStoreHeaders = [
        'X-Product'     => 'metafox',
        'X-Namespace'   => 'phpfox',
        'X-API-Version' => '1.1',
    ];

    private array $apiClientAreaHeaders = [
        'X-Product'     => 'metafox',
        'X-Namespace'   => 'expert',
        'X-API-Version' => '1.1',
    ];

    private PackageRepositoryInterface $packageRepository;

    public function __construct()
    {
        $this->baseUrl           = config('app.store_api_url');
        $this->licenseId         = Settings::get('core.license.id', '');
        $this->licenseKey        = Settings::get('core.license.key', '');
        $this->packageRepository = resolve('core.packages');
    }

    /**
     * browse.
     *
     * @param  array $params
     * @return array
     */
    public function browse(array $params = []): array
    {
        $data = $this->productBrowse($params);

        $identities    = collect($data)->pluck('identity')->toArray();
        $localPackages = $this->packageRepository->getModel()->newModelQuery()
            ->whereIn('name', $identities)
            ->get()
            ->collect()
            ->keyBy('name')
            ->toArray();

        $data = collect($data)
            ->filter(function ($item) use ($localPackages) {
                if (!is_array($item)) {
                    return false;
                }

                $identity     = Arr::get($item, 'identity');
                $localPackage =  Arr::get($localPackages, $identity);

                return !is_array($localPackage) || !Arr::get($localPackage, 'is_installed');
            })
            ->values()
            ->toArray();

        foreach ($data as $index => $item) {
            $data[$index]['module_name']   = 'app';
            $data[$index]['resource_name'] = 'app_store_product';

            $identity    = Arr::get($item, 'identity');
            $package     = $identity ? Arr::get($localPackages, $identity) : [];
            $isInstalled = Arr::get($package, 'is_installed') ?? false;

            $itemAction = [
                'label'   => $isInstalled ? __p('app::phrase.installed') : __p('app::phrase.get_app'),
                'variant' => $isInstalled ? 'outlined' : 'contained',
                'size'    => 'small',
            ];

            $version = Arr::get($item, 'version');
            if (empty($version) && !$isInstalled) {
                Arr::set($itemAction, 'label', __p('app::phrase.app_not_compatible'));
                Arr::set($itemAction, 'disabled', true);
            }

            $data[$index]['action_button']   = $itemAction;
            $data[$index]['rated']           = $item['rated'] ?? 0;
            $data[$index]['total_rated']     = $item['total_rated'] ?? 0;
            $data[$index]['total_installed'] = $item['total_installed'] ?? 0;
            $data[$index]['total_reviews']   = $item['total_reviews'] ?? 0;
        }

        return $data;
    }

    public function purchased(): array
    {
        $response = null;
        try {
            $response = Http::asJson()
            ->timeout(self::TIMEOUT)
            ->withHeaders($this->apiStoreHeaders)
            ->withBasicAuth($this->licenseId, $this->licenseKey)
            ->get($this->getPurchasedApiUrl())
            ->json();
        } catch (\Throwable $error) {
            Log::info($error->getMessage());
        }

        $data = $response['data'] ?? [];

        foreach ($data as $index => $item) {
            $data[$index]['module_name']   = 'app';
            $data[$index]['resource_name'] = 'app_store_product';

            $this->verifyProductStatus($item);
        }

        return Arr::sort($data, function (array $item) {
            return $item['name'];
        });
    }

    private function verifyProductStatus(?array $item): void
    {
        if (!$item) {
            return;
        }

        $package        = $this->packageRepository->findByName($item['identity']);
        $expirationDate = Arr::get($item, 'expired_at');

        if ($package) {
            $package->latest_version = $item['version'];
            $package->store_id       = Arr::get($item, 'id') ?? 0;
            $package->expired_at     = !empty($expirationDate) ? $expirationDate : null;
            $package->saveQuietly();
        }
    }

    public function show($id)
    {
        $endpoint = $this->baseUrl . '/product/' . $id;
        $params   = [
            'version'         => MetaFox::getVersion(),
            'release_channel' => config('app.mfox_app_channel'),
        ];

        $request = null;
        try {
            $request  = Http::asJson()
            ->timeout(self::TIMEOUT)
            ->withHeaders($this->apiStoreHeaders)
            ->withBasicAuth($this->licenseId, $this->licenseKey)
            ->get($endpoint, $params);
        } catch (\Throwable $error) {
            Log::info($error->getMessage());
        }

        if (!$request) {
            return [];
        }

        Log::channel('installation')
            ->debug('Show detail ' . $endpoint, $params);

        $json = $request->json();

        Log::channel('dev')->debug('product detail ', $json);

        $data                   = $json['data'] ?? [];
        $storeVersion           = Arr::get($data, 'version');
        $storePriceType         = Arr::get($data, 'pricing_type', 'perpetual');

        $data                         = Arr::add($data, 'id', $id);
        $data['module_name']          = 'app';
        $data['resource_name']        = 'app_store_product';
        $data['purchase_url']         = $this->purchaseUrl($id);
        $data['version']              = !empty($storeVersion) ? $storeVersion : 'N/A';
        $data['is_installed']         = false;
        $data['label_install']        = __p('app::phrase.install');
        $data['internal_admincp_url'] = null;
        if ($storePriceType === 'subscription') {
            $data['pricing_type_label'] = __p('app::phrase.price_type_subscription');
        }

        $package     = $this->packageRepository->findByName($data['identity'] ?? '');
        $isInstalled = $package?->is_installed;
        $isActive    = $package?->is_active;

        if (!$package) {
            $data['bundle_status'] = 'unknown';

            return $data;
        }

        $data['bundle_status']   = $package->bundle_status;
        $data['current_version'] = $package->version;

        $versonStoreToLocal = version_compare($storeVersion, $package->version);
        if ($isInstalled) {
            $data['is_installed']   = true;
            $data['label_install']  = match ($versonStoreToLocal) {
                1       => __p('app::phrase.upgrade'),
                default => __p('app::phrase.reinstall'),
            };

            /*
             * Case 1: Store version < package version =>  button disabled, text = 'reinstall'
             * Case 2: Store version == package version => button active, text = 'reinstall'
             * Case 3: Store version > package version => button active, text = 'upgrade'
             */
            $data['can_upgrade'] = match ($versonStoreToLocal) {
                -1      => false,
                default => true,
            };
        }

        if ($isInstalled && $isActive) {
            $data['internal_admincp_url'] = $package->internal_admin_url ? [
                'label' => __p('core::phrase.app_settings'),
                'url'   => $package->internal_admin_url,
            ] : null;
        }

        return $data;
    }

    public function purchaseUrl($id): ?string
    {
        $returnUrl = config('app.url') . '/admincp/app/store/product/' . $id;

        $json = [];
        try {
            $request = Http::asJson()
            ->timeout(self::TIMEOUT)
            ->withHeaders($this->apiStoreHeaders)
            ->withBasicAuth($this->licenseId, $this->licenseKey)
            ->get($this->baseUrl . '/purchase/' . $id, ['return_url' => $returnUrl]);

            $json = $request->json();
        } catch (\Throwable $error) {
            Log::info($error->getMessage());
        }

        return Arr::get($json, 'data.purchase_url');
    }

    public function publishToStore(string $package, string $version, string $name, string $file, string $channel, ?array $require = []): void
    {
        $token           = config('app.mfox_store_api_token');
        $coreConstraints = $this->parseVersionConstraints(Arr::get($require, 'metafox/core'));

        if (!$token) {
            Log::channel('dev')->debug('missing environment variable "MFOX_STORE_API_TOKEN"');

            return;
        }

        $serviceUrl = $this->baseUrl . '/product/version/add';

        $fileSize = number_format(filesize($file) / 1024 / 1024, 2);

        Log::channel('dev')->debug(sprintf(
            'Upload "%s", filename: %s, filesize: %s Mb',
            $serviceUrl,
            $name,
            $fileSize
        ));

        $storeId = Arr::get(PackageManager::getComposerJson($package), 'extra.metafox.store_id');

        if (!$storeId || $storeId == '0') {
            $storeId = $package;
        }

        $request = Http::asMultipart()
            ->withToken($token)
            ->withHeaders($this->apiClientAreaHeaders)
            ->attach('version_package', mf_get_contents($file), $name)
            ->post($serviceUrl, [
                'version_type'     => 'source',
                'version'          => $version,
                'description'      => '---',
                'release_channel'  => $channel,
                'id'               => $storeId,
                'core_min_version' => Arr::get($coreConstraints, 'min_version'),
                'core_max_version' => Arr::get($coreConstraints, 'max_version'),
            ]);

        $json = $request->json();

        if (!is_array($json)) {
            Log::channel('dev')->debug($request->body());
        } elseif (Arr::get($json, 'status') == 'failed') {
            Log::channel('dev')->debug(json_encode($json, JSON_PRETTY_PRINT));
        } else {
            Log::channel('dev')->debug(json_encode($json, JSON_PRETTY_PRINT));
        }
    }

    public function downloadProduct(string $name, ?string $version, string $channel): string
    {
        Log::channel('installation')->debug(sprintf('Downloading %s:%s', $name, $version));

        $post = [
            'version'         => MetaFox::getVersion(),
            'app_version'     => $version,
            'version_type'    => 'source',
            'release_channel' => $channel,
            'id'              => $name,
        ];

        if ($version) {
            $post['app_version'] = $version;
        }

        $json = [];
        try {
            $request = Http::asJson()
                ->timeout(self::TIMEOUT)
                ->withBasicAuth($this->licenseId, $this->licenseKey)
                ->withHeaders($this->apiStoreHeaders)
                ->post($this->baseUrl . '/install', $post);
            $json = $request->json();
        } catch (\Throwable $error) {
            Log::info($error->getMessage());
        }

        $json = $json['data'] ?? $json;

        $downloadUrl = $json['download'] ?? null;

        Log::channel('installation')->debug(sprintf('Download product url %s', $downloadUrl));

        if (!$downloadUrl) {
            throw new InvalidArgumentException('Could not download the product.' . PHP_EOL . $request->body());
        }

        $filename = tempnam(sys_get_temp_dir(), 'metafox-product-' . $json['id']) . '.zip';
        $stream   = fopen($filename, 'w');

        if (!$stream) {
            throw new \InvalidArgumentException(sprintf('Failed opening stream "%s" to write.', $filename));
        }

        $content = mf_get_contents($downloadUrl);

        if (!$content) {
            throw new \InvalidArgumentException(sprintf('Failed opening download "%s".', $downloadUrl));
        }

        if (!fwrite($stream, $content)) {
            throw new \InvalidArgumentException(sprintf('Failed writing to "%s".', $filename));
        }

        fclose($stream);

        return $filename;
    }

    public function downloadFramework(string $channel, string $filename)
    {
        $downloadUrl =  config('app.mfox_downloadable_framework_url');

        $temporary = $filename . '.temp';

        // migration.
        register_shutdown_function(function () use ($filename, $temporary) {
            if (file_exists($temporary)) {
                @copy($temporary, $filename);
                @unlink($temporary);
            }
            Log::channel('dev')->debug('downloadFramework');
        });

        if (!$downloadUrl) {
            $json = [];
            try {
                $json = Http::asJson()
                    ->timeout(self::TIMEOUT)
                    ->withBasicAuth($this->licenseId, $this->licenseKey)
                    ->withHeaders($this->apiStoreHeaders)
                    ->post($this->baseUrl . '/phpfox-download')
                    ->json();
            } catch (\Throwable $error) {
                Log::info($error->getMessage());
            }

            $downloadUrl = $json['download'] ?? null;
        }

        if (!$downloadUrl) {
            throw new \RuntimeException('Missing downloadUrl.');
        }

        ProcessHelper::downloadFile($downloadUrl, $temporary);
    }

    public function verifyExpiredProducts(): void
    {
        $response = [];

        try {
            $response = Http::asJson()
            ->timeout(self::TIMEOUT)
            ->withBasicAuth($this->licenseId, $this->licenseKey)
            ->withHeaders($this->apiStoreHeaders)
            ->get($this->getPurchasedApiUrl())
            ->json();
        } catch (\Throwable $error) {
            Log::info($error->getMessage());
        }

        $data = $response['data'] ?? [];
        foreach ($data as $item) {
            try {
                $this->verifyProduct($item);
            } catch (\Exception) {
                // don't break error
            }
        }
    }

    public function getLicenseDetails(string $licenseId, string $licenseKey, string $installPath = '/'): array
    {
        $json     = [];
        $response = null;

        try {
            $response = Http::asJson()
            ->timeout(self::TIMEOUT)
            ->withBasicAuth($licenseId, $licenseKey)
            ->withHeaders($this->apiStoreHeaders)
            ->post($this->baseUrl . '/verify', [
                'url'               => config('app.url'),
                'installation_path' => $installPath,
            ]);

            $json = $response->json();
        } catch (\Throwable $error) {
            Log::info($error->getMessage());
        }

        Log::channel('installation')->debug($response?->body());

        return $json;
    }

    public function verifyLicense(string $licenseId, string $licenseKey, string $installPath = '/'): bool
    {
        $json     = [];
        $response = null;

        try {
            $response = Http::asJson()
            ->timeout(self::TIMEOUT)
            ->withBasicAuth($licenseId, $licenseKey)
            ->withHeaders($this->apiStoreHeaders)
            ->post($this->baseUrl . '/verify', [
                'url'               => config('app.url'),
                'installation_path' => $installPath,
            ]);

            $json = $response->json();
        } catch (\Throwable $error) {
            Log::info($error->getMessage());
        }

        Log::channel('installation')->debug($response?->body());

        if (Arr::get($json, 'valid')) {
            return true;
        }

        throw new \InvalidArgumentException($response?->body() ?? '');
    }

    private function verifyProduct(?array $item): void
    {
        if (empty($item)) {
            return;
        }

        Log::channel('dev')->debug('verifyProduct', $item);

        $identity       = $item['identity'];
        $package        = $this->packageRepository->findByName($identity);
        $expirationDate = Arr::get($item, 'expired_at');

        if (!$package) {
            return;
        }

        $package->is_purchased   = 1;
        $package->latest_version = $item['version'];
        $package->expired_at     = !empty($expirationDate) ? $expirationDate : null;
        $package->saveQuietly();
    }

    public function getInstalledListByType(string $type)
    {
        return array_filter(array_map(function ($data) use ($type) {
            if ($data['type'] === $type && !$data['core']) {
                return $data['name'];
            }

            return null;
        }, config('metafox.packages', [])), function ($data) {
            return (bool) $data;
        });
    }

    public function verifyLatestVersions()
    {
        $apps      = $this->getInstalledListByType('app');
        $themes    = $this->getInstalledListByType('theme');
        $languages = $this->getInstalledListByType('language');
        $pdo       = DB::connection()->getPdo();
        $json      = null;
        try {
            $response = Http::asJson()
            ->timeout(self::TIMEOUT)
            ->withBasicAuth($this->licenseId, $this->licenseKey)
            ->withHeaders($this->apiStoreHeaders)
            ->post($this->baseUrl . '/products', [
                'products' => [
                    'apps'      => [...$apps, 'metafox/framework'],
                    'themes'    => $themes,
                    'languages' => $languages,
                ],
                'server' => [
                    'php_version' => phpversion(),
                    'db_engine' => $pdo?->getAttribute(\PDO::ATTR_DRIVER_NAME),
                    'db_version' => $pdo?->getAttribute(\PDO::ATTR_SERVER_VERSION),
                ],
                'version' => MetaFox::getVersion(),
            ]);

            $json = $response->json();
        } catch (\Throwable $error) {
            Log::info($error->getMessage());
        }

        if (!$json) {
            return;
        }

        Log::channel('dev')->debug($response->body());

        $this->updateLatestVersion(Arr::get($json, 'products.apps'));
    }

    private function updateLatestVersion($apps)
    {
        if (empty($apps)) {
            return;
        }

        foreach ($apps as $name => $app) {
            $package = $this->packageRepository->findByName($name);
            if (
                $package
                && isset($app['version'])
                && $package->latest_version != $app['version']
            ) {
                $package->latest_version = $app['version'];
                $package->saveQuietly();
            }
        }
    }

    public function verifyMetaFoxInfo()
    {
        try {
            // if installation for testing without info.
            if (!$this->licenseId || !$this->licenseKey || !$this->baseUrl) {
                return;
            }

            $response = Http::asJson()
                ->timeout(self::TIMEOUT)
                ->withBasicAuth($this->licenseId, $this->licenseKey)
                ->withHeaders($this->apiStoreHeaders)
                ->post($this->baseUrl . '/info');

            $json = $response->json();

            if (!$json) {
                return;
            }

            $status = Arr::get($json, 'status');
            if ('failed' === $status) {
                License::deactivate();

                return;
            }

            $expired = Arr::get($json, 'renewal_expired_date');

            if ($expired) {
                $expired = Carbon::createFromTimestamp($expired);
            } else {
                $expired = null;
            }

            Settings::save([
                'core.license.expired_at'      => $expired,
                'core.platform.latest_version' => Arr::get($json, 'eligible_phpfox_version'),
            ]);

            Artisan::call('cache:reset');
        } catch (\Exception $exception) {
            Log::channel('dev')->debug($exception->getMessage());
        }
    }

    /**
     * @return array<int, mixed>
     */
    public function getSearchFormData(): array
    {
        return localCacheStore()->rememberForever(__CLASS__ . '_search_form', function () {
            $data = [];
            try {
                $response = Http::asJson()
                ->timeout(self::TIMEOUT)
                ->withBasicAuth($this->licenseId, $this->licenseKey)
                ->withHeaders($this->apiStoreHeaders)
                ->get($this->baseUrl . '/search/form');

                $data = $response->json('data') ?? [];
            } catch (\Throwable $error) {
                Log::info($error->getMessage());
            }

            return $data;
        });
    }

    /**
     * @return array<string>
     */
    public function getAllowedCategories(): array
    {
        $data = $this->getSearchFormData();

        $categoriesOptions = Arr::get($data, 'elements.basic.elements.category.options');

        if (!is_array($categoriesOptions)) {
            return [];
        }

        return ['all', ...Arr::pluck($categoriesOptions, 'value')];
    }

    /**
     * @return void
     */
    public function increaseInstallAppStatistic(): void
    {
        try {
            Http::asJson()
                  ->timeout(self::TIMEOUT)
                  ->withBasicAuth($this->licenseId, $this->licenseKey)
                  ->withHeaders($this->apiStoreHeaders)
                  ->post($this->baseUrl . '/post-phpfox-install', [
                      'install_status' => 'success',
                      'host'           => config('app.url'),
                      'version'        => MetaFox::getVersion(),
                  ]);
        } catch (\Throwable $error) {
            Log::info($error->getMessage());
        }
    }

    /**
     * @param mixed $versionConstraints
     *
     * @return array
     */
    private function parseVersionConstraints(?string $versionConstraints)
    {
        $minVersion = $maxVersion = null;

        try {
            $constraints = (new VersionParser())->parseConstraints($versionConstraints);
            $lowerBound  = $constraints->getLowerBound();
            $upperBound  = $constraints->getUpperBound();

            if (!$lowerBound->isZero()) {
                $minVersion = $this->toStoreVersion($lowerBound->getVersion());
            }

            if (!$upperBound->isPositiveInfinity()) {
                $maxVersion = $this->toStoreVersion($upperBound->getVersion());

                // min version shouldn't be larger than the max version
                if ($minVersion && version_compare($minVersion, $maxVersion, '>')) {
                    $maxVersion = null;
                }
            }
        } catch (Exception) {
            // silent
        }

        return [
            'min_version' => $minVersion,
            'max_version' => $maxVersion,
        ];
    }

    /**
     * convert to store version standard.
     * @param string $version
     *
     * @return ?string
     */
    private function toStoreVersion($version): ?string
    {
        preg_match("/^\w(.\w+){1,2}/", $version, $matches);

        return array_shift($matches);
    }

    private function getPurchasedApiUrl(?array $params = []): string
    {
        return $this->baseUrl . '/purchased?' . Arr::query(array_merge([
            'version'         => MetaFox::getVersion(),
            'release_channel' => config('app.mfox_app_channel'),
        ], $params));
    }

    /**
     * This method is used to query data from store.
     *
     * @param  array<int, mixed> $params
     * @return array<int, mixed>
     */
    protected function productBrowse(array $params): array
    {
        $requestUrl = $this->baseUrl
        . '/products/browse?'
        . Arr::query(array_merge([
            'version'         => MetaFox::getVersion(),
            'release_channel' => config('app.mfox_app_channel', 'production'),
        ], $params));
        $response = null;

        try {
            $response = Http::asJson()
            ->timeout(self::TIMEOUT)
            ->withHeaders($this->apiStoreHeaders)
            ->withBasicAuth($this->licenseId, $this->licenseKey)
            ->get($requestUrl)
            ->json();
        } catch (\Throwable $error) {
            Log::info($error->getMessage());
        }

        if (empty($response)) {
            return [];
        }

        return $response['data'] ?? [];
    }

    /**
     * @param  array<int, mixed> $params
     * @return array<int, mixed>
     */
    public function latest(array $params): array
    {
        $data = $this->productBrowse($params);

        foreach ($data as $index => $value) {
            $data[$index]['module_name']   = 'app';
            $data[$index]['resource_name'] = 'app_store_product';
        }

        return $data;
    }
}
