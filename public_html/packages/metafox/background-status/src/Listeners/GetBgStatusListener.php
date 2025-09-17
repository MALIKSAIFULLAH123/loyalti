<?php

namespace MetaFox\BackgroundStatus\Listeners;

use MetaFox\BackgroundStatus\Models\BgsBackground;

/**
 * Class GetBgStatusListener.
 * @ignore
 * @codeCoverageIgnore
 */
class GetBgStatusListener
{
    /**
     * @param  int                $bgStatusId
     * @return BgsBackground|null
     */
    public function handle(int $bgStatusId): ?BgsBackground
    {
        return BgsBackground::query()->find($bgStatusId);
    }
}
