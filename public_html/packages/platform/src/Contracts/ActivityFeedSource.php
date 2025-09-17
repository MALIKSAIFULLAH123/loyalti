<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

use MetaFox\Platform\Support\FeedAction;

/**
 * Interface ActivityFeedSource.
 *
 * @property \MetaFox\Activity\Models\Feed|null $activity_feed
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
interface ActivityFeedSource
{
    /**
     * @return FeedAction|null
     */
    public function toActivityFeed(): ?FeedAction;
}
