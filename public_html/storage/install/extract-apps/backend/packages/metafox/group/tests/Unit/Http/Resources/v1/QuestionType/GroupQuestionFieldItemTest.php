<?php

namespace MetaFox\Group\Tests\Unit\Http\Resources\v1\QuestionType;

use MetaFox\Group\Http\Resources\v1\QuestionField\QuestionFieldItem as Resource;
use MetaFox\Group\Http\Resources\v1\QuestionField\QuestionFieldItemCollection as ResourceCollection;
use MetaFox\Group\Models\QuestionField as Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GroupQuestionFieldItemTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function testCreate(): array
    {
        $this->markTestIncomplete();
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
