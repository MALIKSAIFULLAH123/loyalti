<?php

namespace MetaFox\App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFox\App\Models\Package;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Layout\Repositories\VariantRepositoryInterface;
use MetaFox\Platform\Contracts\SiteSettingRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use RuntimeException;
use ZipArchive;

/**
 * @method Package getModel()
 * @method Package find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageRepository extends AbstractRepository implements PackageRepositoryInterface
{
    public function model(): string
    {
        return Package::class;
    }

    public function getPackageIdOptions(): array
    {
        return localCacheStore()->rememberForever(
            __METHOD__,
            function () {
                $return   = [];
                $packages = $this->getBuilderActivePackage()->get();

                foreach ($packages as $module) {
                    $return[] = ['value' => $module->name, 'label' => $module->title];
                }

                return array_values(Arr::sort($return, 'label'));
            }
        );
    }

    public function getPackageOptions(bool $alias = true): array
    {
        if ($alias) {
            return localCacheStore()->rememberForever(
                'ModuleRepository_getModuleOptions_true',
                function () {
                    $return = [];

                    $packages = $this->getBuilderActivePackage()->get(['title', 'alias']);

                    foreach ($packages as $module) {
                        $return[] = ['value' => $module->alias, 'label' => $module->title];
                    }

                    return $return;
                }
            );
        }

        return localCacheStore()->rememberForever(
            'ModuleRepository_getModuleOptions_false',
            function () {
                $return   = [];
                $packages = $this->getBuilderActivePackage()->get(['title', 'name']);

                foreach ($packages as $module) {
                    $return[] = ['value' => $module->name, 'label' => $module->title];
                }

                return $return;
            }
        );
    }

    public function getPackageHasPermission(): Collection
    {
        return localCacheStore()->rememberForever(
            'ModuleRepository_getModuleHasPermission',
            function () {
                return $this->getBuilderActivePackage()
                    ->withCount([
                        'permissions' => function ($query) {
                            $query->whereNotIn('entity_type', ['*']);
                        },
                    ])
                    ->get('packages.*');
            }
        );
    }

    public function getPackageHasPermissionOptions(): array
    {
        $packages = $this->getPackageHasPermission()->filter(function (Package $package) {
            return $package->permissions_count > 0;
        });

        $packageOptions = [];

        foreach ($packages as $package) {
            $packageOptions[] = ['value' => $package->alias, 'label' => $package->title];
        }

        return $packageOptions;
    }

    /**
     * @return array|string[]
     *                        Order by title supports filter options.
     */
    public function getActivePackageAliases(): array
    {
        return localCacheStore()->rememberForever(
            __METHOD__,
            function () {
                $return   = [];
                $packages = $this->getBuilderActivePackage()->get(['is_active', 'alias']);

                foreach ($packages as $package) {
                    $return[] = $package->alias;
                }

                return $return;
            }
        );
    }

    /**
     * @return array
     *               Order by title supports filter options.
     */
    public function getActivePackageIds(): array
    {
        return localCacheStore()->rememberForever(
            __METHOD__,
            function () {
                $return = [];

                $packages = $this->getBuilderActivePackage()->get(['is_active', 'name']);

                foreach ($packages as $package) {
                    $return[] = $package->name;
                }

                return $return;
            }
        );
    }

    protected function getBuilderActivePackage(): Builder
    {
        return $this->getModel()->newQuery()->where([
            'is_active'    => 1,
            'is_installed' => 1,
        ])->orderBy('title');
    }

    public function getResourceOptions(): array
    {
        return localCacheStore()->rememberForever(
            __METHOD__,
            function () {
                $return   = [];
                $resource = Relation::$morphMap;

                $options = array_keys($resource);

                foreach ($options as $option) {
                    $return[] = ['value' => $option, 'label' => $option];
                }

                return $return;
            }
        );
    }

    public function getAllPackages(User $context, array $params): Builder
    {
        gate_authorize($context, 'moderate', Package::class);

        $query = $this->getModel()->newQuery()->orderBy('title')->where($params);

        $query->orderBy('is_core', 'DESC');
        $query->orderBy('priority', 'ASC');
        $query->orderBy('id', 'ASC');

        return $query;
    }

    public function getPackageByName(string $name): Package
    {
        /** @var Package $result */
        $result = $this->getModel()
            ->newQuery()
            ->where('name', '=', $name)
            ->firstOrFail();

        return $result;
    }

    public function getPackageByAlias(string $alias): Package
    {
        /** @var Package $result */
        $result = $this->getModel()
            ->newQuery()
            ->where('alias', '=', $alias)->firstOrFail();

        return $result;
    }

    public function setupPackage(string $packageName): Package
    {
        $filename = implode(DIRECTORY_SEPARATOR, [
            base_path(),
            'packages',
            $packageName,
            'composer.json',
        ]);

        $content = app('files')->get($filename);

        $composer = json_decode($content, true);

        if (!$composer) {
            throw new RuntimeException('Could not read ' . $filename);
        }

        Artisan::call('package:install', ['package' => $packageName]);

        return $this->getPackageByName($packageName);
    }

    public function updatePackage(User $context, int $id, array $params): Package
    {
        $package = $this->find($id);

        $package->update($params);

        Artisan::call('package:install', [
            'package' => $package->name,
        ]);

        return $package->refresh();
    }

    public function uninstallPackage(User $context, int $id): void
    {
        /** @var Package $package */
        $package = Package::query()->findOrFail($id);

        if ($package->is_active) {
            throw new \InvalidArgumentException(__p('app::phrase.failed_unistalling_an_active_app'));
        }

        Artisan::call('package:uninstall', [
            'package' => $package->name,
        ]);
    }

    public function deletePackage(User $context, int $id): void
    {
        /** @var Package $package */
        $package = Package::query()->findOrFail($id);

        if ($package->is_installed) {
            throw new \InvalidArgumentException(__p('app::phrase.failed_delete_not_uninstall_app'));
        }

        Artisan::call('package:uninstall', [
            'package' => $package->name,
            '--clean' => true,
        ]);
    }

    public function parseComposerInfo(array $composer)
    {
        $title = Arr::get($composer, 'extra.metafox.title');

        return [
            'title'              => $title ?? Arr::get($composer, 'name'),
            'name'               => Arr::get($composer, 'name'),
            'version'            => Arr::get($composer, 'version', '1.0.0'),
            'latest_version'     => Arr::get($composer, 'version', '1.0.0'),
            'description'        => Arr::get($composer, 'description', ''),
            'keywords'           => Arr::get($composer, 'keywords', ''),
            'path'               => Arr::get($composer, 'extra.metafox.path', ''),
            'type'               => Arr::get($composer, 'extra.metafox.type', 'app'),
            'category'           => Arr::get($composer, 'extra.metafox.category', null),
            'alias'              => Arr::get($composer, 'extra.metafox.alias', ''),
            'icon'               => Arr::get($composer, 'extra.metafox.icon', 'ico-question-mark'),
            'namespace'          => Arr::get($composer, 'extra.metafox.namespace', ''),
            'name_studly'        => Str::studly(Arr::get($composer, 'extra.metafox.alias', '')),
            'author'             => Arr::get($composer, 'authors.0.name', ''),
            'author_url'         => Arr::get($composer, 'authors.0.homepage', ''),
            'internal_url'       => Arr::get($composer, 'extra.metafox.internalUrl', ''),
            'internal_admin_url' => Arr::get($composer, 'extra.metafox.internalAdminUrl', ''),
            'store_url'          => Arr::get($composer, 'extra.metafox.store_url'),
            'frontend'           => Arr::get($composer, 'extra.metafox.frontend', []),
            'mobile'             => Arr::get($composer, 'extra.metafox.mobile', []),
            'order'              => Arr::get($composer, 'extra.metafox.priority', 0),
            'is_core'            => Arr::get($composer, 'extra.metafox.core', 1),
            'priority'           => Arr::get($composer, 'extra.metafox.priority', 100),
            'providers'          => Arr::get($composer, 'extra.metafox.providers', []),
            'requires'           => Arr::get($composer, 'extra.metafox.requires', []),
        ];
    }

    public function syncComposerInfo(array $composer): ?Package
    {
        $attributes = $this->parseComposerInfo($composer);

        $package = $this->findByName($attributes['name']);

        if (!$package) {
            // create temporary package.
            $package = $this->getModel()->create($attributes);
        } else {
            Log::channel('installation')->info('update app into database', [$attributes]);
            $package->fill($attributes);
            $package->saveQuietly();
            $package->refresh();
        }

        return $package;
    }

    private function getFrontendPackages(): array
    {
        $query = $this->getModel()->newQuery();

        /** @var \Illuminate\Database\Eloquent\Collection<Package> $collection */
        $collection = $query->orderBy('is_core', 'DESC')
            ->orderBy('priority', 'ASC')
            ->where('is_active', '=', 1)
            ->get();

        $result = [];

        foreach ($collection as $module) {
            $frontend = $module->frontend;
            if (!is_array($frontend)) {
                continue;
            }

            foreach ($frontend as $name => $version) {
                $result[$name] = $version;
            }
        }

        return $result;
    }

    /**
     * prepare env value for react-js site.
     * env format guide: @link https://www.npmjs.com/package/dotenv.
     *
     * @return string
     */
    public function getBuildEnvironments(): string
    {
        $data = [
            'PUBLIC_URL'       => '',
            'ASSET_PATH'       => '',
            'PUBLIC_HTML'      => config('app.mfox_public_html'),
            'MFOX_API_URL'     => config('app.mfox_api_url'),
            'MFOX_SITE_URL'    => config('app.url'),
            'MFOX_ADMINCP_URL' => config('app.mfox_admincp_url'),
            // default theme site : conversaion theme_id:variant_id
            'MFOX_SITE_THEME'              => config('app.mfox_site_theme', 'a0:a0'),
            'MFOX_ADMINCP_THEME'           => config('app.mfox_admincp_theme', 'admincp:admincp'),
            'MFOX_LOADING_BG'              => '#2d2d2d',
            'MFOX_BUILD_SERVICE'           => true,
            'MFOX_SITE_DESCRIPTION'        => Settings::get('core.general.description'),
            'MFOX_SITE_KEYWORDS'           => Settings::get('core.general.keywords'),
            'MFOX_SITE_TITLE'              => Settings::get('core.general.site_title'),
            'MFOX_END_HEAD_HTML'           => Settings::get('core.end_head_html'),
            'MFOX_END_BODY_HTML'           => Settings::get('core.end_body_html'),
            'MFOX_INIT_BODY_HTML'          => Settings::get('core.init_body_html'),
            'MFOX_START_BODY_HTML'         => Settings::get('core.start_body_html'),
            'MFOX_SITE_NAME'               => Settings::get('core.general.site_name'),
            'MFOX_COOKIE_PREFIX'           => config('core.cookie.prefix'),
            'MFOX_LOCALE'                  => config('app.locale'),
            'MFOX_FAVICON_URL'             => app('asset')->findByName('site_favicon')?->url,
            'MFOX_MASK_ICON_URL'           => app('asset')->findByName('site_mask_icon')?->url,
            'MFOX_APPLE_TOUCH_ICON_URL'    => app('asset')->findByName('site_apple_touch_icon')?->url,
            'MFOX_MOBILE_GOOGLE_APP_ID'    => Settings::get('mobile.google_app_id'),
            'MFOX_MOBILE_APPLE_APP_ID'     => Settings::get('mobile.apple_app_id'),
            'MFOX_ACTIVE_THEMES'           => implode(',', resolve(VariantRepositoryInterface::class)->getActiveVariantIds()),
            'MFOX_LOCALE_SUPPORTS'         => implode(',', Language::availableLocales()),
            'MFOX_PWA_DISPLAY'             => Settings::get('core.pwa.display'),
            'MFOX_PWA_APP_NAME'            => Settings::get('core.pwa.app_name') ?: Settings::get('core.general.site_name'),
            'MFOX_PWA_APP_DESCRIPTION'     => Settings::get('core.pwa.app_description') ?: Settings::get('core.general.description'),
            'MFOX_PWA_INSTALL_DESCRIPTION' => Settings::get('core.pwa.install_description'),
            'MFOX_SITE_THEME_TYPE'         => Settings::get('user.user_profile_default_theme_type', 'auto'),
        ];

        $privateSettings = resolve(SiteSettingRepositoryInterface::class)
            ->getPrivateEnvironments();

        $data = array_merge($privateSettings, $data);

        $response = [];
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (null === $value || '' === $value) {
                $value = '';
            } elseif (is_string($value)) {
                // escape multiple line string to \n.
                $value = str_replace(["\r\n", "\n", "\r"], '\\n', $value);
                $value = sprintf('"%s"', $value);
            }
            $response[] = sprintf('%s=%s', $key, $value);
        }

        return implode(PHP_EOL, $response);
    }

    public function attachBuildArchive(\ArrayObject $data, ZipArchive $zip, ?string $reason = null): void
    {
        $setting = $this->getBuildSettings($reason);
        // add env settings, required for build
        $env = $this->getBuildEnvironments();
        $zip->addFromString('app/.env', $env);

        // add env settings, required for build
        $json = json_encode($setting);
        $zip->addFromString('app/settings.json', $json);

        $buildInfo = [
            'license' => [
                'id'  => config('app.mfox_license_id'),
                'key' => config('app.mfox_license_key'),
            ],
            'dependencies' => $this->getBuildDependencies(),
            'build'        => [
                'platformVersion' => MetaFoxConstant::VERSION,
                'reason'          => $reason ?? __p('layout::phrase.rebuild_site_action'),
                'callbackUrl'     => url_utility()->makeApiFullUrl('api/v1/core/package/build/callback'),
            ],
        ];

        foreach ($buildInfo as $key => $value) {
            $data[$key] = $value;
        }
    }

    private function getBuildDependencies(): array
    {
        /**
         * @deprecated 5.2.0
         * Workaround solution to avoid missing FE code for core apps that are available on the Store.
         * NOTE:
         * DO NOT include core apps that are UNAVAILABLE on the Store, it might crash the build service.
         * Need to find a proper solution to solve this issue instead of hardcoded like this.
         */
        $requiredAppNames = [
            'metafox/captcha',
            'metafox/emoney',
            'metafox/friend',
            'metafox/hashtag',
            'metafox/photo',
            'metafox/search',
            'metafox/sms',
            'metafox/featured',
        ];

        return $this->getModel()
            ->newModelQuery()
            ->where(function (Builder $subQuery) use ($requiredAppNames) {
                $subQuery->where('is_core', '=', 0)
                    ->orWhereIn('name', $requiredAppNames);
            })
            ->where('is_active', '=', 1)
            ->where('is_installed', '=', 1)
            ->orderBy('is_core', 'DESC')
            ->orderBy('priority', 'ASC')
            ->get()
            ->map(function ($package) {
                return [
                    'name'     => $package->name,
                    'version'  => $package->version,
                    'channel'  => config('app.mfox_app_channel'),
                    'download' => config('app.mfox_bundle_service_download_app'),
                ];
            })->toArray();
    }

    public function getBuildSettings(?string $reason = null): array
    {
        $data = [
            'siteUrl'    => '/',
            'admincpUrl' => '/admincp',
            'cookie'     => [
                'prefix'     => 'yA0JuFD6n6zkC1',
                'attributes' => [],
            ],
            'localStore' => [
                'prefix' => 'mfox',
            ],
            'i18n' => [
                'locale'   => 'en',
                'supports' => ['en'],
            ],
            'packages' => $this->getFrontendPackages(),
        ];

        // unique string to track hash changed.
        $contentHash         = sha1(json_encode($data));
        $data['contentHash'] = $contentHash;

        return $data;
    }

    public function setInstallationStatus(string $name, string $status): void
    {
        $model = $this->findByName($name);

        if ($model) {
            $model->bundle_status = $status;
            $model->saveQuietly();

            return;
        }

        Package::withoutEvents(function () use ($name, $status) {
            $this->getModel()->insert([
                'title'         => $name,
                'icon'          => 'ico-app',
                'path'          => '',
                'alias'         => $name,
                'version'       => '0.0.0',
                'namespace'     => '',
                'name_studly'   => $name,
                'name'          => $name,
                'bundle_status' => $status,
                'is_installed'  => false,
                'is_active'     => false,
                'providers'     => '[]',
            ]);
        });
    }

    /**
     * @param  string       $name
     * @return Package|null
     *                      WARN: Do not use find or fail this method.
     */
    public function findByName(string $name): ?Package
    {
        return $this->getModel()->where('name', $name)->first();
    }

    public function getInternalAdminUrls(): array
    {
        /** @var Package[] $packges */
        $packges = $this->get();
        $result  = [];
        foreach ($packges as $package) {
            $result[PackageManager::getAlias($package->name)] = [
                'url'   => $package->internal_admin_url,
                'title' => $package->title,
            ];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getPackageByNames(array $names): Collection
    {
        return $this->getModel()
            ->newModelQuery()
            ->where('is_active', '=', 1)
            ->where('is_installed', '=', 1)
            ->whereIn('name', $names)
            ->get()
            ->collect();
    }

    public function isAppActive(string $name): bool
    {
        return in_array($name, $this->getActivePackageIds());
    }

    /**
     * @return Builder
     */
    public function getEloquentBuilder(): Builder
    {
        return $this->getModel()->newQuery();
    }

    /**
     * @inheritDoc
     */
    public function getInstalledPackageAliases(): array
    {
        return localCacheStore()->rememberForever(__METHOD__, function () {
            return $this->getEloquentBuilder()->where('is_installed', 1)->get('alias')->collect()->pluck('alias')->toArray();
        });
    }
}
