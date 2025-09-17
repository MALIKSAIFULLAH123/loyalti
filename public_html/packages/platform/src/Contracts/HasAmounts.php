<?php

namespace MetaFox\Platform\Contracts;

/**
 * Trait HasAmounts
 * @package MetaFox\Platform\Contracts
 * @method  mixed incrementOrDecrement($column, $amount, $extra, $method)
 */
interface HasAmounts
{
    public function incrementAmount(string $column, int $amount = 1): int;

    public function decrementAmount(string $column, int $amount = 1): int;
}
