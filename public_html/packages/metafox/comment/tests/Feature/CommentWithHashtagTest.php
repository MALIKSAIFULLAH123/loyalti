<?php

namespace MetaFox\Comment\Tests\Feature;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Repositories\Eloquent\CommentRepository;
use MetaFox\Friend\Models\Friend;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use Tests\TestCase;

class CommentWithHashtagTest extends TestCase
{
    use TestFactoryContent;

    /**
     * @depends testCreateContent
     * @throws AuthorizationException
     */
    public function testCommentItemWithHashtag(Model $item)
    {
        $user = $item->user;
        $this->createNormalUser();

        /** @var CommentRepository $service */
        $service = app(CommentRepositoryInterface::class);

        $expectedHashtag = '#' . now()->timestamp;

        $data = Comment::factory()
            ->setItem($item)
            ->makeOne([
                'text'     => $expectedHashtag,
                'photo_id' => 0,
            ])->toArray();

        $comment = $service->createComment($user, $data);

        $this->assertNotEmpty($comment->entityId());

        $comment->loadMissing(['tagData']);
        $this->assertNotEmpty($comment->tagData);

        return $comment;
    }

    /**
     * @depends testCommentItemWithHashtag
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testUpdateCommentItemWithNoHashtag(Comment $comment)
    {
        /** @var CommentRepository $service */
        $service = app(CommentRepositoryInterface::class);
        $user    = $comment->user;
        $this->be($user);

        $data = [
            'text' => 'phpunit comment without hashtag',
        ];

        $comment = $service->updateComment($user, $comment->entityId(), $data);

        $comment->refresh();

        $this->assertEmpty($comment->tagData);
    }

    /**
     * @depends testCreateContent
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function testMentionWithPoster(Content $item)
    {
        /** @var User $context */
        $context = $item->user;

        $this->be($item->user);

        $friend = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertInstanceOf(User::class, $friend);

        Friend::factory()
            ->setUser($context)
            ->setOwner($friend)
            ->create();

        Friend::factory()
            ->setUser($friend)
            ->setOwner($context)
            ->create();

        $data = [
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
            'text'      => $this->faker->text . " [user={$context->entityId()}]{$context->full_name}[/user]",
            'photo_id'  => 0,
        ];

        $repository = app(CommentRepositoryInterface::class);

        $result = $repository->createComment($context, $data);

        $this->assertInstanceOf(Comment::class, $result);
    }
}
