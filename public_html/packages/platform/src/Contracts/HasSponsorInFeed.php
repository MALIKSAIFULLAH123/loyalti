<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasSponsor.
 *
 * @property int $sponsor_in_feed
 * @deprecated
 */
interface HasSponsorInFeed
{
    public const IS_SPONSOR_IN_FEED = 1;
    public const IS_UN_SPONSOR_IN_FEED = 0;
}
