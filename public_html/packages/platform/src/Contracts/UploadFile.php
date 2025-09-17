<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use MetaFox\Core\Models\Attachment;
use MetaFox\Storage\Models\StorageFile;

interface UploadFile
{
    /**
     * @return ResizeImageInterface
     */
    public function getResizeImage(): ResizeImageInterface;

    /**
     * Thumb sizes configure at "./config/image.php".
     *
     * @param string[] $sizes
     *
     * @return self
     */
    public function setThumbSizes(array $sizes): self;

    /**
     * @param  User|null $user
     * @return $this
     */
    public function setUser(?User $user): static;

    /**
     * @param  string|null $itemType
     * @return UploadFile
     */
    public function setItemType(?string $itemType): static;

    /**
     * Get storage server id.
     *
     * @return string
     */
    public function getStorage(): string;

    /**
     * Set storage by server_id.
     *
     * @param mixed $storageId
     *
     * @return self
     */
    public function setStorage(mixed $storageId): self;

    /**
     * @param  string $path
     * @return static
     */
    public function setPath(string $path): static;

    /**
     * @param  UploadedFile $uploadedFile
     * @return StorageFile
     */
    public function storeFile(UploadedFile $uploadedFile): StorageFile;

    /**
     * @param UploadedFile[] $files
     *
     * @return StorageFile[]
     */
    public function storeFiles(array $files): array;

    /**
     * @param UploadedFile[] $files
     *
     * @return array<Attachment>
     */
    public function storeAttachments(array $files): array;

    /**
     * @param UploadedFile $file
     *
     * @return Attachment
     */
    public function storeAttachment(UploadedFile $file): Attachment;

    /**
     * @param  string       $realpath
     * @param  string|null  $newFileName
     * @param  bool         $isTestMode
     * @return UploadedFile
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function pathToUploadedFile(string $realpath, string $newFileName = null, bool $isTestMode = true);

    /**
     * @param string $imageBase64
     *
     * @return UploadedFile
     */
    public function convertBase64ToUploadedFile(string $imageBase64): UploadedFile;

    /**
     * @param string $realpath
     *
     * @return string
     */
    public function convertImageToBase64(string $realpath): string;

    /**
     * Convert local file path to uploaded file.
     *
     * @param  string       $realpath
     * @param  string|null  $originalName
     * @param  bool         $testMode
     * @return UploadedFile
     */
    public function asUploadedFile(string $realpath, string $originalName = null, bool $testMode = false): UploadedFile;

    /**
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function isImage(UploadedFile $file): bool;

    /**
     * @param  UploadedFile $file
     * @return bool
     */
    public function isAnimatedImage(UploadedFile $file): bool;

    /**
     * @param int $id
     *
     * @return bool
     * @throws ModelNotFoundException
     */
    public function rollUp(int $id): bool;

    /**
     * @param int $id
     *
     * @return StorageFile
     * @throws ModelNotFoundException
     */
    public function getFile(int $id): StorageFile;

    /**
     * @param  ?int $tempFileId
     * @param  bool $rollUp
     * @return ?int
     */
    public function getFileId(?int $tempFileId, bool $rollUp = false): ?int;

    /**
     * @param  string|null $base64
     * @return $this
     */
    public function setBase64(?string $base64): static;

    /**
     * @return string|null
     */
    public function getBase64(): ?string;

    /**
     * @param  UploadedFile      $file
     * @return UploadedFile|null
     */
    public function convertFile(UploadedFile $file): ?UploadedFile;

    /**
     * @param UploadedFile         $file
     * @param array<string, mixed> $params
     */
    public function uploadWithParams(UploadedFile $file, array $params = []): StorageFile;
}
