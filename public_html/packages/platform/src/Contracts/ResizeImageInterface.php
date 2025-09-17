<?php

namespace MetaFox\Platform\Contracts;

use Closure;
use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Storage\Models\StorageFile;

interface ResizeImageInterface
{
    /**
     * @return string[]
     * @deprecated
     */
    public function getSizes(): array;

    /**
     * @param  string[] $sizes
     * @return static
     */
    public function setSizes(array $sizes): self;

    /**
     * @return int[]
     */
    public function getSquareSizes(): array;

    /**
     * @param  array|string[] $options
     * @return static
     */
    public function setOptions(array $options): static;

    /**
     * @param  string $path
     * @return static
     */
    public function setPath(string $path): static;

    public function getPrefixPath(): string;

    /**
     * @param  mixed|string $serverId
     * @return static
     */
    public function setServerId(mixed $serverId): static;

    /**
     * @param  mixed  $imageFile
     * @return static
     */
    public function setImage(mixed $imageFile): static;

    /**
     * @param int[] $squareSizes
     * @deprecated
     */
    public function setSquareSizes(array $squareSizes): self;

    /**
     * @param  string $fileName
     * @return static
     */
    public function setFileName(string $fileName): static;

    /**
     * @param  array       $extra
     * @return ResizeImage
     */
    public function setExtra(array $extra): static;

    /**
     * @param  string     $variant
     * @param  mixed      $originalId
     * @param  bool       $forceCreate
     * @param  Closure    $callback
     * @return array|null
     */
    public function createThumb(string $variant, mixed $originalId, bool $forceCreate, Closure $callback): mixed;

    /**
     * @return StorageFile
     */
    public function createFile(): StorageFile;

    /**
     * @param  string $clientOriginalName
     * @return $this
     */
    public function setOriginalName(string $clientOriginalName): static;

    /**
     * @param  mixed $user
     * @return $this
     */
    public function setUser(?Entity $user): static;

    /**
     * @param  string|null $itemType
     * @return $this
     */
    public function setItemType(?string $itemType): static;
}
