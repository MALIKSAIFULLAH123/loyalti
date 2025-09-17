<?php

namespace MetaFox\Importer\Http\Resources\v1\Bundle\Admin;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Importer\Models\Bundle as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class BundleItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class BundleItem extends JsonResource
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
            'id'          => $this->id,
            'filename'    => $this->filename,
            'resource'    => $this->resource,
            'status'      => $this->status,
            'priority'    => $this->priority,
            'total_entry' => $this->total_entry,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'start_time'  => $this->convertDateTime($this->start_time),
            'end_time'    => $this->convertDateTime($this->end_time),
            'links'       => [
                'entry' => sprintf('/importer/bundle/%s/entry/browse', $this->id),
            ],
        ];
    }

    protected function convertDateTime(?string $dateTime): ?string
    {
        if (null === $dateTime) {
            return null;
        }

        return Carbon::parse($dateTime)->format('c');
    }
}
