<?php

namespace MetaFox\App\Support;

use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Layout\Jobs\CreateBuild;
use MetaFox\Platform\MetaFoxConstant;
use ZipArchive;

/**
 * Class PackageImporter.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class PackageInstaller
{
    /**
     * @var PackageRepositoryInterface
     */
    private PackageRepositoryInterface $repository;

    /**
     * @param PackageRepositoryInterface $repository
     */
    public function __construct(PackageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $filename
     */
    public function install(string $filename): void
    {
        $archive = new ZipArchive();

        if ($archive->open($filename) !== true) {
            throw new InvalidArgumentException(sprintf('Failed opening archive file "%s"', $filename));
        }

        $packageName = $this->readPackageNameFromArchive($archive);

        // Close the archive, don't throw error here.
        $this->extractSource($archive);

        $archive->close();
        
        Artisan::call('package:install', [
            'package'   => $packageName,
            '--refresh' => false,
        ]);

        $this->uploadToFrontend($filename, __p('layout::phrase.rebuild_package_app_name_action', ['app_name' => $packageName]));
    }

    public function extractSource(ZipArchive $archive): void
    {
        $files       = app('files');
        $tmpRoot     = storage_path('tempdir/install-' . uniqid('t'));
        $backendRoot = sprintf('%s/%s', $tmpRoot, MetaFoxConstant::BACKEND_WRAP_NAME);
        $projectRoot = base_path();

        Log::channel('dev')->info(sprintf('extract to temp: %s', $tmpRoot));

        $files->ensureDirectoryExists($tmpRoot);
        $archive->extractTo($tmpRoot);

        if (!is_dir($backendRoot)) {
            Log::channel('dev')->info('Could not found ' . $backendRoot);

            return;
        }

        $files->copyDirectory($backendRoot, $projectRoot);

        // unlink temp file.
        $files->deleteDirectories($tmpRoot);
    }

    /**
     * @param ZipArchive $archive
     *
     * @return string
     */
    private function readPackageNameFromArchive(ZipArchive $archive): string
    {
        $disk    = app('files');
        $content = null;

        for ($index = 0; $index < $archive->numFiles; $index++) {
            $named = $archive->getNameIndex($index);
            if ($disk->basename($named) === 'composer.json') {
                $content = $archive->getFromName($named);
                break;
            }
        }

        if (!$content) {
            throw new InvalidArgumentException('Failed reading "backend/backend.json" in archive file.');
        }

        $info = json_decode($content, true);

        if (!$info || !isset($info['name'])) {
            throw new InvalidArgumentException('Failed parsing json structure "backend/backend.json" in archive file.');
        }

        return $info['name'];
    }

    /**
     * @param  string $zipFilePath
     * @param  string $reason
     * @return void
     * @params string $reason
     */
    private function uploadToFrontend(string $zipFilePath, string $reason): void
    {
        CreateBuild::dispatch($reason, $zipFilePath);
    }
}
