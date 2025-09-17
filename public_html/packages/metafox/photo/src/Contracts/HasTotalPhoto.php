<?php

namespace MetaFox\Photo\Contracts;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;

/**
 * @property int $total_photo
 */
interface HasTotalPhoto extends Entity, HasAmounts
{
    // TODO: remove total_photo,
    // only use total_item to count both photos and videos in album
}
