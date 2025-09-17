<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionComparison\Admin;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin\SubscriptionComparisonItem as Resource;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin\SubscriptionComparisonItemCollection as ResourceCollection;
use MetaFox\Subscription\Models\SubscriptionComparison as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_test.stub
*/

/**
 * Class SubscriptionComparisonItemTest.
 */
class SubscriptionComparisonItemTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function testCreate(): array
    {
        $this->asAdminUser();
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $model = Model::factory()->setUser($user)->setOwner($user)->create();

        $model->refresh();

        $this->assertNotEmpty($model->entityId());

        return [$model, $user];
    }

    /**
     * @depends testCreate
     *
     * @param array<mixed> $params
     */
    public function testResource(array $params)
    {
        /**
         * @var Model $model
         * @var User  $user
         */
        [$model, $user] = $params;

        $this->be($user);

        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);
    }

    /**
     * @depends testCreate
     *
     * @param array<mixed> $params
     */
    public function testCollection(array $params)
    {
        /**
         * @var Model $model
         * @var User  $user
         */
        [$model, $user] = $params;

        $this->be($user);

        $collection = new ResourceCollection([$model]);

        $data = $collection->toJson();

        $this->assertIsString($data);
    }
}
