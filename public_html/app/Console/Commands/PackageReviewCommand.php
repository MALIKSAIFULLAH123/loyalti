<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Authorization\Repositories\Contracts\PermissionRepositoryInterface;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Localize\Support\PackageTranslationExporter;
use MetaFox\Menu\Listeners\PackageInstalledListener;
use MetaFox\Platform\PackageManager;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class InspectPackageCommand.
 *
 * Help to fix package
 * @codeCoverageIgnore
 */
class PackageReviewCommand extends Command
{
    public const PHRASE_REG = '/^([\w-]+)::([\w-]+)\.([\w-]+)$/';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:review';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Metafox export command';

    protected function getArguments(): array
    {
        return [
            ['name', null, InputOption::VALUE_NONE, 'package name?'],
        ];
    }

    protected function reviewMenuPhrases(string $package): void
    {
        $rootPath = base_path(PackageManager::getPath($package));

        $phrases      = [];
        $alias        = PackageManager::getAlias($package);
        $prefix       = $alias . '::phrase.';
        $replacements = [];

        foreach (['web', 'admin', 'mobile'] as $menu) {
            foreach ($this->inspectMenuFile(
                $prefix,
                $menu,
                $rootPath . '/resources/menu/' . $menu . '.php'
            ) as $key => $label) {
                $phrases[$key] = $label;
                app('phrases')->addSamplePhrase($key, $label);
                $replacements[substr($key, strlen($prefix))] = $label;
            }
        }

        if (count($phrases)) {
            if ($this->option('verbose')) {
                $this->info(json_encode($phrases, JSON_PRETTY_PRINT));
            }
            $file   = "$rootPath/resources/lang/en/phrase.php";
            $origin = file_exists($file) ? app('files')->getRequire($file) : [];
            $origin = array_merge($replacements, $origin);
            export_to_file($file, $origin);
        }
    }

    protected function inspectMenuFile(string $prefix, string $menu, string $file): array
    {
        $phrases = [];

        if (!file_exists($file)) {
            return [];
        }

        $items = app('files')->getRequire($file);
        if (empty($items)) {
            return [];
        }
        $changed = false;

        foreach ($items as $index => $item) {
            foreach (['label', 'note', 'subInfo'] as $name) {
                $text = $item[$name] ?? null;
                if (!$text) {
                    continue;
                }

                if ('admin' === $menu and 'note' === $name) {
                    $items[$index][$name] = null;
                    $changed              = true;
                    continue;
                }

                if (preg_match(static::PHRASE_REG, $text)) {
                    continue;
                }
                $key                  = $prefix . trim(Str::lower(preg_replace('/(\W+)/m', '_', $text)), '_');
                $items[$index][$name] = $key;
                $phrases[$key]        = $text;
                $changed              = true;
            }
        }
        if ($changed) {
            $this->comment('Updated file ' . substr($file, strlen(base_path())));
            if ($this->option('verbose')) {
                $this->comment(json_encode($items, JSON_PRETTY_PRINT));
            }
            export_to_file($file, $items);
        }

        return $phrases;
    }

    protected function getOptions(): array
    {
        return [
            ['phrases', null, InputOption::VALUE_NONE, 'inspect phrases?'],
            ['menus', null, InputOption::VALUE_NONE, 'inspect phrases?'],
            ['menuPhrases', null, InputOption::VALUE_NONE, 'inspect menu phrases?'],
            ['drivers', null, InputOption::VALUE_NONE, 'inspect phrases?'],
            ['perms', null, InputOption::VALUE_NONE, 'inspect phrases?'],
            ['trans', null, InputOption::VALUE_NONE, 'inspect translations?'],
            ['all', 'a', InputOption::VALUE_NONE, 'Review all?'],
            ['force', null, InputOption::VALUE_NONE, 'inspect phrases?'],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $package = $this->argument('name');

        $this->info('Inspect package ' . $package);

        if ('all' === $package) {
            foreach (PackageManager::getPackageNames() as $package) {
                $this->reviewPackage($package);
            }

            return 0;
        }

        $this->reviewPackage($package);

        return 0;
    }

    public function reviewPackage(string $package)
    {
        $package = PackageManager::getName($package);
        $this->info('Inspect package ' . $package);
        $reviewAll = $this->option('all');

        if ($reviewAll || $this->option('menuPhrases')) {
            $this->reviewMenuPhrases($package);
        }

        if ($reviewAll || $this->option('menus')) {
            $this->reviewMenus($package);
        }

        if ($reviewAll || $this->option('phrases')) {
            $this->reviewMissingPhrases($package);
        }

        if ($reviewAll || $this->option('drivers')) {
            $this->reviewDrivers($package);
        }

        if ($reviewAll || $this->option('perms')) {
            $this->reviewMissingPermissionPhrases($package);
        }

        if ($reviewAll || $this->option('trans')) {
            $this->reviewTranslations($package);
        }
    }

    /**
     * @param  array<string,?string> $phrases
     * @param  bool                  $dryRun  Dry run
     * @return void
     */
    public function publishSamplePhrases(array $phrases, bool $dryRun = false): void
    {
        $reposition = resolve(PhraseRepositoryInterface::class);

        foreach ($phrases as $key => $text) {
            if ($reposition->addSamplePhrase($key, $text, $dryRun)) {
                $this->info(sprintf('Added new phrases %s', $key));
            }
        }
    }

    /**
     * @param  string $package
     * @return void
     */
    private function reviewMissingPermissionPhrases(string $package): void
    {
        /** @var array<Permission> $rows */
        $rows = resolve(PermissionRepositoryInterface::class)
            ->getModel()->newQuery()
            ->where(['module_id' => PackageManager::getAlias($package)])
            ->get();

        /** @var array<string,?string> $phrases */
        $phrases = [];
        foreach ($rows as $row) {
            $label           = $row->getLabelPhrase();
            $phrases[$label] = null;

            $oldLabel = "{$row->module_id}::phrase.{$row->entity_type}.{$row->action}";
            if (__p($oldLabel) !== $oldLabel) {
                $phrases[$label] = __p($oldLabel);
            }

            $oldLabel = "{$row->module_id}::permission.{$row->entity_type}_{$row->action}";
            if (__p($oldLabel) !== $oldLabel) {
                $phrases[$label] = __p($oldLabel);
            }

            $help           = $row->getHelpPhrase();
            $phrases[$help] = '';
        }
        $this->publishSamplePhrases($phrases, false);
    }

    /**
     * Execute the console command.
     * @param string $package
     */
    private function reviewMissingPhrases(string $package): void
    {
        $path = $package === 'all' ? base_path('packages') : PackageManager::getBasePath($package);

        $this->info(sprintf('Find missing phrases in %s', $path));

        $this->processPhraseInPath($path);

        // reset cache.
        $this->call('optimize:clear');

        if ($package !== 'all') {
            $updatedFiles = resolve(PackageTranslationExporter::class)
                ->exportTranslations($package);
            foreach ($updatedFiles as $updatedFile) {
                $this->info("Updated $updatedFile");
            }
        }
    }

    /**
     * @param       $path
     * @return void
     */
    private function processPhraseInPath($path): void
    {
        $dir_iterator = new RecursiveDirectoryIterator($path);
        /** @var \SplFileInfo[] $iterator */
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

        /** @var array<string,string> $phrases */
        $phrases = [];

        $re = '/(__p|__|__icu)\(\'(?<value>[\w:\.-_]+)\'\)/m';
        foreach ($iterator as $file) {
            $filename = $file->getPathname();
            if ($file->isDir()
                || $file->getExtension() !== 'php'
                || in_array(
                    $file->getBasename(),
                    ['PackageSettingListener.php', 'web.php', 'admin.php', 'config.php', 'mobile.php']
                )) {
                continue;
            }
            $str = mf_get_contents($filename);
            preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

            if (!empty($matches)) {
                foreach ($matches as $match) {
                    $key           = $match['value'];
                    $phrases[$key] = null;
                }
            }

            $this->testInvalidPhrases($file, $str);
        }

        $this->publishSamplePhrases($phrases);
    }

    public function testInvalidPhrases(string $file, string $str)
    {
        $invalids = [
            '/^(.*)((label|description|placeholder|title))\((\'|")([^\']+)(\'|")(.*)$/um',
            '/^(.*)\'(title|description|placeholder|label|message)\'\s*=>\s*\'([^\']+)\'(.*)$/m',

        ];

        $missing = [];

        if (Str::endsWith($file, 'drivers.php')
            || Str::endsWith($file, 'items.php')) {
            return;
        }

        foreach ($invalids as $re) {
            if (preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0)) {
                $missing[] = array_map(function ($arr) {
                    return trim($arr['0']);
                }, $matches);
            }
        }

        $missing = array_unique(Arr::flatten($missing));

        if (!count($missing)) {
            return;
        }

        $this->comment('File: ' . substr($file, strlen(base_path())));
        $this->error(implode(PHP_EOL, $missing));
    }

    /**
     * @param  string $package
     * @return void
     */
    public function reviewDrivers(string $package): void
    {
        $driverRepository = resolve(DriverRepositoryInterface::class);
        $driverRepository->exportDriverToFilesystem($package);
    }

    /**
     * @param  string $package
     * @return void
     */
    public function reviewMenus(string $package): void
    {
        $this->reviewMenuPhrases($package);

        (new PackageInstalledListener())->handle($package);
    }

    /**
     * @param  string $package
     * @return void
     */
    public function reviewTranslations(string $package): void
    {
        resolve(PackageTranslationExporter::class)
            ->exportTranslations($package);
    }
}
