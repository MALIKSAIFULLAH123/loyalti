<?php

namespace MetaFox\Comment\Tests\Unit\Repositories\Eloquent\Comment;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\Blog\Models\Blog as ContentModel;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentHide;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Repositories\CommentHiddenRepositoryInterface;
use MetaFox\Comment\Repositories\Eloquent\CommentRepository;
use MetaFox\Platform\Contracts\User;
use Tests\TestCase;

class HideCommentItemTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(CommentHiddenRepositoryInterface::class);
        $this->assertInstanceOf(CommentRepository::class, $repository);

        $user  = $this->createNormalUser();
        $user2 = $this->createNormalUser();

        return [$repository, $user, $user2];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testHideSuccess(array $data)
    {
        /**
         * @var CommentHiddenRepositoryInterface $repository
         * @var User                             $user
         * @var User                             $owner
         */
        [$repository, $user, $owner] = $data;

        $this->be($user);

        $item = ContentModel::factory()
            ->setUser($owner)
            ->setOwner($owner)
            ->create();

        $comment = Comment::factory()
            ->setUser($owner)
            ->setOwner($owner)
            ->setItem($item)->create();

        // pass policy check.
        $this->mock(CommentPolicy::class)
            ->shouldReceive('hide')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $result = $repository->hideComment($owner, $comment->entityId(), false);
        $this->assertTrue($result);

        // pass policy check.
        $this->mock(CommentPolicy::class)
            ->shouldReceive('hide')
            ->once()
            ->withAnyArgs()
            ->andReturn(false);

        $this->expectException(AuthorizationException::class);

        $result = $repository->hideComment($owner, $comment->entityId(), false);
        $this->assertFalse($result);
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testUnHideNotFoundSuccess(array $data)
    {
        /**
         * @var CommentHiddenRepositoryInterface $repository
         * @var User                             $user
         * @var User                             $user2
         */
        [$repository, , $user2] = $data;

        $this->actingAs($user2);
        $this->expectException(ModelNotFoundException::class);
        $repository->hideComment($user2, 0, true);
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testUnHideSuccess(array $data)
    {
        /**
         * @var CommentHiddenRepositoryInterface $repository
         * @var User                             $user
         * @var User                             $user2
         */
        [$repository, $user, $user2] = $data;

        $this->skipPolicies(CommentPolicy::class);

        $item = $this->contentFactory()->setUser($user2)->setOwner($user2)->create();
        $this->actingAs($user);

        $comment = Comment::factory()->setUser($user)->setItem($item)->create();

        CommentHide::factory()->setUser($user2)->setItem($comment)->create();

        $result = $repository->hideComment($user2, $comment->entityId(), true);

        $this->assertTrue($result);
    }
}
