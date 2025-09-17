<?php

namespace MetaFox\Comment\Tests\Unit\Http\Resources\v1\Comment;

use MetaFox\Comment\Http\Resources\v1\Comment\CommentDetail as Resource;
use MetaFox\Comment\Models\Comment as Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Blog\Models\Blog as ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class CommentDetailTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreate(): array
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item  = ContentModel::factory()->setUser($user)->setOwner($user)->create();
        $model = Model::factory()->setUser($user)->setItem($item)->create();

        $model->refresh();

        $this->assertNotEmpty($model->id);

        return [$user, $model];
    }

    /**
     * @depends testCreate
     *
     * @param array<int, mixed> $data
     */
    public function testResource(array $data)
    {
        /**
         * @var User  $user
         * @var Model $model
         */
        [$user, $model] = $data;
        $this->be($user);

        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);
    }
}
