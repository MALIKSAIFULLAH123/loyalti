<?php

namespace MetaFox\HealthCheck\Checks;

use Illuminate\Support\Arr;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CheckFilesystemPermission extends Checker
{
    public function check(): Result
    {
        $result = $this->makeResult();
        $this->checkWritableDirectory($result);
        $this->checkPermissions($result);
        $this->checkUnexpectedDirectories($result);

        if ($result->okay()) {
            $result->success(__p('health-check::phrase.filesystem_is_writable'));
        }

        return $result;
    }

    public function checkWritableDirectory(Result $result)
    {
        $directories = [
            './storage',
            './storage/logs',
            './bootstrap/cache',
            './public',
            './public/install',
            './storage/app/web',
            './storage/app/public',
            './storage/framework',
        ];

        foreach ($directories as $dir) {
            $path = realpath(base_path($dir));

            if (!$path || !is_dir($path)) {
                continue;
            }

            if (!is_writable($path)) {
                $result->error(__p('health-check::phrase.directory_is_not_writable', ['value' => $dir]));
            }
        }
    }

    public function checkPermissions(Result $result)
    {
        $directories = [
            './app',
            './config',
            './database',
            './packages',
            './public',
            './resources',
            './routes',
            './storage/app/public/assets',
            './storage/app/logs',
        ];

        foreach ($directories as $dir) {
            $this->checkFilePermissions($result, $dir, '0644', '0755');
        }

        if ($result->okay()) {
            $result->success(__p('health-check::phrase.checked_file_directory_permissions'));
        }

        return $result;
    }

    public function checkUnexpectedDirectories(Result $result)
    {
        $directories = [
            'public/install' => [
                'title'   => __p('health-check::phrase.security'),
                'message' => __p('health-check::phrase.the_installation_path_public_install'),
            ],
        ];

        foreach ($directories as $dir => $error) {
            if (is_string($error)) {
                // no specific error detail is defined
                $dir = $error;
            }

            $title   = '';
            $path    = base_path($dir);
            $message = __p('health-check::phrase.unexpected_directory_exists', ['value' => $dir]);
            if (is_array($error)) {
                $title   = Arr::get($error, 'title', '');
                $message = Arr::get($error, 'message', $message);
            }

            if (is_dir($path)) {
                $actions = [
                    [
                        'name'    => 'remove',
                        'title'   => __p('health-check::phrase.remove'),
                        'action'  => 'health-check/resolve',
                        'payload' => [
                            'apiUrl'    => '/admincp/health-check/resolve/unexpected-directory',
                            'apiMethod' => 'PATCH',
                        ],
                        'config' => [
                            'variant' => 'link',
                            'size'    => 'small',
                            'sx'      => [
                                'height' => 'auto',
                            ],
                        ],
                    ],
                ];

                $result->error($message, $title, $actions);
            }
        }
    }

    public function getName()
    {
        return __p('health-check::phrase.file_permissions');
    }

    private function checkFilePermissions(Result $result, string $dir, $filePermission, $dirPermission)
    {
        $path = realpath(base_path($dir));

        if (!$path || !is_dir($path)) {
            return;
        }

        $flags             = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $directoryIterator = new RecursiveDirectoryIterator($path, $flags);
        /** @var \SplFileInfo[] $iterator */
        $iterator = new RecursiveIteratorIterator($directoryIterator);

        $prefix      = strlen(base_path(''));
        $limit       = 10;
        $failedCount = 0;
        $found       = [];

        foreach ($iterator as $file) {
            $pathname = $file->getPathname();
            $perms    = substr(sprintf('%o', $file->getPerms()), -4);

            if ($file->isFile() &&
                $file->getExtension() === 'php'
                && $perms != $filePermission) {
                if ($failedCount < $limit) {
                    $found[] = __p('health-check::phrase.expected_pathname_permission_is_file_permission_but_actually_is_perms', [
                        'pathname'       => substr($pathname, $prefix),
                        'filePermission' => $filePermission,
                        'perms'          => $perms,
                    ]);
                    $failedCount ++;
                }
            } elseif ($file->isDir() && $perms != $dirPermission) {
                if ($failedCount < $limit) {
                    $found[] = __p('health-check::phrase.expected_pathname_permission_is_file_permission_but_actually_is_perms', [
                        'pathname'       => substr($pathname, $prefix),
                        'filePermission' => $dirPermission,
                        'perms'          => $perms,
                    ]);
                }
                $failedCount ++;
            }
        }

        if (!$failedCount) {
            return;
        }

        $result->error(__p('health-check::phrase.failed_checking_file_permission', ['value' => $path]));

        foreach ($found as $item) {
            $result->error($item);
        }

        if ($failedCount > $limit) {
            $result->error(__p('health-check::phrase.and_others', ['value' => $failedCount - $limit]));
        }
    }
}
