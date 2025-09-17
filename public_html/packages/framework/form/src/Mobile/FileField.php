<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class FileField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::FILE)
            ->multiple(false)
            ->uploadUrl('/file')
            ->variant('standard-inlined');
    }

    public function maxFiles(int $maxFiles): self
    {
        return $this->setAttribute('max_files', $maxFiles);
    }

    public function maxFilesDescription(string $description): self
    {
        return $this->setAttribute('maxFilesDescription', $description);
    }

    /**
     * @param  int|array<int, mixed> $maxSize
     * @return self
     */
    public function maxUploadSize(mixed $maxSize): self
    {
        return $this->setAttribute('max_upload_filesize', $maxSize);
    }

    public function itemType(string $type): self
    {
        return $this->setAttribute('item_type', $type);
    }

    public function fileType(string $type): self
    {
        return $this->setAttribute('file_type', $type);
    }

    public function thumbnailSizes(mixed $sizes): self
    {
        return $this->setAttribute('thumbnail_sizes', $sizes);
    }

    public function previewUrl(?string $previewUrl): self
    {
        return $this->setAttribute('preview_url', $previewUrl);
    }

    public function uploadUrl(string $url): self
    {
        return $this->setAttribute('upload_url', $url);
    }

    /**
     * @param  string $accepts
     * @return $this
     */
    public function accept(string $accepts): self
    {
        return $this->setAttribute('accept', $accepts);
    }

    public function acceptFail(string $message): self
    {
        return $this->setAttribute('acceptFail', $message);
    }

    public function storageId(string $name): self
    {
        return $this->setAttribute('storage_id', $name);
    }
}
