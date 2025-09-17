<?php

namespace MetaFox\Attachment\Http\Resources\v1\Attachment;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Attachment\Traits\HasAttachmentExtraTrait;
use MetaFox\Core\Models\Attachment as Model;
use MetaFox\Core\Support\FileSystem\FileSizeManager;

/**
 * Class AttachmentItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AttachmentItem extends JsonResource
{
    use HasAttachmentExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $file = $this->resource->file;

        return [
            'id'             => $this->resource->entityId(),
            'module_name'    => 'attachment',
            'resource_name'  => $this->resource->entityType(),
            'file_name'      => $file?->original_name,
            'is_image'       => $file?->is_image,
            'image'          => $file?->images,
            'download_url'   => $file?->url,
            'file_size_text' => FileSizeManager::convertFileSizeToText($file?->file_size),
            'extra'          => $this->getExtra(),
        ];
    }
}
