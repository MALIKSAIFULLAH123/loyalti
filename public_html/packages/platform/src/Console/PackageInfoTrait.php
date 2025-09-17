<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Console;

use MetaFox\Platform\PackageManager;

/**
 * Trait PackageInfoTrait.
 */
trait PackageInfoTrait
{
    public function getPackageName(): string
    {
        $name = $this->argument('package');

        if (!is_string($name)) {
            $this->error('Package name is invalid');
            abort(400);
        }

        return $name;
    }

    public function getPackageNamespace(): string
    {
        return PackageManager::getNamespace($this->getPackageName());
    }

    public function getPackageAlias(): string
    {
        return PackageManager::getAlias($this->getPackageName());
    }

    public function getPackagePath(): string
    {
        return PackageManager::getPath($this->getPackageName());
    }

    /**
     * @return array<mixed>
     */
    public function getPackageConfig(): array
    {
        return PackageManager::getConfig($this->getPackageName());
    }

    public function getEscapedPackageNamespace(): string
    {
        $namespace = $this->getPackageNamespace();

        return str_replace('\\', '\\\\', $namespace);
    }
}
