<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdatePreloader extends Command
{
    /**
     * @var int
     *          limit number of files to preload
     *          Increase this number affected to memory per child process.
     */
    public const LIMIT_FILE = 3000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'preload:gen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate preload files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $root       = base_path();
        $rootLength = strlen($root);
        $output     = [
            '<?php',
            '/*',
            ' * Readme',
            ' * Do not manual edit this file!',
            ' * Run "php artisan preload:gen" to update preload',
            ' */',
            'require_once __DIR__.\'/vendor/autoload.php\';',
        ];

        /** @var \Composer\Autoload\ClassLoader $classLoader */
        $classLoader = require base_path('vendor/autoload.php');

        $invert = [];
        foreach ($classLoader->getClassMap() as $className => $filename) {
            $invert[substr(realpath($filename), $rootLength)] = $className;
        }

        foreach ($invert as $file => $className) {
            if ($this->shouldPreloadFile($file, $className)) {
                require_once $root . $file;
            }
        }

        $total      = 0;
        $scripts    = $this->getPreloadFromOpcacheScripts();
        $preloadMap = [];

        // append preload from now

        if (file_exists($fix = base_path('storage/framework/preload.txt'))) {
            foreach (explode(PHP_EOL, mf_get_contents($fix)) as $file) {
                if ($file && array_key_exists($file, $invert)) {
                    $preloadMap[$file] =  true;
                }
            }
        }

        array_unshift($scripts, realpath('./vendor/autoload.php'));
        foreach ($scripts as $filename) {
            $file     = substr(realpath($filename), $rootLength);
            $testFile = strtolower($file);

            if (!$file) {
                continue;
            }

            if (!array_key_exists($file, $invert)) {
                continue;
            }

            if ($this->shouldExclude($testFile)) {
                continue;
            }

            $total += 1;
            $preloadMap[$file] = true;

            // keep limit to safe memory
            if ($total > self::LIMIT_FILE) {
                break;
            }
        }

        foreach ($preloadMap as $file => $_) {
            $output[] = sprintf('require_once __DIR__.\'%s\';', $file);
        }

        file_put_contents(base_path('preload.php'), implode(PHP_EOL, $output));

        $this->info(sprintf(
            'Update ./preload.php, preload %s/%s files',
            number_format(count($preloadMap)),
            number_format(count($scripts))
        ));

        return Command::SUCCESS;
    }

    public function shouldExclude($file)
    {
        foreach (['command', 'console', 'admin', 'test'] as $str) {
            if (str_contains($file, $str)) {
                return true;
            }
        }

        if (!str_ends_with($file, '.php')) {
            return true;
        }

        return false;
    }

    public function shouldInclude($file)
    {
        return str_contains($file, '/src/http/controllers')
        || str_contains($file, '/src/listeners/')
        || str_contains($file, '/src/providers/')
        || str_contains($file, '/src/models/')
        || str_contains($file, '/src/observers/')
        || str_contains($file, '/src/policies/')
        || str_contains($file, '/src/repositories/');
    }

    public function shouldPreloadFile(string $file, string $className): ?bool
    {
        $file = strtolower($file);
        foreach (['/app/exceptions', '/app/http', '/app/models', '/app/provider', '/app/platform'] as $needed) {
            if (str_starts_with($file, $needed) && !$this->shouldExclude($file)) {
                return true;
            }
        }

        $prefix = [
            '/packages/platform',
            '/packages/framework',
            '/packages/metafox/core',
            '/packages/metafox/user',
            '/packages/metafox/activity',
            '/packages/metafox/authorization',
            '/packages/metafox/friend',
            '/packages/framework/captcha',
            '/packages/framework/quota',
            '/packages/framework/form',
            '/packages/framework/localize',
            '/packages/framework/seo',
            '/packages/framework/word',
            '/packages/framework/yup',
            '/packages/metafox/core',
            '/packages/metafox/user',
            '/packages/metafox/attachment',
            '/packages/metafox/authorization',
            '/packages/framework/regex',
            '/packages/framework/mfa',
            '/packages/metafox/friend',
            '/packages/metafox/hashtag',
            '/packages/metafox/notification',
            '/packages/metafox/photo',
            '/packages/metafox/search',
            '/packages/framework/cache',
            '/packages/framework/flood',
            '/packages/metafox/profile',
            '/packages/metafox/rewrite',
            '/packages/metafox/storage',
            '/packages/metafox/event',
            '/packages/metafox/blog',
            '/packages/metafox/photo',
            '/packages/metafox/video',
            '/packages/metafox/marketplace',
        ];

        foreach ($prefix as $needed) {
            if (str_starts_with($file, $needed) && !$this->shouldExclude($file) && $this->shouldInclude($file)) {
                return true;
            }
        }

        return false;
    }

    public function getPreloadFromOpcacheScripts()
    {
        if (!function_exists('opcache_get_status')) {
            throw new \RuntimeException('opcache_get_status is disabled');
        }

        $status = opcache_get_status();

        if (!is_array($status)) {
            throw new \RuntimeException('opcache_get_status() failed');
        }

        $scripts = $status['scripts'];

        return array_keys($scripts);
    }
}
