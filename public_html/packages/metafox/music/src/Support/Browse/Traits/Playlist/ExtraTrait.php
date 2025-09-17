<?php

namespace MetaFox\Music\Support\Browse\Traits\Playlist;

use Illuminate\Support\Arr;
use MetaFox\Platform\ResourcePermission;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;

trait ExtraTrait
{
    use HasExtra {
        getExtra as getMainExtra;
    }

    public function getExtra(): array
    {
        $extra = $this->getMainExtra();

        if (Arr::has($extra, ResourcePermission::CAN_SHARE)) {
            $extra = array_merge($extra, [
                ResourcePermission::CAN_SHARE => Arr::get($extra, ResourcePermission::CAN_SHARE) && $this->resource->total_track > 0
            ]);
        }

        return $extra;
    }
}
