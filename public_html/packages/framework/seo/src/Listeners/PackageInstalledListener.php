<?php

namespace MetaFox\SEO\Listeners;

use MetaFox\Platform\PackageManager;
use MetaFox\SEO\Repositories\MetaRepositoryInterface;
use MetaFox\SEO\Repositories\SchemaRepositoryInterface;

class PackageInstalledListener
{
    public function handle(string $package): void
    {
        $this->publishPages($package);
        $this->publishSchemas($package);
    }

    public function publishPages(string $package): void
    {
        $pages = PackageManager::readFile($package, 'resources/pages.php');

        if (empty($pages)) {
            return;
        }

        resolve(MetaRepositoryInterface::class)->setupSEOMetas($package, $pages);
    }

    public function publishSchemas(string $package): void
    {
        $schemas = PackageManager::readFile($package, 'resources/schemas.php');

        if (empty($schemas)) {
            return;
        }

        resolve(SchemaRepositoryInterface::class)->setupSEOMetaSchemas($package, $schemas);
    }
}
