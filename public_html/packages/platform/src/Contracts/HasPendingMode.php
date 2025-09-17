<?php

namespace MetaFox\Platform\Contracts;

/**
 * @property int $pending_mode
 * @package MetaFox\Platform\Contracts
 */
interface HasPendingMode extends Entity
{
    public const IS_PENDING_MODE = 1;
}
