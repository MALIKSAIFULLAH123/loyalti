<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionCancelReason;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\SubscriptionCancelReasonItem as Resource;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\SubscriptionCancelReasonItemCollection as ResourceCollection;
use MetaFox\Subscription\Models\SubscriptionCancelReason as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_test.stub
*/

/**
 * Class SubscriptionCancelReasonItemTest.
 */
class SubscriptionCancelReasonItemTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function testCreate(): array
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
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
