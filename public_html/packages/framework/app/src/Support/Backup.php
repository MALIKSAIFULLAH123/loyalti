<?php

namespace MetaFox\App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Backup
{
    /**
     * @param string $modulePath
     * @param string $moduleName
     *
     * @return void
     */
    private function backupPackage(string $modulePath, string $moduleName): void
    {
        $disk = Storage::disk('local');

        if (!File::exists($modulePath)) {
            return;
        }

        /** @var PackageExporter $expoter */
        $exporter   = resolve(PackageExporter::class);
        $exportPath = $exporter->exportProcess($moduleName, $modulePath);

        if (!$exportPath) {
            return;
        }

        $backupPath = $disk->path(config('app.backup_module_path')) . DIRECTORY_SEPARATOR .
            $moduleName . '.' . File::extension($exportPath);

        $dirname = dirname($backupPath);
        if (!File::isDirectory($dirname)) {
            File::makeDirectory($dirname, 0755, true);
        }
        File::move($exportPath, $backupPath);
    }
}
