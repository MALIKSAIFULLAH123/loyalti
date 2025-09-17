<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Notification\Repositories;

use MetaFox\Notification\Models\WebpushSubscription;
use MetaFox\Notification\Repositories\Contracts\WebpushSubscriptionRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

class WebpushSubscriptionRepository extends AbstractRepository implements WebpushSubscriptionRepositoryInterface
{
    public function model()
    {
        return WebpushSubscription::class;
    }
}
