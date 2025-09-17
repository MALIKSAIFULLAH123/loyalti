<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionCancelReason;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\SubscriptionCancelReasonDetail as Resource;
use MetaFox\Subscription\Models\SubscriptionCancelReason as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail Test
|--------------------------------------------------------------------------
| @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\SubscriptionCancelReasonDetail
| stub: /packages/resources/detail_test.stub
*/

class SubscriptionCancelReasonDetailTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function testCreate(): array
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);
        $model = Model::factory()->setOwner($user)->create();

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
}
