<?php

namespace MetaFox\Rad\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @codeCoverageIgnore
 */
class MakeTestApi extends Command
{
    protected $name = 'make:test-api';

    public function handle(): int
    {
        $package = $this->argument('package');

        $acceptPackages = $package == 'all' ? array_keys(config('metafox.packages')) : [$package];

        $this->loadAdminUrls($acceptPackages, 'v1');

        return Command::SUCCESS;
    }

    public function loadAdminUrls(array $acceptPackages, string $ver)
    {
        Artisan::call('route:list', ['--json' => true, '--sort' => 'uri']);
        $routes = json_decode(Artisan::output(), true);

        $methodPriority = fn ($a) => match ($a) {
            'GET'    => 1,
            'POST'   => 2,
            'PUT'    => 3,
            'PATCH'  => 4,
            'DELETE' => 5,
        };

        $getMethod = fn ($method) => Arr::first(explode('|', $method));

        uasort($routes, function ($a, $b) use ($methodPriority, $getMethod) {
            $w1 = $methodPriority($getMethod($a['method']));
            $w2 = $methodPriority($getMethod($b['method']));

            if ($w1 == $w2) {
                return strcmp($a['uri'], $b['uri']);
            }

            return $w1 - $w2;
        });

        $packageMap = Arr::pluck(config('metafox.packages'), 'name', 'namespace');
        $pathMap    = Arr::pluck(config('metafox.packages'), 'path', 'name');
        $aliasMap   = Arr::pluck(config('metafox.packages'), 'alias', 'name');
        $result     = [];

        foreach ($routes as $route) {
            extract($route);
            $altUrl  = str_replace('api/{ver}/', 'api/' . $ver . '/', $uri);
            $isAdmin = str_contains($uri, 'api/{ver}/admincp/');

            $method = $getMethod($method);
            if (!str_starts_with($uri, 'api/') || !str_contains($action, '@')) {
                continue;
            }

            $namespace = implode('\\', Arr::only(explode('\\', $action, 3), [0, 1]));
            $package   = $packageMap[$namespace] ?? null;

            if (!$package || !in_array($package, $acceptPackages)) {
                continue;
            }

            $testName = Str::studly(sprintf('%sApiTest', $aliasMap[$package]));
            // generate filename based on controller name.
            $providerName = Str::snake(preg_replace('/(.+)\\\(\w+)Controller@(.+)/', '$2', $action), '-');
            $dataName     = sprintf('%s %s', $method, $altUrl);
            $see          = '\\' . str_replace(['@', '\\Api\\'], ['::', "\\Api\\$ver\\"], $action);

            if (!Arr::has($result, $package)) {
                Arr::set($result, $package, ['providers' => [], 'controllers' => []]);
            }

            // assign path to provider
            if (!Arr::has($result, $temp = "$package.providers.$providerName")) {
                Arr::set($result, $temp, [
                    'path'   => sprintf('%s/tests/fixtures/api/%s/%s', $pathMap[$package], $ver, $providerName),
                    'routes' => [],
                ]);
            }

            // assign controllers
            if (!Arr::has($result, $temp = "$package.controllers.$testName")) {
                Arr::set($result, $temp, [
                    'testName'  => $testName,
                    'directory' => sprintf('%s/tests/fixtures', $pathMap[$package]),
                    'path'      => sprintf('%s/tests/Api/%s/%s.php', $pathMap[$package], $ver, $testName),
                    'namespace' => $namespace,
                    'providers' => [],
                ]);
            }

            $result[$package]['controllers'][$testName]['providers'][] = sprintf('api/%s/%s.php', $ver, $providerName);

            $result[$package]['providers'][$providerName]['routes'][$dataName] = [
                '@see'       => $see,
                'package'    => $package,
                'middleware' => json_encode($middleware),
                'url'        => $altUrl,
                'method'     => $method,
                'user'       => $isAdmin ? '\'admin\'' : 'null',
                'data'       => [],
                'status'     => 200,
                'skipTest'   => 'false',
            ];
        }

        foreach ($result as $package => $info) {
            if (!in_array($package, $acceptPackages)) {
                continue;
            }

            foreach ($info['controllers'] as $data) {
                $filename = $data['path'];
                if (!is_dir($dir = dirname($filename))) {
                    app('files')->makeDirectory($dir, 0755, true);
                }

                if (file_exists($filename)) {
                    $this->comment(sprintf('Skip %s', $data['path']));
                    continue;
                }

                $this->comment(sprintf('Created %s', $data['path']));
                $data['providers'] = array_unique($data['providers']);
                file_put_contents(base_path($filename), view('rad::phpunit.tests.api_test_case', $data)->render());
            }

            foreach ($info['providers'] as $data) {
                $filename = $data['path'];
                $filename = $filename . '.php';

                if (!is_dir($dir = dirname($filename))) {
                    app('files')->makeDirectory($dir, 0755, true);
                }

                if (file_exists(base_path($filename))) {
                    $this->comment(sprintf('Skipped %s', $filename));
                    continue;
                }

                $this->comment(sprintf('Created %s', $filename));
                file_put_contents(base_path($filename), view('rad::phpunit.tests.api_data_provider', $data)->render());
            }
        }
    }

    /*
     * sort out api fixtures file.
     */
    public function loadCurrentFixtures()
    {
        $files       = [];
        $projectRoot = base_path();
        $patterns    = $patterns ?? [
            'packages/*/*/tests/fixtures/api/v1/*.json',
            'packages/*/*/tests/fixtures/api/v1/*.php',
        ];

        array_walk($patterns, function ($pattern) use (&$files, $projectRoot) {
            $dir = $projectRoot . '/' . $pattern;
            foreach (glob($dir) as $file) {
                $files[] = substr($file, strlen($projectRoot) + 1);
            }
        });
    }

    public function getArguments()
    {
        return [
            ['package', InputArgument::REQUIRED, 'Package name , etc metafox/blog'],
        ];
    }
}
