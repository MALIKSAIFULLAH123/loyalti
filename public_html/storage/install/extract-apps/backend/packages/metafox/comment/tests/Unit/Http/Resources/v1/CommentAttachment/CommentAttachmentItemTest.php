<?php

namespace MetaFox\Comment\Tests\Unit\Http\Resources\v1\CommentAttachment;

use MetaFox\Comment\Http\Resources\v1\CommentAttachment\CommentAttachmentItem as Resource;
use MetaFox\Comment\Http\Resources\v1\CommentAttachment\CommentAttachmentItemCollection as ResourceCollection;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentAttachment as Model;
use MetaFox\Blog\Models\Blog as ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class CommentAttachmentItemTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $user    = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item    = ContentModel::factory()->setUser($user)->setOwner($user)->create();
        $comment = Comment::factory()->setUser($user)->setItem($item)->create();

        $model = Model::factory()->setCommentId($comment->entityId())->create();

        $model->refresh();

        $this->assertNotEmpty($model->entityId());

        return $model;
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testResource(Model $model)
    {
        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testCollection(Model $model)
    {
        $collection = new ResourceCollection([$model]);

        $data = $collection->toJson();

        $this->assertIsString($data);
    }
}
