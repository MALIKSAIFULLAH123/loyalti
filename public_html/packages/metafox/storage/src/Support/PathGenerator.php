<?php

namespace MetaFox\Storage\Support;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Ramsey\Uuid\Uuid;

class PathGenerator
{
    public function getExtension(string|UploadedFile $path): string
    {
        return is_string($path) ? File::extension($path) : $path->getClientOriginalExtension();
    }

    public function getOriginalName(string|UploadedFile $file): ?string
    {
        return is_string($file) ? null : $file->getClientOriginalName();
    }

    public function rootDir(string $path): string
    {
        return explode('/', trim($path, '/'))[0];
    }

    public function fileName(string $extension, string $root = null, bool $hasVariants = false): string
    {
        $now = Carbon::now();

        return trim(
            sprintf(
                '%s/%s/%s-%s/%s.%s',
                $root ?? '',
                $now->year,
                $now->month,
                $now->day,
                Uuid::uuid4() . ($hasVariants ? '_%s' : ''),
                $extension
            ),
            '/.'
        );
    }

    public function copyPath(string $fromPath): string
    {
        return $this->fileName($this->getExtension($fromPath), $this->rootDir($fromPath));
    }
}
