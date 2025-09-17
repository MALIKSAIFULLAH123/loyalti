<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Http\UploadedFile;

interface MetaFoxFileTypeInterface
{
    public function isAllowType(string $type): bool;

    /**
     * @param  string $type
     * @param  bool   $useConverter
     * @return string
     */
    public function getMimeTypeFromType(string $type, bool $useConverter = true): string;

    /**
     * @param  UploadedFile $file
     * @param  string       $type
     * @return bool
     */
    public function verifyMime(UploadedFile $file, string $type): bool;

    /**
     * This method return how many bytes should a type can be uploaded to server.
     *
     * @param  string $type
     * @return int
     */
    public function getFilesizePerType(string $type): int;

    /**
     * @param  string $type
     * @return float
     */
    public function getFilesizeInMegabytes(string $type): float;

    /**
     * @param  string|null $mimeType
     * @return string|null
     */
    public function getTypeByMime(?string $mimeType): ?string;

    /**
     * @param  string|null $mimeType
     * @param  string      $fileType
     * @return bool
     */
    public function verifyMimeTypeByType(?string $mimeType, string $fileType = 'photo'): bool;

    /**
     * @param  string $fileType
     * @return string
     */
    public function transformFileType(string $fileType): string;

    /**
     * @param int $bytes
     */
    public function getFilesizeReadableString(int $bytes): string;

    /**
     * @param  string        $type
     * @param  bool          $useConverter
     * @return array<string>
     */
    public function getAllowableTypes(string $type, bool $useConverter): array;

    /**
     * @param  UploadedFile $file
     * @return bool
     */
    public function isForbiddenFile(UploadedFile $file): bool;
}
