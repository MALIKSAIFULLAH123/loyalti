<?php

namespace MetaFox\Rad\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Traits\UserMorphTrait;
use ReflectionMethod;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @codeCoverageIgnore
 */
class MakeTestClass extends Command
{
    protected $name = 'make:test-class';

    private array $packages = [];

    private array $skipControllerMethods = [];
    private array $skipRepositoryMethods = [];

    public function calculateSkipMethods(): void
    {
        $getMethods = fn ($name) => Arr::map((new \ReflectionClass($name))->getMethods(), fn ($method) => $method->name);

        $this->skipControllerMethods = Arr::flatten(Arr::map([
            ApiController::class,
        ], $getMethods));

        $this->skipRepositoryMethods = Arr::flatten(Arr::map([
            AbstractRepository::class, UserMorphTrait::class,
            \MetaFox\Platform\Repositories\AbstractCategoryRepository::class,
        ], $getMethods));
    }

    public function handle(): int
    {
        $package = $this->argument('package');

        $this->packages = $package == 'all' ? array_keys(config('metafox.packages')) : [$package];

        $this->calculateSkipMethods();

        $this->loadFromClasses();

        return Command::SUCCESS;
    }

    public function loadFromClasses(): void
    {
        $classLoader  = require base_path('/vendor/autoload.php');
        $config       = config('metafox.packages');
        $acceptPrefix = Arr::map(
            Arr::map($this->packages, fn ($name) => $config[$name]['namespace']),
            fn ($name) => addslashes($name . '\\')
        );
        $reg         = '/(' . implode('|', $acceptPrefix) . ')/m';
        $projectRoot = base_path('');

        foreach ($classLoader->getClassMap() as $className => $filename) {
            if (!preg_match($reg, $className)) {
                continue;
            }

            $reflectionClass = new \ReflectionClass($className);

            $filename = substr(realpath($filename), strlen($projectRoot) + 1);

            $this->createTest($reflectionClass, $filename);
        }
    }

    public function inspectReflectionClass(\ReflectionClass $ref, array &$data)
    {
        $data['namespace']     = preg_replace('/^(\w+)\\\\(\w+)\\\\(.+)$/m', '$1\\\$2', $ref->getName());
        $data['fullClassName'] = $ref->getName();
        $data['className']     = $ref->getShortName();
        $data['testFile']      = str_replace(
            ['/src/', '.php'],
            ['/tests/Unit/', 'Test.php'],
            substr($ref->getFileName(), strlen(base_path()) + 1)
        );
        $data['dependencies'] = [];
        $data['viewFile']     = null;
        $data['actions']      = [];
        $data['di']           = false;
        $data['skipMethods']  = [];
        $data['testPrefix']   = 'test';

        if ($ref->isSubclassOf(ApiController::class)) {
            $data['di']          = true;
            $data['viewFile']    = 'rad::stubs.tests.unit.http_controller_api_v1_controller_test';
            $data['skipMethods'] = $this->skipControllerMethods;
            $data['testPrefix']  = 'testAction';
        } elseif ($ref->isSubclassOf(AbstractRepository::class)) {
            $data['viewFile']    = 'rad::stubs.tests.unit.respository_eloquent_test';
            $data['skipMethods'] = $this->skipRepositoryMethods;
        }
    }

    public function createTest(\ReflectionClass $reflectionClass, $filename): int
    {
        $data = [];
        $this->inspectReflectionClass($reflectionClass, $data);

        if (!$data['viewFile'] || !$data['testFile']) {
            return 1;
        }

        $testFile = $data['testFile'];

        if (file_exists($testFile)) {
            return 0;
        }

        if (!is_dir($dirname = dirname($testFile))) {
            echo 'Try to make dir ' . $dirname . PHP_EOL;
            if (!mkdir(base_path($dirname), 0755, true)) {
                echo 'Could not make dir ' . $dirname;
                exit(1);
            }
        }

        /*
         * inspect original class then inject here.
         */
        $this->inspectDependencies($reflectionClass, $data);
        $this->inspectTestMethods($reflectionClass, $data);

        $content = view($data['viewFile'], $data)->render();

        file_put_contents($testFile, $content);
        @chmod($testFile, 0644);
        $this->info('Created ' . $testFile);

        return Command::SUCCESS;
    }

    public function inspectTestMethods(\ReflectionClass $reflection, array &$data)
    {
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if (in_array($method->name, $data['skipMethods'])
                || str_starts_with($method->name, '__')
                || str_starts_with($method->name, 'step')
            ) {
                continue;
            }

            $testMethod = [
                'testName'   => $data['testPrefix'] . Str::studly($method->name),
                'name'       => $method->name,
                'request'    => null,
                'id'         => null,
                'parameters' => [],
            ];

            $this->inspectMethodParameters($method, $testMethod, $data['di']);

            $testMethod['arguments'] = implode(', ', Arr::map(array_keys($testMethod['parameters']), fn ($x) => '$' . $x));

            $data['actions'][] = $testMethod;
        }
    }

    public function inspectMethodParameters(ReflectionMethod $method, array &$action, bool $di)
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $name     = $parameter->name;
            $type     = $parameter->getType();
            $typeName = $type?->getName() ?? 'int';

            if (!$type?->isBuiltin() && $di) {
                $action[$name] = $type?->getName();
            } else {
                $parameters[$name] = json_encode(match ($typeName) {
                    'string' => 'input string',
                    'array'  => [],
                    'bool', 'boolean' => false,
                    'int'   => 1,
                    default => null,
                });
            }
        }

        $action['id']         = $parameters['id'] ?? null;
        $action['parameters'] = $parameters;
    }

    public function inspectDependencies(\ReflectionClass $reflectionClass, array &$data)
    {
        $contructor = $reflectionClass->getConstructor();

        foreach ($contructor->getParameters() as $parameter) {
            if ($parameter->name == 'app') {
                continue;
            }
            $data['dependencies'][$parameter->name] = $parameter->getType()?->getName();
        }
    }

    public function getArguments()
    {
        return [
            ['package', InputArgument::REQUIRED, 'Package name , etc metafox/blog'],
        ];
    }
}
