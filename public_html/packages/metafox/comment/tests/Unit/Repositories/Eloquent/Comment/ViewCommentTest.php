<?php

namespace MetaFox\Comment\Tests\Unit\Repositories\Eloquent\Comment;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Repositories\Eloquent\CommentRepository;
use MetaFox\Blog\Models\Blog as ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewCommentTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(CommentRepositoryInterface::class);
        $this->assertInstanceOf(CommentRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testSuccess(CommentRepositoryInterface $repository)
    {
        $this->skipPolicies(CommentPolicy::class);

        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user2);
        $item    = ContentModel::factory()->setUser($user2)->setOwner($user2)->create();
        $this->actingAs($user);
        $comment = Comment::factory()->setUser($user)->setItem($item)->create();

        $result = $repository->viewComment($user, $comment->entityId());

        $this->assertNotEmpty($result);
    }
}
