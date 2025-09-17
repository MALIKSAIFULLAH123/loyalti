<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasFeature.
 * @property int    $is_featured
 * @property string $featured_at
 * @deprecated
 * @package MetaFox\Platform\Contracts
 */
interface HasFeature
{
    public const IS_FEATURED = 1;

    public const FEATURED_AT_COLUMN = 'featured_at';
}
