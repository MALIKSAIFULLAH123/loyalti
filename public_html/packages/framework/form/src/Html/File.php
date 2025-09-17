<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class File.
 */
class File extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::FILE);
    }

    public function itemType(string $itemType): self
    {
        return $this->setAttribute('item_type', $itemType);
    }

    public function setItemId(int $itemId = 0): self
    {
        return $this->setAttribute('item_id', $itemId);
    }

    public function previewUrl(?string $previewUrl): self
    {
        return $this->setAttribute('preview_url', $previewUrl);
    }

    public function maxUploadSize(int $maxUploadSize): self
    {
        return $this->setAttribute('max_upload_filesize', $maxUploadSize);
    }

    public function uploadUrl(string $url): self
    {
        return $this->setAttribute('upload_url', $url);
    }

    public function fileTypes(string $acceptTypes): self
    {
        return $this->setAttribute('file_type', $acceptTypes);
    }

    public function thumbnailSizes(mixed $sizes): self
    {
        return $this->setAttribute('thumbnail_sizes', $sizes);
    }

    /**
     * @param  array<string, mixed> $number
     * @return $this
     */
    public function maxUploadFileSize(array $number): self
    {
        return $this->setAttribute('max_upload_filesize', $number);
    }

    public function maxFiles(int $numberOfFiles): self
    {
        return $this->setAttribute('maxFiles', $numberOfFiles);
    }

    /**
     * @param string $accepts
     *
     * @return $this
     */
    public function accepts(string $accepts): self
    {
        return $this->setAttribute('accept', $accepts);
    }

    public function acceptFail(string $message): self
    {
        return $this->setAttribute('acceptFail', $message);
    }

    /**
     * Set 'acceptWhen' attribute.
     * This attribute supports a way to address the accept value base on logical conditions of other fields.
     *
     * @param  array<string, array<int, mixed>> $condition sample: ['image/*' => [ 'and' , [ 'truthy', 'fieldA'], ['eq',
     *                                                     'fieldB', 1 ] , ...]]
     * @return $this
     */
    public function acceptWhen(array $condition): self
    {
        return $this->setAttribute('acceptWhen', $condition);
    }

    public function isVideoUploadAllowed(bool $allowed): self
    {
        return $this->setAttribute('isVideoUploadAllowed', $allowed);
    }

    public function storageId(string $name): self
    {
        return $this->setAttribute('storage_id', $name);
    }

    public function allowDrop(bool $flag = true): self
    {
        return $this->setAttribute('allowDrop', $flag);
    }
}
