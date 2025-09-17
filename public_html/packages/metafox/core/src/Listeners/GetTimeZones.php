<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Localize\Repositories\TimezoneRepositoryInterface;

/**
 * Class getTimezones.
 * @deprecated
 */
class GetTimeZones
{
    /**
     * @return array<int, mixed>
     */
    public function handle(): array
    {
        return resolve(TimezoneRepositoryInterface::class)->getTimeZones();
    }
}
