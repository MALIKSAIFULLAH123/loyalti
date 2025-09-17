<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasLocationCheckin.
 *
 * @description use for resource has location checkin ability.
 * @property string|null $location_name
 * @property float|null  $location_latitude
 * @property float|null  $location_longitude
 * @property string|null $location_address
 * @property bool        $show_map_on_feed
 * @package     MetaFox\Platform\Contracts
 */
interface HasLocationCheckin
{
    /**
     * [address, lat, lng].
     * @return array<mixed>
     */
    public function toLocation(): array;
}
