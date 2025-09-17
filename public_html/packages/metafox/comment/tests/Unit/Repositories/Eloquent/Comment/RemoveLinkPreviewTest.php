<?php

namespace MetaFox\Comment\Tests\Unit\Repositories\Eloquent\Comment;

use Illuminate\Database\Eloquent\Relations\Relation;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentAttachment;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Repositories\Eloquent\CommentRepository;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use Tests\TestCase;

class RemoveLinkPreviewTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(CommentRepositoryInterface::class);

        $this->assertInstanceOf(CommentRepository::class, $repository);

        return $repository;
    }

    public function testContext()
    {
        $context = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertInstanceOf(User::class, $context);

        return $context;
    }

    public function testUser()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertInstanceOf(User::class, $user);

        return $user;
    }

    /**
     * @depends testUser
     * @return Content
     */
    public function testContent(User $user)
    {
        $content = $this->contentFactory()
            ->setUser($user)
            ->setOwner($user)
            ->create();

        $this->assertInstanceOf(Content::class, $content);

        return $content;
    }

    /**
     * @depends testUser
     * @depends testContent
     * @depends testInstance
     * @param User    $user
     * @param Content $content
     * @params  CommentRepositoryInterface $repository
     * @return void
     */
    public function testRemoveLinkPreviewSuccess(User $user, Content $content, CommentRepositoryInterface $repository): void
    {
        $this->actingAs($user);

        Relation::morphMap([
            $content->entityType() => get_class($content),
        ]);

        $comment = $repository->createComment($user, [
            'item_id'   => $content->entityId(),
            'item_type' => $content->entityType(),
            'parent_id' => 0,
            'text'      => $this->faker->url,
        ]);

        $this->assertInstanceOf(Comment::class, $comment);

        $this->assertInstanceOf(CommentAttachment::class, $comment->commentAttachment);

        $this->assertSame(CommentAttachment::TYPE_LINK, $comment->commentAttachment->item_type);

        $result = $repository->removeLinkPreview($comment);

        $this->assertSame(true, $result);
    }
}
