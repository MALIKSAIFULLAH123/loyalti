<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasTotalView.
 * @property int $total_view
 */
interface HasTotalView extends HasAmounts
{
    public function incrementTotalView(): void;
}
