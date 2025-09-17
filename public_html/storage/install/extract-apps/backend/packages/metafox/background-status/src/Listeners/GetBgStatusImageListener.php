<?php

namespace MetaFox\BackgroundStatus\Listeners;

use MetaFox\BackgroundStatus\Models\BgsBackground;

/**
 * Class GetBgStatusImageListener
 * @ignore
 * @codeCoverageIgnore
 */
class GetBgStatusImageListener
{
    /**
     * @param int $bgStatusId
     *
     * @return false|array<string, mixed>|null
     */
    public function handle(int $bgStatusId)
    {
        /** @var BgsBackground $bgStatus */
        $bgStatus = BgsBackground::query()->find($bgStatusId);

        if (null == $bgStatus) {
            return false;
        }

        return $bgStatus->images;
    }
}
