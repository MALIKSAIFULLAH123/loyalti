<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PointTransaction as Model;
use MetaFox\ActivityPoint\Support\ActivityPoint;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PointTransactionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PointTransactionItem extends \MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction\PointTransactionItem
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $data = parent::toArray($request);

        $color = ActivityPoint::ADDED_COLOR;

        if ($this->resource->is_subtracted) {
            $color = ActivityPoint::SUBTRACTED_COLOR;
        }

        $data = array_merge($data, [
            'sx'     => [
                'points' => [
                    'color' => $color,
                ],
            ],
            'action' => strip_tags(Arr::get($data, 'action', '')),
        ]);

        return $data;
    }
}
