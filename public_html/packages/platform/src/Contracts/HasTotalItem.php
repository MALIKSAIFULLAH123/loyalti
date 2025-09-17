<?php

namespace MetaFox\Platform\Contracts;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;

/**
 * @property int $total_item
 */
interface HasTotalItem extends Entity, HasAmounts
{
    public function incrementTotalItem(): void;

    public function decrementTotalItem(): void;
}
