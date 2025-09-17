<?php

namespace MetaFox\Attachment\Http\Resources\v1\FileType\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Models\AttachmentFileType as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class FileTypeItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class FileTypeItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'resource_name' => $this->resource->entityType(),
            'module_name'   => 'attachment',
            'extension'     => $this->resource->extension,
            'mime_type'     => $this->resource->mime_type,
            'is_active'     => $this->resource->is_active,
            'extra'         => $this->getExtra(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getExtra(): array
    {
        return [
            'can_delete' => true,
        ];
    }
}
