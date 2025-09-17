<?php

namespace App\Console\Commands;

use Composer\Console\Input\InputOption;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Models\Driver;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\AdminSettingForm;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Menu\Models\Menu;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Notifications\Notification as NotificationAlias;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Resource\GridConfig;
use MetaFox\SEO\Models\Meta;
use MetaFox\Sms\Contracts\ServiceInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\Console\Input\InputArgument;
use Tests\TestCase;

class ReviewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'review';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Review package agrement policy';

    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    private array $reviewTasks = [];

    private ?string $package = null;

    private bool $continue = false;

    public const LOG_FILE = 'storage/logs/review.log';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->continue = $this->option('continue');
        $this->package  = $this->argument('package');

        $this->createLogger();
        $packages = config('metafox.packages');

        $fix = $this->option('fix');

        if ($fix && config('app.env') !== 'local') {
            $this->error('--fix option allow on production mode');

            return Command::FAILURE;
        }

        $this->info('Running `composer dumpautoload -o` first.');
        $this->addTask('Review table core_drivers - p1', fn () => $this->reviewDriverClasses() == 0);
        $this->addTask('Review missing core_seo_meta phrases - p1', fn () => $this->checkMissingSeoPhrase() == 0);
        $this->addTask('Review menu has title attribute', fn () => $this->checkMenuHasTitle() == 0);
        $this->addTask('Review missed controller actions', fn () => $this->reviewRoutes() == 0);
        $this->checkLoadClasses();

        if ($this->package) {
            $name = $this->package;
            $this->addTask('Review dependency ' . $name, fn () => $this->checkDependencies([$name]) == 0);
        } else {
            $this->addTask('Review dependencies', fn () => $this->checkDependencies(array_keys($packages)) == 0);
        }

        $this->processTasks($this->reviewTasks);

        preg_match_all('/\[[\d\-\ :]{19}\] \w+.ERROR:/m', mf_get_contents(base_path(self::LOG_FILE)), $matches);

        $exitCode = empty($matches) ? 0 : 1;

        if ($exitCode) {
            $this->getOutput()->writeln(sprintf(
                'view <error>%s</error> for more further information!',
                base_path(self::LOG_FILE)
            ));
        } else {
            $this->getOutput()->writeln(sprintf(
                'view <info>%s</info> for more further information!',
                base_path(self::LOG_FILE)
            ));
        }

        return $exitCode;
    }

    public function createLogger()
    {
        $logfile = base_path(self::LOG_FILE);

        if (file_exists($logfile)) {
            @unlink($logfile);
        }

        $this->logger = Log::build([
            'driver' => 'single',
            'path'   => $logfile,
            'level'  => 'debug',
        ]);
    }

    public function processTasks($tasks)
    {
        collect($tasks)
            ->each(fn ($task, $description) => $this->components->task($description, $task));
    }

    public function checkLoadClasses()
    {
        /** @var \Composer\Autoload\ClassLoader $classLoader */
        $classLoader = require base_path('/vendor/autoload.php');

        $this->logger->debug(sprintf('Found %s classes', count($classLoader->getClassMap())));
        $packagePrefix = $this->package ? PackageManager::getNamespace($this->package) : null;

        $entityClasses     = [];
        $adminSettingForms = [];
        $formClasses       = [];
        $testCasesClasses  = [];

        foreach ($classLoader->getClassMap() as $className => $filename) {
            if (!str_starts_with($className, 'MetaFox')) {
                continue;
            }

            if ($packagePrefix && !str_starts_with($className, $packagePrefix)) {
                continue;
            }

            $ref = new ReflectionClass($className);

            if ($ref->isSubclassOf(AdminSettingForm::class)) {
                $adminSettingForms[] = $className;
            }

            if ($ref->isSubclassOf(Entity::class)) {
                $entityClasses[] = $className;
            }

            if ($ref->isSubclassOf(FormRequest::class)) {
                $formClasses[] = $className;
            }

            if ($ref->isSubclassOf(TestCase::class) && str_ends_with($className, 'Test')) {
                $testCasesClasses[$className] = $filename;
            }
        }

        $this->addTask('Review settings forms', fn () => $this->reviewSiteSettingForms($adminSettingForms) == 0);
        $this->addTask('Review entities', fn () => $this->reviewEntityClasses($entityClasses) == 0);
        $this->addTask('Review tests ', fn () => $this->reviewTestCaseClasses($testCasesClasses) == 0);

        if ($this->option('clean')) {
            $this->addTask('Clean request class', fn () => $this->reviewMessRequest($formClasses) == 0);
        }
    }

    public function addTask($name, $fn)
    {
        if (isset($this->reviewTasks[$name])) {
            throw new \RuntimeException("Duplicated task $name");
        }
        $this->reviewTasks[$name] = $fn;
    }

    public function reviewSiteSettingForms($haystack): int
    {
        $query = Driver::query()
            ->where(['type' => 'form-settings', 'resolution' => 'admin']);

        if ($this->package) {
            $query->where('package_id', $this->package);
        }

        $driverClasses = $query
            ->pluck('driver')
            ->toArray();

        $missings = array_diff($haystack, $driverClasses);

        collect($missings)->each(fn (
            $name
        ) => $this->logger->notice(sprintf('Missed core_driver(type=form-settings, driver=%s)', $name)));

        return 0;
    }

    /**
     * @param          $className
     * @return ?Driver
     */
    public function getDriverByClass($className)
    {
        /** @var ?Driver $driver */
        $driver = Driver::query()->where('driver', $className)->first();

        return $driver;
    }

    /**
     * @throws \ReflectionException
     */
    public function reviewDriverClasses()
    {
        $query = Driver::query()
            ->orderBy('type')
            ->whereNotNull('driver');

        if ($this->package) {
            $query->where('package_id', '=', $this->package);
        }

        /** @var Builder $drivers */
        $drivers = $query->orderBy('module_id')
            ->get(['driver', 'type', 'name']);

        $response = static::SUCCESS;

        foreach ($drivers as $driver) {
            $className = $driver->driver;
            $type      = $driver->type;

            if ($className && !class_exists($className)) {
                $this->logger->error('Missed class ' . $className);
                $response = static::FAILURE;
                continue;
            }

            $ref = new ReflectionClass($className);

            if ($type === 'entity' && !$ref->isSubclassOf(Entity::class)) {
                $this->logger->error(sprintf('driver entity %s must be sub-class of %s', $className, Entity::class));
            }

            if ($type === 'data-grid' && !$ref->isSubclassOf(GridConfig::class)) {
                $this->logger->error(sprintf(
                    'driver entity %s must be sub-class of %s',
                    $className,
                    GridConfig::class
                ));
            }

            if ($type === 'entity-user' && !$ref->isSubclassOf(ContractUser::class)) {
                $this->logger->error(sprintf(
                    'driver entity %s must be sub-class of %s',
                    $className,
                    ContractUser::class
                ));
            }

            if (in_array($type, [
                'form', 'form-cache', 'form-captcha', 'form-logger', 'form-mailer', 'form-queue', 'form-service',
                'form-session', 'form-settings', 'form-storage', 'form-user-gateway',
            ]) && !$ref->isSubclassOf(AbstractForm::class)) {
                $this->logger->error(sprintf(
                    'driver entity %s must be sub-class of %s',
                    $className,
                    AbstractForm::class
                ));
            }

            if ($type === 'json-collection' && !$ref->isSubclassOf(ResourceCollection::class)) {
                $this->logger->error(sprintf(
                    'driver entity %s must be sub-class of %s',
                    $className,
                    ResourceCollection::class
                ));
            }
            if ($type === 'json-resource' && !$ref->isSubclassOf(JsonResource::class)) {
                $this->logger->error(sprintf(
                    'driver entity %s must be sub-class of %s',
                    $className,
                    JsonResource::class
                ));
            }

            if ($type === 'notification' && !$ref->isSubclassOf(NotificationAlias::class)) {
                $this->logger->error(sprintf(
                    'driver entity %s must be sub-class of %s',
                    $className,
                    NotificationAlias::class
                ));
            }
            if ($type === 'package-mobile' && !$ref->isSubclassOf(\MetaFox\Platform\Resource\MobileSetting::class)) {
                $this->logger->error(sprintf(
                    'driver entity %s must be sub-class of %s',
                    $className,
                    \MetaFox\Platform\Resource\MobileSetting::class
                ));
            }
            if ($type === 'package-setting') {
                // skip step.
            }
            if ($type === 'package-web' && !$ref->isSubclassOf(\MetaFox\Platform\Resource\WebSetting::class)) {
                $this->logger->error(sprintf(
                    'driver entity %s must be sub-class of %s',
                    $className,
                    \MetaFox\Platform\Resource\WebSetting::class
                ));
            }
            if ($type === 'policy-resource' && !$ref->isSubclassOf(\MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface::class)) {
                //                $this->logger->error(sprintf(
                //                    '%s must be sub-class of %s',
                //                    $className,
                //                    \MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface::class
                //                ));
            }
            if ($type === 'policy-rule' && !$ref->isSubclassOf(\MetaFox\Platform\Support\PolicyRuleInterface::class)) {
                //                $this->logger->info(sprintf(
                //                    'driver entity %s must be sub-class of %s',
                //                    $className,
                //                    \MetaFox\Platform\Support\PolicyRuleInterface::class
                //                ));
            }
            if ($type === 'sms-service' && !$ref->isSubclassOf(ServiceInterface::class)) {
                $this->logger->error(sprintf(
                    'driver entity %s must be sub-class of %s',
                    $className,
                    NotificationAlias::class
                ));
            }
            if ($type === 'resource-mobile' && !$ref->isSubclassOf(\MetaFox\Platform\Resource\MobileSetting::class)) {
                $this->logger->error(sprintf(
                    'driver entity %s must be sub-class of %s',
                    $className,
                    \MetaFox\Platform\Resource\MobileSetting::class
                ));
            }
            if ($type === 'resource-web' && !$ref->isSubclassOf(\MetaFox\Platform\Resource\WebSetting::class)) {
                $this->logger->error(sprintf(
                    'driver entity %s must be sub-class of %s',
                    $className,
                    \MetaFox\Platform\Resource\WebSetting::class
                ));
            }
        }

        return $response;
    }

    private function reviewEntityClasses(array $haystack)
    {
        $query = Driver::query()->where(['type' => 'entity']);

        if ($this->package) {
            $query->where('package_id', $this->package);
        }
        $drivers = $query->pluck('driver')->toArray();

        $missings = array_diff($haystack, $drivers);
        collect($missings)->each(fn ($name) => $this->logger->notice(
            sprintf('Missed core_driver(type=entity, driver=%s)', $name)
        ));

        return 0;
    }

    public function checkDependencies(array $packages)
    {
        $hasError = 0;
        foreach ($packages as $package) {
            if (!$this->checkImportDepdenency($package)) {
                $hasError = 1;
            }
        }

        return $hasError;
    }

    private function checkImportDepdenency(string $packageName): int
    {
        $packages = config('metafox.packages');
        $info     = $packages[$packageName] ?? null;

        if (!$info) {
            $this->logger->error('Failed localting package ' . $packageName);
        }

        $directory = base_path($info['path']) . '/src';

        if (!is_dir($directory)) {
            return static::SUCCESS;
        }

        $projectRoot = base_path();
        $namespace   = $info['namespace'] ?? null;
        $namespaces  = array_map(function ($str) {
            return str_replace('\\', '\\\\', $str);
        }, array_filter(array_map(function ($item) use ($namespace) {
            return $item['core'] || $namespace === $item['namespace'] ? null : $item['namespace'] . '\\';
        }, $packages), function ($value) {
            return $value;
        }));

        $regexp = '/use (' . implode('|', $namespaces) . ')/m';

        $directoryIterator = new \RecursiveDirectoryIterator($directory);
        /** @var \SplFileInfo[] $iterator */
        $iterator = new \RecursiveIteratorIterator($directoryIterator);
        $response = static::SUCCESS;

        foreach ($iterator as $file) {
            $matches = null;
            if (!$file->isFile() || $file->getExtension() != 'php') {
                continue;
            }

            if (!preg_match_all($regexp, mf_get_contents($file->getPathname()), $matches, PREG_SET_ORDER)) {
                continue;
            }

            $baseFile = substr($file->getPathname(), strlen($projectRoot));
            $this->logger->notice(sprintf('%s: Unxepected [%s]', $baseFile, implode(',', array_unique($matches[0]))));

            $response = static::FAILURE;
        }

        return $response;
    }

    /**
     * @return array
     */
    public function getExtendPackages(): array
    {
        return array_filter(array_map(function ($info) {
            return (bool) $info['core'];
        }, array_values(config('metafox.packages'))));
    }

    /**
     * @param  array    $packages
     * @return string[]
     */
    public function getCoreNamespaces(array $packages): array
    {
        return array_filter(array_map(function ($info) {
            return $info['core'] ? $info['namespace'] : null;
        }, $packages), function ($value) {
            return $value;
        });
    }

    public function checkLoadDrivers()
    {
        /** @var Driver[] $drivers */
        $drivers = Driver::query()
            ->orderBy('type')
            ->orderBy('module_id')
            ->get();
    }

    public function checkMenuHasTitle()
    {
        $query = Menu::query();

        if ($this->package) {
            $query->where('package_id', $this->package);
        }

        /** @var Menu[] $menus */
        $menus = $query->get();

        foreach ($menus as $menu) {
            if ($menu->title) {
                continue;
            }

            $this->logger->notice(sprintf('Missed menu title (name=%s, module_id=%s)', $menu->name, $menu->module_id));
        }

        return 0;
    }

    public function checkMissingSeoPhrase(): int
    {
        $loadMissingPhrase = function ($column, $package) {
            $query = Meta::query()
                ->select([$column, 'key'])
                ->leftJoin('phrases', $column, '=', 'key')
                ->whereNotNull($column)
                ->whereNull('key');

            if ($package) {
                $query->where('core_seo_meta.package_id', $package);
            }

            return $query->pluck($column)->toArray();
        };

        $fix = true;

        $missedPhrases = [];
        foreach (['phrase_title', 'phrase_heading', 'phrase_keywords', 'phrase_description'] as $column) {
            $missedPhrases[] = $loadMissingPhrase($column, $this->package);
        }

        $missedPhrases = array_unique(Arr::flatten($missedPhrases, 1));

        $count = count($missedPhrases);

        if (!$count) {
            return 0;
        }

        $this->logger->warning(sprintf(
            'Missing %d phrases (%s)',
            count($missedPhrases),
            implode(', ', $missedPhrases)
        ));

        if (!$fix) {
            return 1;
        }

        $confirmed = $this->confirm(sprintf('Adding %d missing seo phrases?', $count));

        if (!$confirmed) {
            return 1;
        }

        $phraseRepo = resolve(PhraseRepositoryInterface::class);

        /*
         * Add empty phrase to avoid translation.
         */
        foreach ($missedPhrases as $phraseKey) {
            $this->comment('Adding phrase ' . $phraseKey);
            $phraseRepo->addSamplePhrase($phraseKey, '', 'en', false, false);
        }

        return 1;
    }

    public function getArguments()
    {
        return [
            ['package', InputArgument::OPTIONAL, 'Package Id, etc: metafox/user'],
        ];
    }

    public function getOptions()
    {
        return [
            ['fix', null, InputOption::VALUE_NONE, 'Automatic fix (run on APP_ENV=local)'],
            ['clean', null, InputOption::VALUE_NONE, 'Check to clear class'],
            ['continue', null, InputOption::VALUE_NONE, 'Continue mode'],
        ];
    }

    public function reviewMessRequest(array $formClasses): int
    {
        $response = 0;
        foreach ($formClasses as $class) {
            $command = sprintf("grep --exclude=\"*Test.php\" -rnw packages -e 'use %s'", addslashes($class));
            $output  = null;
            exec($command, $output);

            if (empty($output)) {
                $response = 1;
                $this->logger->notice(sprintf('Un-use class %s: %s', $class, implode(PHP_EOL, $output)));
            }
        }

        return $response;
    }

    public function reviewTestCaseClasses(array $classNames)
    {
        $controllerLog = fopen(base_path('storage/logs/review.controllers.md'), 'w');
        $requestLog    = fopen(base_path('storage/logs/review.requests.md'), 'w');

        fwrite($controllerLog, 'Move api tests to ./fixtures.' . PHP_EOL);

        foreach ($classNames as $className => $filename) {
            $ref = new ReflectionClass($className);

            if (str_contains($className, '\Unit\Http\Requests')
                && $ref->isSubclassOf(TestCase::class)
                && !$ref->hasMethod('requestName')) {
                fwrite($requestLog, sprintf('%s', $className) . PHP_EOL);
            }

            if (str_contains($className, '\Unit\Http\Controllers')
                && $ref->isSubclassOf(TestCase::class)
                && strpos(mf_get_contents($filename), 'API_PREFIX') != false) {
                fwrite($controllerLog, $className . PHP_EOL);
            }
        }

        fclose($controllerLog);
        fclose($requestLog);

        return 0;
    }

    public function reviewRoutes()
    {
        $hasError = 0;
        Artisan::call('route:list', ['--json' => true]);

        $output = Artisan::output();

        $routes            = json_decode($output, true);
        $lastApiController = null;
        $lastRef           = null;

        foreach ($routes as $route) {
            if (!str_contains($route['action'], '@') || !str_contains($route['action'], 'Api')) {
                continue;
            }
            [$gatewayController, $action] = explode('@', $route['action']);
            $apiController                = str_replace('\\Api\\', '\\Api\\v1\\', $gatewayController);

            if (str_contains($gatewayController, '\\Api\\v1\\')) {
                $this->logger->error($gatewayController . ' should bind to route via version gateway' . PHP_EOL);
                $this->confirm('continue');
                continue;
            }

            $ref = $lastRef;

            if ($lastApiController != $apiController) {
                $lastApiController = $apiController;
                $ref               = $lastRef = new ReflectionClass($apiController);
            }

            // report if there are route but not method.
            if (!$ref->hasMethod($action)) {
                $this->logger->error(sprintf(
                    'Missed %s@%s for declared route %s',
                    $apiController,
                    $action,
                    $route['uri']
                ));
                $hasError = 1;
            }
        }

        return $hasError;
    }
}
