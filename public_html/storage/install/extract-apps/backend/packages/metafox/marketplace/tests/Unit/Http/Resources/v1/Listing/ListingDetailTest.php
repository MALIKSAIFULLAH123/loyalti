<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Resources\v1\Listing;

use MetaFox\Marketplace\Http\Resources\v1\Listing\ListingDetail as Resource;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ListingDetailTest extends TestCase
{
    /**
     * @return array
     */
    public function testCreate(): array
    {
        $model = Model::factory()->create();

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $model->refresh();

        $this->assertNotEmpty($model->id);
        $this->assertInstanceOf(User::class, $user);

        return [$model, $user];
    }

    /**
     * @depends testCreate
     *
     * @param array $params
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

        $resource->toJson();

        // assert ...

        $this->assertTrue(true);
    }
}
