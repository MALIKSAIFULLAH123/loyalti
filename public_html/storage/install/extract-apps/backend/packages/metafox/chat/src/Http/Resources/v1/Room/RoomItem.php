<?php

namespace MetaFox\Chat\Http\Resources\v1\Room;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Chat\Models\Room;
use MetaFox\Chat\Traits\RoomInfoTraits;

/**
 * Class RoomItem.
 * @property Room $resource
 */
class RoomItem extends RoomDetail
{
}
