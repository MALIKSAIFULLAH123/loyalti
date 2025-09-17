<?php

namespace MetaFox\Comment\Tests\Unit\Repositories\Eloquent\Comment;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Repositories\Eloquent\CommentRepository;
use MetaFox\Platform\Contracts\User;
use MetaFox\Blog\Models\Blog as ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class UpdateCommentTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(CommentRepositoryInterface::class);
        $this->assertInstanceOf(CommentRepository::class, $repository);
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user2);
        $item    = ContentModel::factory()->setUser($user2)->setOwner($user2)->create();
        $this->actingAs($user);
        $comment = Comment::factory()->setUser($user)->setItem($item)->create();

        return [$repository, $user, $comment];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @return array<int, mixed>
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testSuccess(array $data): array
    {
        /**
         * @var CommentRepositoryInterface $repository
         * @var User                       $user
         * @var Comment                    $comment
         */
        [$repository, $user, $comment] = $data;
        $this->actingAs($user);
        $text                          = $this->faker->text();
        $params                        = [
            'text' => $text,
        ];

        $this->skipPolicies(CommentPolicy::class);

        $repository->updateComment($user, $comment->entityId(), $params);
        $comment->refresh();
        $this->assertSame($text, $comment->text);

        return [$repository, $user, $comment];
    }

    /**
     * @depends testSuccess
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testUpdateCommentHiddenTruthy(array $data): array
    {
        /**
         * @var CommentRepositoryInterface $repository
         * @var User                       $user
         * @var Comment                    $comment
         */
        [$repository, $user, $comment] = $data;
        $this->actingAs($user);

        $params = [
            'is_hide' => 1,
        ];

        $this->skipPolicies(CommentPolicy::class);

        $repository->updateComment($user, $comment->entityId(), $params);
        $comment->refresh();

        $this->assertNotNull($comment->is_hidden);

        return $data;
    }

    /**
     * @depends testUpdateCommentHiddenTruthy
     *
     * @param array<int, mixed> $data
     *
     * @return array<int,             mixed>
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testWithPhoto(array $data): array
    {
        /**
         * @var CommentRepositoryInterface $repository
         * @var User                       $user
         * @var Comment                    $comment
         */
        [$repository, $user, $comment] = $data;

        $this->actingAs($user);
        $tempFile = $this->createTempFile($user, 'test.jpg', 'test');
        $params   = [
            'photo_id' => $tempFile->id,
        ];
        $this->skipPolicies(CommentPolicy::class);

        $repository->updateComment($user, $comment->entityId(), $params);
        $comment->refresh();

        $this->assertNotNull($comment->commentAttachment);

        return $data;
    }

    /**
     * @depends testWithPhoto
     *
     * @param array<int, mixed> $data
     *
     * @return array<int,             mixed>
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testWithPhotoAttachmentExist(array $data): array
    {
        /**
         * @var CommentRepositoryInterface $repository
         * @var User                       $user
         * @var Comment                    $comment
         */
        [$repository, $user, $comment] = $data;

        $tempFile = $this->createTempFile($user, 'test2.jpg', 'test');
        $params   = [
            'photo_id' => $tempFile->id,
        ];

        $this->skipPolicies(CommentPolicy::class);

        $repository->updateComment($user, $comment->entityId(), $params);
        $comment->refresh();

        $this->assertNotNull($comment->commentAttachment);

        return $data;
    }

    /**
     * @depends testWithPhotoAttachmentExist
     *
     * @param array<int, mixed> $data
     *
     * @return array<int,             mixed>
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testWithDeletePhotoAttachment(array $data): array
    {
        /**
         * @var CommentRepositoryInterface $repository
         * @var User                       $user
         * @var Comment                    $comment
         */
        [$repository, $user, $comment] = $data;

        $params = [
            'photo_id' => 0,
        ];

        $this->skipPolicies(CommentPolicy::class);

        $repository->updateComment($user, $comment->entityId(), $params);
        $comment->refresh();

        $this->assertNull($comment->commentAttachment);

        return $data;
    }
}
