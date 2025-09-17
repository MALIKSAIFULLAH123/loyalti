<?php

namespace MetaFox\Rad\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class MakePhpunitXml extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:phpunit-xml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Help to update phpunit.xml';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $corePaths = $this->getCorePaths();
        $appPaths  = $this->getAppPaths();
        $allPaths  = array_unique([...$corePaths, ...$appPaths]);

        $pathMap = [
            '' => $allPaths,
            // 'core' => $corePaths,
            // 'apps' => $appPaths,
        ];

        $folders = [
            'tests'        => 'tests/Unit',
//            'requests'     => 'tests/Unit/Http/Requests',
//            'resources'    => 'tests/Unit/Http/Resources',
//            'controllers'  => 'tests/Unit/Http/Controllers',
//            'models'       => 'tests/Unit/Models',
//            'policies'     => 'tests/Unit/Policies',
//            'repositories' => 'tests/Unit/Repositories',
//            'features'     => 'tests/Feature',
//            'apis'         => 'tests/Api/v1',
//            'supports'     => ['tests/Unit/Support', 'tests/Unit/Supports', 'tests/Unit/Rule', 'tests/Unit/Rules'],
        ];

        $suiteMap   = [];
        $testsuites = '';

        foreach ($folders as $suffix => $dirs) {
            $dirs = is_array($dirs) ? $dirs : [$dirs];
            foreach ($pathMap as $name => $paths) {
                $suiteName = trim($name . '.' . $suffix, '.');
                $paths     = Arr::flatten(Arr::map($dirs, fn ($dir) => array_filter(
                    Arr::map($paths, fn ($x) => $x . '/' . $dir),
                    fn ($x) => is_dir(base_path($x))
                )));

                if (!empty($paths)) {
                    $suiteMap[$suiteName] = $paths;
                    $testsuites .= sprintf('<testsuite name="%s">%s</testsuite>', $suiteName, implode(
                        PHP_EOL,
                        Arr::map($paths, fn ($x) => sprintf('<directory suffix="Test.php">%s</directory>', $x))
                    ));
                }
            }
        }

        $content = view('rad::phpunit/phpunit', [
            'suiteMap'   => $suiteMap,
            'testsuites' => $testsuites,
            'coveragePaths'=> $this->getCoveragPaths(),
            'excludeSourcePaths'=> $this->getExcludeSourcePaths()
        ])->render();

        file_put_contents(base_path('phpunit.xml'), $content);

        return Command::SUCCESS;
    }

    public function getExcludeSourcePaths(): array{
        return glob('packages/*/*/tests');
    }

    public function getCoveragPaths()
    {
        return glob('packages/*/*/src');
    }

    public function getCorePaths()
    {
        $allPackages = array_values(config('metafox.packages'));
        $corePaths   = array_filter(Arr::map($allPackages, function ($info) {
            return @$info['core'] ? $info['path'] : null;
        }), fn ($x) => !empty($x));

        sort($corePaths);
        array_unshift($corePaths, 'packages/platform/tests');
        $corePaths = array_unique($corePaths);

        return $corePaths;
    }

    public function getAppPaths()
    {
        $allPackages = array_values(config('metafox.packages'));
        $corePaths   = array_filter(Arr::map($allPackages, function ($info) {
            return @$info['core'] ? null : $info['path'];
        }), fn ($x) => !empty($x));

        sort($corePaths);
        $corePaths = array_unique($corePaths);

        return $corePaths;
    }
}
