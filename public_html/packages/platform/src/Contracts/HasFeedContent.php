<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasFeedContent
 * @package MetaFox\Platform\Contracts
 */
interface HasFeedContent
{
    /**
     * @return string|null
     */
    public function getFeedContent(): ?string;
}
