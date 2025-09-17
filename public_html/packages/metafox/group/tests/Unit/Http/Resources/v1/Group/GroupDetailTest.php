<?php

namespace MetaFox\Group\Tests\Unit\Http\Resources\v1\Group;

use MetaFox\Group\Http\Resources\v1\Group\GroupDetail as Resource;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail Test
|--------------------------------------------------------------------------
|
| @link \MetaFox\Group\Http\Resources\v1\Group\GroupDetail
*/

class GroupDetailTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreate(): array
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $model = Model::factory()->setUser($user)->setPrivacyType(PrivacyTypeHandler::PUBLIC)->create();

        $model->refresh();

        $this->assertNotEmpty($model->entityId());

        return [$model, $user];
    }

    /**
     * @depends testCreate
     *
     * @param array<int, mixed> $data
     */
    public function testResource(array $data)
    {
        [$model, $user] = $data;
        $this->be($user);

        $resource = new Resource($model);

        $resource->toJson();

        // assert ...

        $this->markTestIncomplete('coming soon!');
    }
}
