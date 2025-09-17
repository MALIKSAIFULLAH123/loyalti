<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Localize\Listeners;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Localize\Repositories\LanguageRepositoryInterface;
use MetaFox\Platform\PackageManager;

/**
 * Handle module installed.
 *
 * Class PackageInstalledListener
 */
class PackageInstalledListener
{
    public function handle(string $package): void
    {
        $config = PackageManager::getComposerJson($package);

        if ('language' === Arr::get($config, 'extra.metafox.type')) {
            // upsert language info
            $this->setupLanguage($package, $config);
        } else {
            $this->publishTranslations($package);
        }
    }

    /**
     * scan language phrase from file system then add to file.
     *
     * @param string $package
     */
    private function publishTranslations(string $package): void
    {
        $path = base_path(implode(
            DIRECTORY_SEPARATOR,
            [PackageManager::getPath($package), 'resources', 'lang']
        ));

        if (!app('files')->isDirectory($path)) {
            return;
        }

        Log::channel('installation')->debug('publishTranslations', [$package]);

        $namespace = PackageManager::getAlias($package);

        resolve('translation')->importTranslations($package, $namespace, $path);
    }

    public function setupLanguage(string $package, array $config): void
    {
        $info = Arr::get($config, 'extra.metafox.language');

        // missing language info
        if (!$info) {
            return;
        }

        $code = $info['language_code'];

        $params =  Arr::only($info, [
            'name',
            'language_code',
            'charset',
            'direction',
        ]);

        $params['package_id'] =  Arr::get($config, 'name');

        resolve(LanguageRepositoryInterface::class)
            ->getModel()
            ->newQuery()
            ->updateOrCreate(Arr::only($info, 'language_code'), $params);

        // update translation phrases
        $filename = base_path(PackageManager::getPath($package) . "/resources/lang/$code.csv");

        if (app('files')->exists($filename)) {
            try {
                resolve('translation')->importTranslationsFromCSV($filename);
            } catch (Exception $e) {
                Log::channel('installation')->debug("setupLanguage {$package}: {$e->getMessage()}");
            }
        }
    }
}
