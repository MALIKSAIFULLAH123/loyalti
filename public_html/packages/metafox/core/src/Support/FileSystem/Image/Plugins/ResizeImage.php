<?php

namespace MetaFox\Core\Support\FileSystem\Image\Plugins;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\ResizeImageInterface;
use MetaFox\Storage\Models\StorageFile;
use Ramsey\Uuid\Uuid;

class ResizeImage implements ResizeImageInterface
{
    public const SIZE        = ['75', '100', '150', '240', '500', '1024'];
    public const SQUARE_SIZE = ['50x50', '120x120', '200x200'];

    /**
     * @var mixed|string
     */
    private mixed $serverId = 'photo';

    private int $imageWidth = 0;

    private int $imageHeight = 0;

    /**
     * path to.
     * @var mixed|string
     */
    private string $path = 'photo';

    private ?string $fileName = null;

    private array $extra = [];

    private array $options = [
        'visibility' => 'public',
    ];

    /**
     * @var mixed
     */
    private mixed $image;

    /**
     * @var int[]
     */
    private array $sizes = self::SIZE;

    /**
     * @var int[]
     */
    private array $squareSizes = self::SQUARE_SIZE;

    private ?string $originalName = null;

    private ?string $itemType = null;

    private ?Entity $user = null;

    /**
     * @return string[]
     */
    public function getSizes(): array
    {
        return $this->sizes;
    }

    /**
     * @param  string      $fileName
     * @return ResizeImage
     */
    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileName(): string
    {
        if (!$this->fileName) {
            $this->fileName = Uuid::uuid4() . '_%s.jpg';
        }

        return $this->fileName;
    }

    /**
     * @param  array       $extra
     * @return ResizeImage
     */
    public function setExtra(array $extra): static
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * @param int[] $sizes
     * @return static;
     */
    public function setSizes(array $sizes): self
    {
        $this->sizes = $sizes;

        return $this;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getPrefixPath(): string
    {
        $now = Carbon::now();

        return sprintf('%s/%s', $this->path, $now->format('Y/m/d'));
    }

    /**
     * @return int[]
     */
    public function getSquareSizes(): array
    {
        return $this->squareSizes;
    }

    /**
     * @param int[] $squareSizes
     */
    public function setSquareSizes(array $squareSizes): self
    {
        $this->squareSizes = $squareSizes;

        return $this;
    }

    /**
     * @param  array|string[] $options
     * @return static
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param  mixed|string $serverId
     * @return ResizeImage
     */
    public function setServerId(mixed $serverId): static
    {
        $this->serverId = $serverId;

        return $this;
    }

    public function setImage(mixed $imageFile): static
    {
        if (!file_exists($imageFile)) {
            throw new \InvalidArgumentException('Could not find ' . $imageFile);
        }

        /**
         * Adding "@" to ignore error generated from gd package
         * when trying to read a corrupted JPEG image.
         *
         * Error: "corrupt JPEG data: <some_number> extraneous bytes before marker"
         */
        $imageSize = @getimagesize($imageFile);

        if (!$imageSize || empty($imageSize[0]) || empty($imageSize[1])) {
            throw new \InvalidArgumentException('Could not get image size of ' . $imageFile);
        }

        $this->imageWidth  = $imageSize[0];
        $this->imageHeight = $imageSize[1];
        $this->image       = $imageFile;

        return $this;
    }

    private function getResizeSize(int $orgWidth, int $orgHeight, int $newWidth, int $newHeight): ?array
    {
        if ($newWidth && $orgWidth > $newWidth) {
            $width  = $newWidth;
            $height = floor($orgHeight * $newWidth / $orgWidth);

            if ($newHeight) {
                if ($height > $newHeight) {
                    $height = $newHeight;
                    $width  = floor($orgWidth * $newHeight / $orgHeight);
                }
            }

            return ['width' => $width, 'height' => $height];
        }

        if ($newHeight && $orgHeight > $newHeight) {
            $height = $newHeight;
            $width  = floor($orgWidth * $newHeight / $orgHeight);

            return ['width' => $width, 'height' => $height];
        }

        // Avoid to resize image.
        return null;
    }

    /**
     * @param  string     $variant
     * @param  mixed      $originalId
     * @param  bool       $forceCreate
     * @param  Closure    $callback
     * @return array|null
     */
    public function createThumb(string $variant, mixed $originalId, bool $forceCreate, Closure $callback): mixed
    {
        $newFileName = sprintf($this->getFileName(), $variant);

        $thumbConfig = config('image.thumbs.' . $variant, []);

        if (empty($thumbConfig)) {
            throw new \InvalidArgumentException('Could not found image.thumbs.' . $variant);
        }

        $width     = $thumbConfig['width'] ?? 0;
        $height    = $thumbConfig['height'] ?? 0;
        $extension = $thumbConfig['extension'] ?? 'jpg';
        $quality   = $quality ?? 90;

        if ($width == 0 || $height == 0) {
            $newSize = $this->getResizeSize($this->imageWidth, $this->imageHeight, $width, $height);

            if ($forceCreate && !$newSize) {
                $width  = $this->imageWidth;
                $height = $this->imageHeight;
            } elseif ($newSize) {
                $width  = $newSize['width'];
                $height = $newSize['height'];
            }
        }

        if (!$width || !$height) {
            // trace that there are
            return null;
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'metafox');

        Image::make($this->image)
            ->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->orientate()
            ->save($temporaryPath, $quality);

        $disk = Storage::disk($this->serverId);

        if (!file_exists($temporaryPath)) {
            throw new \InvalidArgumentException('Could not found resized image' . $temporaryPath);
        }

        $path = $disk->putFileAs($this->getPrefixPath(), $temporaryPath, $newFileName, $this->options);

        $fileSize = filesize($temporaryPath);

        if (file_exists($temporaryPath)) {
            @unlink($temporaryPath);
        }

        return $callback(array_merge([
            'origin_id'  => $originalId,
            'storage_id' => $this->serverId,
            //'dir_name'      => $this->getPrefixPath(),
            'path'          => $path,
            'width'         => (int) $width,
            'height'        => (int) $height,
            'file_size'     => $fileSize,
            'extension'     => $extension,
            'variant'       => $variant,
            'mime_type'     => 'image/jpg',
            'original_name' => $this->originalName,
            'item_type'     => $this->itemType,
            'user_id'       => $this->user?->entityId(),
            'user_type'     => $this->user?->entityType(),
            //'url'           => $disk->url($path),
        ], $this->extra));
    }

    public function setOriginalName(string $clientOriginalName): static
    {
        $this->originalName = $clientOriginalName;

        return $this;
    }

    public function setUser(?Entity $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function setItemType(?string $itemType): static
    {
        $this->itemType = $itemType;

        return $this;
    }

    public function createFile(): StorageFile
    {
        $callback = function ($data) {
            $temp = new StorageFile($data);

            $temp->save();

            return $temp;
        };

        $origin = $this->createThumb('origin', null, true, $callback);

        if (!$origin) {
            throw new \InvalidArgumentException('Could not resize image ' . $this->image);
        }

        foreach ($this->sizes as $variant) {
            // calculate size with and height
            try {
                $this->createThumb($variant, $origin->id, false, $callback);
            } catch (\Exception $exception) {
                // log dev to any others
                Log::channel('dev')->info($exception->getMessage());
            }
        }

        return $origin;
    }
}
