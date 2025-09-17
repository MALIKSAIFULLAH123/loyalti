<?php

namespace MetaFox\Importer\Http\Resources\v1\Entry\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Importer\Models\Entry as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class EntryItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class EntryItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $resource = $this->getResource();

        return [
            'id'           => $this->id,
            'source'       => $this->source,
            'ref_id'       => $this->ref_id,
            'status'       => $this->status,
            'last_updated' => $this->updated_at,
            'resource'     => $resource ? sprintf('%s#%s', $resource->entityType() ?? 'core_privacy', $resource->id) : null,
        ];
    }
}
