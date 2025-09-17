<?php

namespace MetaFox\Subscription\Tests\Unit\Models;

use MetaFox\Subscription\Models\SubscriptionPackageText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class SubscriptionPackageTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return SubscriptionPackageText::class;
    }
}
