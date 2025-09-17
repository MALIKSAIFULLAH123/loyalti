<?php

namespace MetaFox\Importer\Http\Resources\v1\Log\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Importer\Models\Log as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class LogItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class LogItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'level'     => $this->level_name,
            'message'   => $this->message,
            'env'       => $this->resource->env,
            'timestamp' => $this->timestamp,
        ];
    }
}
