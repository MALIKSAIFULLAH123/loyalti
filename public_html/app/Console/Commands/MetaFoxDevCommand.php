<?php

namespace App\Console\Commands;

use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Localize\Support\PackageTranslationExporter;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Menu\Repositories\MenuRepositoryInterface;
use MetaFox\Platform\Console\CodeGeneratorTrait;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Resource\GridConfig;
use MetaFox\Platform\Support\PolicyRuleInterface;
use MetaFox\SEO\Repositories\MetaRepositoryInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MetaFoxDevCommand.
 * @SuppressWarnings (PHPMD)
 */
class MetaFoxDevCommand extends Command
{
    use CodeGeneratorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'dev';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish to filesystem';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $publish     = $this->option('publish');
        $inspect     = $this->option('inspect');
        $packages    = $this->option('package');
        $all         = $this->option('all');
        $packageName = $this->option('name');

        if ($all) {
            $packages = PackageManager::getPackageNames();
        }

        if (!$packageName && $packages) {
            foreach ($packages as $name) {
                Artisan::call('dev', [
                    '--name'    => $name,
                    '--publish' => $publish,
                ]);
            }
        }

        if (!$packageName) {
            return 0;
        }

        if (in_array('drivers', $inspect)) {
            $this->inspectPackageDrivers($packageName);
        }

        if (in_array('phrases', $inspect)) {
            Artisan::call('package:review', ['name' => $packageName, '--phrases' => true]);
        }

        if (in_array('drivers', $publish)) {
            $this->publishDrivers($packageName);
        }

        if (in_array('menus', $publish)) {
            $this->publishPackageMenu($packageName);
        }

        if (in_array('phrases', $publish)) {
            $this->publishPhrases($packageName);
        }

        if (in_array('pages', $publish)) {
            $this->publishPackagePages($packageName);
        }

        return 0;
    }

    private function publishDrivers(string $packageName): void
    {
        $drivers = resolve(DriverRepositoryInterface::class);

        $this->info(sprintf('Published %s drivers', $packageName));
        $filename = $drivers->exportDriverToFilesystem($packageName);

        $this->info(sprintf('Updated <comment>%s</comment>', $filename));
    }

    private function inspectAllPackageDrivers(array $packages): void
    {
        foreach ($packages as $packageName) {
            $this->info(sprintf('Inspect %s drivers', $packageName));
            $this->inspectPackageDrivers($packageName);
        }
    }

    private function publishPhrases(string $packageName): void
    {
        $this->info(sprintf('Published %s phrases', $packageName));
        resolve(PackageTranslationExporter::class)->exportTranslations($packageName);
    }

    /**
     * @param  array|string    $driverType
     * @param  string          $fqClassName
     * @param  ReflectionClass $reflection
     * @param  string          $namespace
     * @param  string          $packageAlias
     * @param  mixed           $resourceName
     * @param  string          $shortName
     * @return string|null
     */
    public function extractDriverNameFromReflection(
        array|string $driverType,
        string $fqClassName,
        ReflectionClass $reflection,
        string $namespace,
        string $packageAlias,
        mixed $resourceName,
        string $shortName
    ): ?string {
        $driverName = null;

        switch ($driverType) {
            case Constants::DRIVER_TYPE_JOB:
            case Constants::DRIVER_TYPE_MAIL:
                $driverName = $fqClassName;
                break;
            case Constants::DRIVER_TYPE_FORM_FIELD:
                $driverName = $this->driverNameForFormField($reflection);
                break;
            case Constants::DRIVER_TYPE_ENTITY:
            case Constants::DRIVER_TYPE_ENTITY_USER:
            case Constants::DRIVER_TYPE_ENTITY_CONTENT:
                $driverName = $this->driverNameForEntity($reflection);
                break;
            case Constants::DRIVER_TYPE_NOTIFICATION:
                $driverName = $this->driverNameOfNotification($reflection);
                break;
            case Constants::DRIVER_TYPE_POLICY_RESOURCE:
                $driverName = $this->driverNameOfResourcePolicy(
                    $namespace,
                    $packageAlias,
                    $resourceName,
                    $shortName
                );
                break;
            case Constants::DRIVER_TYPE_JSON_RESOURCE:
                $driverName = $this->driverNameOfJsonResourceName(
                    $namespace,
                    $packageAlias,
                    $resourceName,
                    $shortName
                );
                break;
            case Constants::DRIVER_TYPE_FORM:
                $driverName = $this->driverNameOfForm($namespace, $packageAlias, $resourceName, $shortName);
                break;
            case Constants::DRIVER_TYPE_FORM_SETTINGS:
                $driverName = $this->driverNameOfFormSetting($namespace, $packageAlias, $resourceName, $shortName);
                break;
            case Constants::DRIVER_TYPE_DATA_GRID:
                $driverName = $this->driverNameDataGrid($namespace, $packageAlias, $resourceName, $shortName);
                break;
            case Constants::DRIVER_TYPE_JSON_COLLECTION:
                $driverName = $this->driveNameOfJsonCollection(
                    $namespace,
                    $packageAlias,
                    $resourceName,
                    $shortName
                );
                break;
            case Constants::DRIVER_TYPE_RESOURCE_ACTIONS:
            case Constants::DRIVER_TYPE_RESOURCE_WEB:
                $driverName = $this->driverNameOfResource($namespace, $packageAlias, $resourceName, $shortName);
                break;
            case Constants::DRIVER_TYPE_POLICY_RULE:
                $driverName = $this->driverNameOfPolicyHandler($namespace, $packageAlias, $resourceName, $shortName);
        }

        return $driverName;
    }

    /**
     * Import forms.
     *
     * @param string $packageName
     */
    private function inspectPackageDrivers(string $packageName): void
    {
        $base = base_path(PackageManager::getPath($packageName) . '/src/');

        if (!app('files')->exists($base)) {
            return;
        }

        $dir_iterator = new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS);
        /** @var \SplFileInfo[] $iterator */
        $iterator = new RecursiveIteratorIterator(
            $dir_iterator,
            RecursiveIteratorIterator::SELF_FIRST | RecursiveIteratorIterator::LEAVES_ONLY
        );
        $namespace    = PackageManager::getNamespace($packageName);
        $packageAlias = PackageManager::getAlias($packageName);

        $tableRows = [];

        foreach ($iterator as $file) {
            if (!$file->isFile()
                || $file->getExtension() !== 'php') {
                continue;
            }

            $slug = \Str::substr($file->getPathname(), strlen($base));
            $slug = \Str::substr($slug, 0, -4);

            $fqClassName = sprintf(
                '%s\\%s',
                $namespace,
                \Str::replace('/', '\\', $slug)
            );

            try {
                if (Str::contains($fqClassName, 'Trait') || !class_exists($fqClassName)) {
                    continue;
                }
            } catch (\Exception $err) {
            }

            $response = [];
            try {
                $response = $this->convertResourceClassNameToDriverInfo($fqClassName, $packageAlias, $namespace);
            } catch (\ReflectionException $e) {
            }

            if (empty($response)) {
                continue;
            }

            foreach ($response as $info) {
                [$driverType, $driverName, $version, $isAdmin, , $preload] = $info;

                if ($driverType && $driverName) {
                    $tableRows[] = [$driverType, $driverName, $version, $isAdmin, $fqClassName];
                }

                if (!$driverType || !$driverName || $driverName == 'ignored' || $this->option('dry')) {
                    continue;
                }

                $this->updateDrivers([
                    'type'       => $driverType,
                    'name'       => $driverName,
                    'version'    => $version,
                    'driver'     => $fqClassName,
                    'is_admin'   => $isAdmin ? 1 : 0,
                    'is_preload' => $preload,
                    'package_id' => $packageName,
                ]);
            }
        }

        uasort($tableRows, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            }
            if ($a[0] > $b[0]) {
                return 1;
            }

            return -1;
        });
        $this->table(['type', 'name', 'version', 'admin', 'driver'], $tableRows);

        $this->saveDriversToFiles($packageName);
    }

    /**
     * @param string $fqClassName
     * @param string $packageAlias
     * @param string $namespace
     *
     * @return array
     * @throws \ReflectionException
     */
    private function convertResourceClassNameToDriverInfo(
        string $fqClassName,
        string $packageAlias,
        string $namespace
    ): array {
        $response   = [];
        $reflection = new ReflectionClass($fqClassName);
        $shortName  = $reflection->getShortName();
        $comment    = $reflection->getDocComment();
        if (!$comment) {
            $comment = '';
        }

        $driverTypes = $this->extractDriverTypeFromReflection($namespace, $fqClassName, $reflection);

        if (empty($driverTypes)) {
            return $response;
        }

        $version      = '*';
        $resourceName = '';

        $matches = null;
        if (preg_match('/(v[\d]+)\\\\(\w+)/', $fqClassName, $matches)) {
            [, $version, $resourceName] = $matches;
        }

        $isAdmin = strpos($fqClassName, '\\Admin\\');

        $driverName  = $this->extractDriverNameFromComment($comment);
        $description = $this->extractDescriptionFromComment($comment);
        $preload     = $this->extractPreloadFromComment($comment);

        foreach ($driverTypes as $driverType) {
            $name = $driverName ?: $this->extractDriverNameFromReflection(
                $driverType,
                $fqClassName,
                $reflection,
                $namespace,
                $packageAlias,
                $resourceName,
                $shortName
            );

            if (!$name) {
                continue;
            }

            $response[] = [$driverType, $name, $version, $isAdmin ? 1 : 0, $description, $preload];
        }

        return $response;
    }

    public function publishMenus(array $packages): void
    {
        foreach ($packages as $packageName) {
            $this->info(sprintf('Published %s menus', $packageName));
            $this->publishPackageMenu($packageName);
        }
    }

    /**
     * @param string $package
     *
     * @return void
     */
    public function publishPackageMenu(string $package): void
    {
        $menuRepository = resolve(MenuRepositoryInterface::class);
        $itemRepository = resolve(MenuItemRepositoryInterface::class);

        PackageManager::exportToFilesystem(
            $package,
            'resources/menu/menus.php',
            $menuRepository->getByPackage($package)
        );

        foreach (['web', 'admin', 'mobile'] as $resolution) {
            PackageManager::exportToFilesystem(
                $package,
                "resources/menu/$resolution.php",
                $itemRepository->dumpByPackage($package, $resolution)
            );
        }
    }

    public function getOptions()
    {
        return [
            [
                'publish', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Example: drivers, phrases, menus, pages',
            ],
            [
                'package', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Indicates scope to execute command. Example: metafox/blog',
            ],
            ['name', null, InputOption::VALUE_OPTIONAL, 'spec package name', null],
            ['inspect', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Example: resources'],
            ['dry', null, InputOption::VALUE_NONE, 'Dry run?'],
            ['all', null, InputOption::VALUE_NONE, 'All packages?'],
        ];
    }

    private function publishPackagePages(string $packageName): void
    {
        $this->info(sprintf('Published %s pages', $packageName));

        $data = resolve(MetaRepositoryInterface::class)->dumpSEOMetas($packageName);

        PackageManager::exportToFilesystem($packageName, 'resources/pages.php', $data);
    }

    private function extractDriverNameFromComment($comment): ?string
    {
        if (!$comment) {
            return null;
        }

        if (preg_match('/@driverName([\s]+)([^\s]+)/', $comment, $matches)) {
            return $matches[2];
        }

        return null;
    }

    private function extractPreloadFromComment($comment): int
    {
        if (!$comment) {
            return 0;
        }

        if (preg_match('/@preload/', $comment, $matches)) {
            return 1;
        }

        return 0;
    }

    private function extractDescriptionFromComment($comment): string
    {
        return '';
    }

    private function getEntityType(string $namespace, string $resourceName, string $packageAlias): string
    {
        $class = sprintf('%s\\Models\\%s', $namespace, $resourceName);

        if (class_exists($class)) {
            $model = new $class();
            if (method_exists($model, 'entityType')) {
                return Str::replace('-', '_', $model->entityType());
            }
        }

        return Str::snake($resourceName);
    }

    private function driverNameOfJsonResourceName(
        string $namespace,
        string $packageAlias,
        string $resourceName,
        string $shortName
    ): string {
        $entityType = $this->getEntityType($namespace, $resourceName, $packageAlias);

        if (Str::startsWith($shortName, $resourceName)) {
            $variant = substr($shortName, strlen($resourceName));

            if (!$variant) {
                return '';
            }

            if ($entityType) {
                return $entityType . '.' . Str::snake($variant);
            } else {
                return Str::snake($resourceName) . '.' . Str::snake($variant);
            }
        }

        return '';
    }

    private function driveNameOfJsonCollection(
        string $namespace,
        string $packageAlias,
        string $resourceName,
        string $shortName
    ): string {
        if (Str::endsWith($shortName, 'Collection')) {
            $shortName = substr($shortName, 0, strlen('Collection') * -1);
        }

        $entityType = $this->getEntityType($namespace, $resourceName, $packageAlias);

        if (Str::startsWith($shortName, $resourceName)) {
            $variant = substr($shortName, strlen($resourceName));

            if ($entityType) {
                return $entityType . '.' . Str::snake($variant);
            } else {
                return Str::snake($resourceName) . '.' . Str::snake($variant);
            }
        }

        return '';
    }

    private function driverNameOfResource(
        string $namespace,
        string $packageAlias,
        string $resourceName,
        string $shortName
    ): string {
        if ($shortName === 'WebSetting' || $shortName === 'MobileSetting') {
            return $this->getEntityType($namespace, $resourceName, $packageAlias);
        }

        return '';
    }

    private function driverNameOfForm(
        string $namespace,
        string $packageAlias,
        mixed $resourceName,
        string $shortName
    ): string {
        $entity = $this->getEntityType($namespace, $resourceName, $packageAlias);

        if (Str::endsWith($shortName, 'Form')) {
            $shortName = substr($shortName, 0, -4);
        }

        if (Str::endsWith($shortName, $resourceName)) {
            $shortName = substr($shortName, 0, -1 * strlen($resourceName));
        }

        $action = Str::snake($shortName);

        $transformAction = [
            'create' => 'store',
            'edit'   => 'update',
            'delete' => 'destroy',
            'filter' => 'search',
        ];

        if (Str::startsWith($action, "{$packageAlias}_")) {
            $action = substr($action, strlen("{$packageAlias}_"));
        }

        $action = $transformAction[$action] ?? $action;

        if ($packageAlias === $entity) {
            return sprintf('%s.%s', $entity, $action);
        }

        return sprintf('%s.%s.%s', $packageAlias, $entity, $action);
    }

    private function driverNameOfMobileForm(
        string $namespace,
        string $packageAlias,
        mixed $resourceName,
        string $shortName
    ): string {
        $entity = $this->getEntityType($namespace, $resourceName, $packageAlias);

        if (Str::endsWith($shortName, 'MobileForm')) {
            $shortName = substr($shortName, 0, -1 * strlen('MobileForm'));
        }

        if (Str::endsWith($shortName, 'Form')) {
            $shortName = substr($shortName, 0, -1 * strlen('Form'));
        }

        if (Str::endsWith($shortName, $resourceName)) {
            $shortName = substr($shortName, 0, -1 * strlen($resourceName));
        }

        $action = Str::snake($shortName);

        $transformAction = [
            'create' => 'store',
            'edit'   => 'update',
            'delete' => 'destroy',
            'filter' => 'search',
        ];

        if (Str::startsWith($action, "{$packageAlias}_")) {
            $action = substr($action, strlen("{$packageAlias}_"));
        }

        $action = $transformAction[$action] ?? $action;

        return sprintf('%s.%s.%s', $packageAlias, $entity, $action);
    }

    private function driverNameDataGrid(
        string $namespace,
        string $packageAlias,
        mixed $resourceName,
        string $shortName
    ): string {
        $resource = Str::snake($resourceName);

        if (Str::endsWith($shortName, 'DataGrid')) {
            $shortName = substr($shortName, 0, -8);
        }

        $prefix = "$packageAlias.$resource";

        if ($packageAlias === $resource) {
            $prefix = $packageAlias;
        }

        if ($shortName) {
            return sprintf('%s.%s', $prefix, Str::snake($shortName));
        }

        return $prefix;
    }

    /**
     * @param string          $namespace
     * @param string          $fqClassName
     * @param ReflectionClass $reflection
     *
     * @return ?string[]
     */
    private function extractDriverTypeFromReflection(
        string $namespace,
        string $fqClassName,
        ReflectionClass $reflection
    ): ?array {
        if ($reflection->isAbstract()
            || $reflection->isAnonymous()
            || $reflection->isTrait()
            || $reflection->isInterface()) {
            return null;
        }
        $shortName = substr($fqClassName, strlen($namespace) + 1);
        if ($reflection->isSubclassOf(ResourceCollection::class)) {
            return [Constants::DRIVER_TYPE_JSON_COLLECTION];
        } elseif ($reflection->isSubclassOf(AbstractField::class)) {
            return [Constants::DRIVER_TYPE_FORM_FIELD];
        } elseif ($reflection->isSubclassOf(\MetaFox\Form\AdminSettingForm::class)) {
            return [Constants::DRIVER_TYPE_FORM_SETTINGS];
        } elseif ($reflection->isSubclassOf(AbstractForm::class)) {
            return [Constants::DRIVER_TYPE_FORM];
        } elseif ($reflection->isSubclassOf(\MetaFox\Platform\Contracts\Entity::class)) {
            $ret = [Constants::DRIVER_TYPE_ENTITY];
            if ($reflection->isSubclassOf(\MetaFox\Platform\Contracts\Content::class)) {
                $ret[] = Constants::DRIVER_TYPE_ENTITY_CONTENT;
            }
            if ($reflection->isSubclassOf(\MetaFox\Platform\Contracts\User::class)) {
                $ret[] = Constants::DRIVER_TYPE_ENTITY_USER;
            }

            return $ret;
        } elseif ($reflection->isSubclassOf(GridConfig::class)) {
            return [Constants::DRIVER_TYPE_DATA_GRID];
        } elseif ($reflection->isSubclassOf(\MetaFox\Platform\Resource\MobileSetting::class)) {
            return [Constants::DRIVER_TYPE_RESOURCE_ACTIONS];
        } elseif ($reflection->isSubclassOf(\MetaFox\Platform\Resource\WebSetting::class)) {
            return [Constants::DRIVER_TYPE_RESOURCE_WEB];
        } elseif ($reflection->isSubclassOf(JsonResource::class)) {
            return [Constants::DRIVER_TYPE_JSON_RESOURCE];
        } elseif ($reflection->isSubclassOf(PolicyRuleInterface::class)) {
            return [Constants::DRIVER_TYPE_POLICY_RULE];
        } elseif ($reflection->isSubclassOf(\MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface::class)) {
            return [Constants::DRIVER_TYPE_POLICY_RESOURCE];
        } else {
            if (preg_match('/^Policies\\\\([\w]+)$/', $shortName)) {
                return [Constants::DRIVER_TYPE_POLICY_RESOURCE];
            } elseif (preg_match('/^Mails\\\\([\w]+)$/', $shortName)) {
                return [Constants::DRIVER_TYPE_MAIL];
            } elseif (preg_match('/^Notifications\\\\([\w]+)$/', $shortName)) {
                return [Constants::DRIVER_TYPE_NOTIFICATION];
            } elseif (preg_match('/^Jobs\\\\([\w]+)$/', $shortName)) {
                return [Constants::DRIVER_TYPE_JOB];
            } elseif (preg_match('/^Events\\\\([\w]+)$/', $shortName)) {
                return [Constants::DRIVER_TYPE_EVENT];
            }
        }

        return null;
    }

    private function driverNameOfResourcePolicy(
        string $namespace,
        string $packageAlias,
        mixed $resourceName,
        string $shortName
    ): ?string {
        if (Str::endsWith($shortName, 'Policy')) {
            $shortName = substr($shortName, 0, -1 * strlen('Policy'));
        }

        // check in the same model

        $modelClass = sprintf('%s\\Models\\%s', $namespace, $shortName);

        if (class_exists($modelClass)) {
            $modelReflection = new ReflectionClass($modelClass);
            if ($modelReflection->isSubclassOf(Model::class)) {
                return $modelClass;
            }
        }

        return null;
    }

    private function driverNameOfFormSetting(
        string $namespace,
        string $packageAlias,
        mixed $resourceName,
        string $shortName
    ): string {
        if ($shortName === 'SiteSettingForm') {
            return $packageAlias;
        }

        if ($shortName === 'GeneralSettingForm') {
            return $packageAlias;
        }

        if (Str::endsWith($shortName, 'SiteSettingForm')) {
            $shortName = substr($shortName, 0, -1 * strlen('SiteSettingForm'));
        }

        if (Str::endsWith($shortName, 'SettingsForm')) {
            $shortName = substr($shortName, 0, -1 * strlen('SettingsForm'));
        }

        return sprintf('%s.%s', $packageAlias, Str::snake($shortName));
    }

    private function driverNameOfPolicyHandler(
        string $namespace,
        string $packageAlias,
        mixed $resourceName,
        string $shortName
    ): ?string {
        if (Str::startsWith($shortName, 'Can')) {
            $shortName = substr($shortName, strlen('Can'));
        }

        return Str::camel($shortName);
    }

    private function driverNameOfNotification(ReflectionClass $reflection): ?string
    {
        try {
            /** @var Notification $obj */
            $obj = $reflection->newInstanceWithoutConstructor();

            return $obj->getType();
        } catch (\Exception) {
        }

        return null;
    }

    private function driverNameForEntity(ReflectionClass $reflection): ?string
    {
        try {
            /** @var Notification $obj */
            $obj = $reflection->newInstanceWithoutConstructor();

            if ($reflection->hasConstant('ENTITY_TYPE')
                && $obj instanceof \MetaFox\Platform\Contracts\Entity) {
                return $obj->entityType();
            }
        } catch (\Exception) {
        }

        return null;
    }

    private function driverNameForFormField(ReflectionClass $reflection): ?string
    {
        $name = $reflection->getShortName();

        if (Str::endsWith($name, 'Field')) {
            $name = substr($name, 0, -1 * strlen('Field'));
        }

        return Str::camel($name);
    }
}
