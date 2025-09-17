<?php

namespace MetaFox\Core\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use MetaFox\Core\Contracts\AttachmentFileTypeContract;
use MetaFox\Core\Models\AttachmentFileType as Model;

class AttachmentFileType implements AttachmentFileTypeContract
{
    /**
     * @var Collection
     */
    private Collection $attachmentFileTypes;

    /**
     * @return Collection
     */
    public function getAttachmentFileTypes(): Collection
    {
        return $this->attachmentFileTypes;
    }

    public function __construct()
    {
        $this->init();
    }

    public function getCacheName(): string
    {
        return CacheManager::CORE_ATTACHMENT_FILE_TYPE;
    }

    public function clearCache(): void
    {
        Cache::forget($this->getCacheName());
    }

    public function getAllActive(): Collection
    {
        return $this->attachmentFileTypes->where('is_active');
    }

    public function getAllMineTypeActive(): array
    {
        return $this->getAllActive()->pluck('mime_type')->toArray();
    }

    public function getAllExtensionActive(): array
    {
        return $this->getAllActive()->pluck('extension')->toArray();
    }

    protected function init(): void
    {
        $this->attachmentFileTypes = Cache::remember(
            $this->getCacheName(),
            3000,
            function () {
                return Model::query()
                    ->get();
            }
        );
    }
}
