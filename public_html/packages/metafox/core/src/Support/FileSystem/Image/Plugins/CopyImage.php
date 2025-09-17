<?php

namespace MetaFox\Core\Support\FileSystem\Image\Plugins;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use MetaFox\Core\Jobs\DeleteImageJob;

class CopyImage
{
    /**
     * @var int[]
     */
    public array $sizes = ResizeImage::SIZE;
    /**
     * @var int[]
     */
    public array $squareSizes = [];

    public string $imagePath;
    public string $path;
    public string $serverId;
    public bool $isDeleteOrigin;

    /**
     * @param string $imagePath
     * @param string $serverId
     * @param string $path
     * @param bool   $isDeleteOrigin
     * @param int[]  $sizes
     * @param int[]  $squareSizes
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        string $imagePath,
        string $serverId = 'public',
        string $path = 'photo',
        bool $isDeleteOrigin = false,
        array $sizes = [],
        array $squareSizes = []
    ) {
        $this->imagePath = $imagePath;
        $this->path = $path;
        $this->serverId = $serverId;
        $this->isDeleteOrigin = $isDeleteOrigin;

        if (!empty($sizes)) {
            $this->sizes = $sizes;
        }

        if (!empty($squareSizes)) {
            $this->squareSizes = $squareSizes;
        }
    }

    public function copy(): string
    {
        $imagePathConvert = convertImagePath($this->imagePath);

        $newImagePath = $this->getNewImagePath($this->path);
        $newImagePathConvert = convertImagePath($newImagePath);

        $this->processCopy($this->sizes, $imagePathConvert, $newImagePathConvert, false);

        if (!empty($this->squareSizes)) {
            $this->processCopy($this->squareSizes, $imagePathConvert, $newImagePathConvert, true);
        }

        return $newImagePath;
    }

    /**
     * @param int[]  $sizes
     * @param string $imagePathConvert
     * @param string $newImagePathConvert
     * @param bool   $isSquare
     */
    private function processCopy(array $sizes, string $imagePathConvert, string $newImagePathConvert, bool $isSquare): void
    {
        foreach (array_merge([0], $sizes) as $size) {
            $prefix = ($isSquare ? '_square' : '') . ($size == 0 ? '' : "_{$size}");
            $oldPath = sprintf($imagePathConvert, $prefix);
            $newPath = sprintf($newImagePathConvert, $prefix);

            if (Storage::disk((string) $this->serverId)->exists($oldPath)) {
                Storage::disk((string) $this->serverId)->copy($oldPath, $newPath);
            }
        }
        //todo: check keep file local => copy file on local if server_id != local

        if ($this->isDeleteOrigin) {
            DeleteImageJob::dispatch($this->imagePath, $this->serverId, $sizes, $isSquare)->delay(Carbon::now()->addDay());
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getNewImagePath(string $path): string
    {
        $now = Carbon::now();
        $newFileName = md5(mb_pathinfo($this->imagePath, PATHINFO_FILENAME))
            . '-' . $now->timestamp
            . '.' . mb_pathinfo($this->imagePath, PATHINFO_EXTENSION);

        $storagePath = $path . DIRECTORY_SEPARATOR . $now->year . DIRECTORY_SEPARATOR . $now->month . DIRECTORY_SEPARATOR . $now->day;

        return $storagePath . DIRECTORY_SEPARATOR . $newFileName;
    }
}
