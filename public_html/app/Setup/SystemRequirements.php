<?php

namespace App\Setup;

use App\ProcessHelper;

class SystemRequirements
{
    /**
     * @var string
     */
    private $projectRoot;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    public function __construct()
    {
        $this->projectRoot   = dirname(dirname(__DIR__));
        $this->processHelper = ProcessHelper::factory();
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        $result = true;

        $response = [
            'sections' => [
                $this->getSystemRequirements(),
                $this->getRecommendations(),
            ],
        ];

        foreach ($response['sections'] as $section) {
            foreach ($section['items'] as $item) {
                if (!$item['value'] && $item['severity'] === 'error') {
                    $result = false;
                }
            }
        }

        $response['result'] = $result;

        /*
         * rollup error first
         */
        foreach ($response['sections'] as $key => $section) {
            usort($section['items'], function ($a, $b) {
                return $a['value'] > $b['value'] ? 1 : 0;
            });
            $response['sections'][$key] = $section;
        }

        return $response;
    }

    /**
     * @return array[]
     * @link https://laravel.com/docs/9.x/deployment#server-requirements
     */
    private function getSystemRequirements()
    {
        $hasDb = extension_loaded('pdo_mysql') || extension_loaded('pdo_pgsql');

        $pathToPhp = 'F:\php8.1.13';

        $items = [
            [
                'label'    => sprintf('PHP Version from 8.1 to 8.3 - Current Version %s (%s) ', phpversion(), php_sapi_name()),
                'value'    => version_compare(phpversion(), '8.1', '>=') && version_compare(phpversion(), '8.4', '<'),
                'severity' => 'error',
            ],
            [
                'label'    => "PHP Path $pathToPhp",
                'value'    => (bool) $pathToPhp,
                'severity' => 'error',
            ],
            [
                'label'    => 'JSON PHP Extension',
                'value'    => extension_loaded('json'),
                'url'      => 'https://www.php.net/manual/en/book.json.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'BCMath PHP Extension',
                'value'    => extension_loaded('bcmath'),
                'url'      => 'https://www.php.net/manual/en/book.bc.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
    'label'    => 'Process Control Extension',
    'value'    => true,
    'url'      => 'https://www.php.net/manual/en/book.pcntl.php',
    'severity' => 'error',
    'skip'     => true,
],
[
    'label'    => 'POSIX Extension',
    'value'    => true,
    'url'      => 'https://www.php.net/manual/en/book.posix.php',
    'severity' => 'error',
    'skip'     => true,
],

            [
                'label'    => 'Ctype PHP Extension',
                'value'    => extension_loaded('ctype'),
                'url'      => 'https://www.php.net/manual/en/book.ctype.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'Exif PHP Extension',
                'value'    => extension_loaded('exif'),
                'url'      => 'https://www.php.net/manual/en/book.exif.php',
                'severity' => 'error',
            ],
            [
                'label'    => 'GD PHP Extension',
                'value'    => extension_loaded('gd'),
                'url'      => 'https://www.php.net/manual/en/book.image.php',
                'severity' => 'error',
            ],
            [
                'label'    => 'Sodium PHP Extension',
                'value'    => extension_loaded('sodium'),
                'url'      => 'https://www.php.net/manual/en/book.sodium.php',
                'severity' => 'error',
            ],
            [
                'label'    => 'Intl PHP Extension',
                'value'    => extension_loaded('intl'),
                'url'      => 'https://www.php.net/manual/en/book.intl.php',
                'severity' => 'error',
            ],
            [
                'label'    => 'cURL PHP Extension',
                'value'    => extension_loaded('curl'),
                'link'     => 'https://php.net/manual/en/book.curl.php',
                'severity' => 'error',
            ],
            [
                'label'    => 'DOM PHP Extension',
                'value'    => extension_loaded('dom'),
                'url'      => 'https://php.net/manual/en/book.dom.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'OpenSSL PHP Extension',
                'value'    => extension_loaded('openssl'),
                'url'      => 'https://www.php.net/manual/en/book.openssl.php',
                'severity' => 'error',
            ],
            [
                'label'    => 'Sockets PHP Extension',
                'value'    => extension_loaded('sockets'),
                'url'      => 'https://www.php.net/manual/en/book.sockets.php',
                'severity' => 'error',
            ],
            [
                'label'    => 'Phar PHP Extension',
                'value'    => extension_loaded('phar'),
                'url'      => 'https://www.php.net/manual/en/intro.phar.php',
                'severity' => 'error',
            ],
            [
                'label'    => 'Database Drivers (MySql/Postgres)',
                'value'    => $hasDb,
                'severity' => 'error',
            ],
            [
                'label'    => 'Mbstring PHP Extension',
                'value'    => extension_loaded('mbstring'),
                'url'      => 'https://php.net/manual/en/book.mbstring.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'Fileinfo PHP Extension',
                'value'    => extension_loaded('fileinfo'),
                'url'      => 'https://php.net/manual/en/book.fileinfo.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'PCRE PHP Extension',
                'value'    => extension_loaded('pcre'),
                'url'      => 'https://www.php.net/manual/en/pcre.configuration.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'Tokenizer PHP Extension',
                'value'    => extension_loaded('tokenizer'),
                'url'      => 'https://www.php.net/manual/en/book.tokenizer.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'XML PHP Extension',
                'value'    => extension_loaded('xml'),
                'url'      => 'https://php.net/manual/en/book.xml.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'Zip/Archive PHP Extension',
                'value'    => extension_loaded('zip'),
                'url'      => 'https://www.php.net/manual/en/book.zip.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'Function exec, proc_open, proc_close, proc_get_status',
                'value'    => function_exists('exec') && function_exists('proc_open') && function_exists('proc_close') && function_exists('proc_get_status'),
                'link'     => 'https://php.net/manual/en/book.exec.php',
                'severity' => 'error',
            ],
            [
                'label'    => 'Function chmod, copy, is_file, filesize, rename',
                'value'    => function_exists('chmod') && function_exists('copy')
                    && function_exists('is_file') && function_exists('filesize') && function_exists('rename'),
                'link'     => 'https://www.php.net/manual/en/book.filesystem.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'Function basename, link, symlink, linkinfo',
                'value'    => function_exists('basename') && function_exists('link')
                    && function_exists('symlink') && function_exists('linkinfo'),
                'link'     => 'https://www.php.net/manual/en/book.filesystem.php',
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'Folder ./storage/* is writable ',
                'value'    => $this->ensureWritable('/storage'),
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'Folder ./storage/* is writable ',
                'value'    => $this->ensureWritable('/storage/logs'),
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'Folder ./public/* is writable to create symlinks',
                'value'    => $this->ensureWritable('/public'),
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'Folder ./bootstrap/cache/* is writable',
                'value'    => $this->ensureWritable('/bootstrap/cache'),
                'severity' => 'error',
                'skip'     => true,
            ],
            [
                'label'    => 'Folder ./config/* is writable',
                'value'    => $this->ensureWritable('/config/metafox.php'),
                'severity' => 'error',
                'skip'     => true,
            ],
        ];

        return [
            'title' => 'System Requirements',
            'items' => $items,
        ];
    }

    /**
     * @param       $dirOrFileName
     * @return bool
     */
    private function ensureWritable($dirOrFileName)
    {
        $path = $this->projectRoot . $dirOrFileName;

        if (!is_dir($path) && !file_exists($path)) {
            return is_writable(dirname($path));
        }

        if (is_writable($path)) {
            return true;
        }

        return is_writable($path);
    }

    /**
     * @return array
     */
    private function getRecommendations()
    {
        $hasAPC = extension_loaded('apc') || extension_loaded('apcu');

        $items = [
            [
                'label'    => 'APC User Cache',
                'value'    => $hasAPC,
                'url'      => 'https://www.php.net/manual/en/book.apcu.php',
                'severity' => 'warning',
            ],
            [
                'label'    => 'Redis Cache',
                'value'    => class_exists('Redis'),
                'url'      => 'https://github.com/phpredis/phpredis',
                'severity' => 'warning',
            ],
            [
                'label'    => 'ImageMagick PHP Extension',
                'value'    => extension_loaded('imagick'),
                'url'      => 'https://www.php.net/manual/en/book.imagick.php',
                'severity' => 'warning',
            ],
        ];

        return [
            'title' => 'Recommendations',
            'items' => $items,
        ];
    }
}
