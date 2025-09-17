<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Platform\Console\FileTranslateTrait;
use MetaFox\Platform\Facades\Settings;

/**
 * Generate .phpstorm.meta data.
 *
 * Class IdeHelperFixCommand.
 * @SuppressWarnings(PHPMD)
 */
class IdeHelperFixCommand extends Command
{
    use FileTranslateTrait;

    /**
     * @var string
     */
    protected $name = 'ide:fix';

    /**
     * @var string
     */
    protected $description = 'Generate .phpstorm.meta data';

    public function handle(): int
    {
        @ini_set('memory_limit', -1);
        if (!app()->isLocal()) {
            $this->error('This method does not allow in production mode.');

            return 0;
        }

        // main entry point 15
        $this->updateLaravelMeta();
        $this->updateSiteSettingsMeta();
        $this->updatePhrasesMeta();
        $this->updateFormBuilderMeta();
        $this->updateMobileFormBuilderMeta();
        $this->updateUserPermissionsMeta();
        $this->updateMacros();
        $this->updateAppActivesMeta();
        $this->updateRouteMetas();

        return 0;
    }

    private function updateLaravelMeta(): void
    {
        // Generate metadata for develop.
        $file = base_path('.phpstorm.meta.php/laravel.meta.php');
        if (file_exists($file)) {
            unlink($file);
        }

        $this->call('ide-helper:meta', [
            '--filename' => $file,
        ]);
    }

    private function discoverSiteSettingKeys(string $key, Collection $map): void
    {
        $isRoot = strpos($key, '.') === false;
        $data   = Settings::get($key);
        $type   = 'mixed';

        if (is_array($data)) {
            $type = 'array';
            foreach (array_keys($data) as $child) {
                if (!is_int($child)) {
                    $this->discoverSiteSettingKeys($key . '.' . $child, $map);
                }
            }
        } elseif (is_int($data)) {
            $type = 'int';
        } elseif (is_bool($data)) {
            $type = 'bool';
        } elseif (is_string($data)) {
            $type = 'string';
        }

        if (!$isRoot) {
            $map[$key] = $type;
        }
    }

    private function updateSiteSettingsMeta(): void
    {
        $map = new Collection();

        foreach (Settings::keys() as $key) {
            $this->discoverSiteSettingKeys($key, $map);
        }

        $this->translate(
            '.phpstorm.meta.php/site_settings.meta.php',
            '/phpstorm/site_settings.meta.stub',
            [
                'ARGUMENTS_MAP' => var_export($map->toArray(), true),
                'DATE'          => Carbon::now()->toString(),
            ],
            true
        );
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    private function updatePhrasesMeta(): void
    {
        $map = [];
        try {
            $limit  = 500;
            $offset = 0;
            do {
                /** @var Collection<Phrase> $rows */
                $rows = Phrase::where('locale', 'en')
                    ->limit($limit)
                    ->offset($offset)
                    ->cursor();

                foreach ($rows as $row) {
                    $key       = $row->key;
                    $map[$key] = 'string';
                }
                $offset += $limit;
            } while ($rows->count() > 0);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

        $this->translate(
            '.phpstorm.meta.php/phrases.meta.php',
            '/phpstorm/phrases.meta.stub',
            [
                'ARGUMENTS_MAP' => var_export($map, true),
                'DATE'          => Carbon::now()->toString(),
            ],
            true
        );
    }

    private function updateFormBuilderMeta(): void
    {
        $map = [];
        try {
            $drivers = resolve(DriverRepositoryInterface::class)
                ->loadDrivers(
                    Constants::DRIVER_TYPE_FORM_FIELD,
                    'web',
                    true,
                    null
                );

            foreach ($drivers as $row) {
                $map[] = sprintf(' * @method static \\%s %s(?string $name=null)', $row[1], $row[0]);
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

        $this->translate(
            'packages/framework/form/src/Builder.php',
            '/phpstorm/form_fields.meta.stub',
            [
                'STATIC_METHODS' => implode(PHP_EOL, $map),
            ],
            true
        );
    }

    private function updateMobileFormBuilderMeta(): void
    {
        $map = [];
        try {
            $drivers = resolve(DriverRepositoryInterface::class)
                ->loadDrivers(Constants::DRIVER_TYPE_FORM_FIELD, 'mobile', true, null);

            foreach ($drivers as $row) {
                $map[] = sprintf(' * @method static \\%s %s(?string $name=null)', $row[1], $row[0]);
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

        $this->translate(
            'packages/framework/form/src/Mobile/Builder.php',
            '/phpstorm/mobile_fields.meta.stub',
            [
                'STATIC_METHODS' => implode(PHP_EOL, $map),
            ],
            true
        );
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    private function updateUserPermissionsMeta(): void
    {
        $map = [];

        $permissionTable = config('permission.table_names.permissions');
        try {
            $limit  = 500;
            $offset = 0;
            do {
                $permissions = DB::table($permissionTable)
                    ->select(['name'])
                    ->limit($limit)
                    ->offset($offset)
                    ->pluck('name')
                    ->toArray();

                foreach ($permissions as $name) {
                    $map[$name] = 'bool';
                }
                $offset += $limit;
            } while (count($permissions) > 0);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }

        $this->translate(
            '.phpstorm.meta.php/user_permissions.meta.php',
            '/phpstorm/user_permissions.meta.stub',
            [
                'ARGUMENTS_MAP' => var_export($map, true),
                'DATE'          => Carbon::now()->toString(),
            ],
            true
        );
    }

    public function updateMacros(): void
    {
        $this->translate(
            '.phpstorm.meta.php/macros.meta.php',
            '/phpstorm/macros.meta.stub',
            [],
            true
        );
    }

    private function updateAppActivesMeta()
    {
        $packages = config('metafox.packages');
        $map      = [];

        foreach ($packages as $package) {
            $map[$package['name']] = 'string';
        }

        $this->translate(
            '.phpstorm.meta.php/app_actives.meta.php',
            '/phpstorm/app_actives.meta.stub',
            [
                'ARGUMENTS_MAP' => var_export($map, true),
                'DATE'          => Carbon::now()->toString(),
            ],
            true
        );
    }

    private function updateRouteMetas()
    {
        Artisan::call('route:list', ['--sort' => 'uri', '--json' => true]);
        $output = json_decode(Artisan::output(), true);

        $map = [];
        foreach ($output as  $row) {
            if (!@$row['name']) {
                continue;
            }
            $map[$row['name']] = 'string';
        }

        $this->translate(
            '.phpstorm.meta.php/routes.meta.php',
            '/phpstorm/routes.meta.stub',
            [
                'ARGUMENTS_MAP' => var_export($map, true),
                'DATE'          => Carbon::now()->toString(),
            ],
            true
        );
    }
}
