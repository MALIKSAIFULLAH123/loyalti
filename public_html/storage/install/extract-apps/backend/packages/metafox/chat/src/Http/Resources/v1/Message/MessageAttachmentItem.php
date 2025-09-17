<?php

namespace MetaFox\Chat\Http\Resources\v1\Message;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Models\Attachment as Model;
use MetaFox\Core\Support\FileSystem\FileSizeManager;

/**
 * Class MessageAttachmentItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class MessageAttachmentItem extends JsonResource
{
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
            'width'          => $file?->width,
            'height'         => $file?->height,
        ];
    }
}
