<?php

namespace MetaFox\Activity\Http\Resources\v1\ActivityHistory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Activity\Models\ActivityHistory as Model;
use MetaFox\Activity\Traits\HasTagTrait;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ActivityHistoryItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ActivityHistoryItem extends JsonResource
{
    use HasTagTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $phrase = MetaFoxConstant::EMPTY_STRING;

        if ($this->resource->phrase) {
            /**
             * In case phrase does not belong to Activity app, it will be whole phrase of other app.
             */
            $phrase = __p($this->resource->phrase);

            if ($phrase == $this->resource->phrase) {
                $phrase = __p('activity::phrase.' . $this->resource->phrase);
            }
        }

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'feed',
            'resource_name' => $this->resource->entityType(),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'phrase'        => $phrase,
            'extra'         => $this->resource->extra,
            'content'       => $this->getParsedContent(),
            'created_at'    => $this->resource->created_at,
        ];
    }
}
