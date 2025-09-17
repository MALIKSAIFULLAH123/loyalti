<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent\Blog;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Blog\Models\Category;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Blog\Repositories\Eloquent\BlogRepository;
use MetaFox\Core\Models\Attachment;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Friend\Database\Factories\FriendListDataFactory;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

/**
 * Class BlogRepositoryActionUpdateBlog.
 */
class BlogRepositoryActionUpdateBlog extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(BlogRepositoryInterface::class);
        $this->assertInstanceOf(BlogRepository::class, $repository);
        $this->assertTrue(true);
        $user        = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $attachment1 = Attachment::factory()->setUser($user)
            ->setData('blog', 'test.jpg', true)->create();
        $attachment2 = Attachment::factory()->setUser($user)
            ->setData('blog', 'test2.jpg', true)->create();
        $item = Model::factory()->setUser($user)->setOwner($user)->create([
            'privacy'    => MetaFoxPrivacy::EVERYONE,
            'image_path' => null,
        ]);
        resolve(AttachmentRepositoryInterface::class)->updateItemId([
            ['id' => $attachment1->entityId(), 'status' => 'created'],
            ['id' => $attachment2->entityId(), 'status' => 'created'],
        ], $item);
        $this->assertNotEmpty($item);

        return [$user, $item->refresh(), $repository, $attachment1, $attachment2];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     */
    public function testNotPermissionUpdateBlog(array $data)
    {
        /**
         * @var Model                   $item
         * @var BlogRepositoryInterface $repository
         */
        [, $item, $repository, ,] = $data;

        $user     = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $category = Category::factory()->create();

        $title = $this->faker->title;

        $params = [
            'title'        => $title,
            'categories'   => [$category->id],
            'text'         => $this->faker->text,
            'temp_file'    => 0,
            'remove_image' => false,
            'draft'        => 0,
            'privacy'      => 0,
        ];

        $this->expectException(AuthorizationException::class);
        $repository->updateBlog($user, $item->entityId(), $params);
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testUpdateBlog(array $data)
    {
        /**
         * @var User                    $user
         * @var Model                   $item
         * @var BlogRepositoryInterface $repository
         * @var Attachment              $attachment1
         */
        [$user, $item, $repository, $attachment1,] = $data;
        $category                                  = Category::factory()->create();
        $title                                     = $this->faker->title;

        $params = [
            'title'        => $title,
            'categories'   => [$category->id],
            'text'         => $this->faker->text,
            'temp_file'    => $this->createTempFile($user, 'test.jpg', 'blog', true)->entityId(),
            'remove_image' => true,
            'draft'        => 0,
            'privacy'      => 0,
            'attachments'  => [$attachment1->entityId()],
        ];

        $blog = $repository->updateBlog($user, $item->entityId(), $params);

        $this->assertTrue(($blog->title == $title));
        $this->assertNotEmpty($blog->image_path);

        $categoryResult = $blog->categories->first();
        $this->assertNotEmpty($categoryResult);
        $this->assertTrue(($categoryResult->id == $category->id));
        $checkCountAttachment = 1;
        $this->assertTrue(($checkCountAttachment == $item->refresh()->total_attachment));
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testUpdateBlogWithPrivacyCustom(array $data)
    {
        /**
         * @var User                    $user
         * @var Model                   $item
         * @var BlogRepositoryInterface $repository
         */
        [$user, $item, $repository, ,] = $data;

        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        FriendFactory::new()->setUser($user1)->setOwner($user)->create();
        FriendFactory::new()->setUser($user)->setOwner($user1)->create();

        $list1 = FriendList::factory()->setUser($user)->create();
        $list2 = FriendList::factory()->setUser($user)->create();

        FriendListDataFactory::new(['list_id' => $list1->id])->setUser($user1)->create();
        FriendListDataFactory::new(['list_id' => $list2->id])->setUser($user1)->create();

        $params = [
            'title'        => $this->faker->title,
            'text'         => $this->faker->text,
            'draft'        => 0,
            'temp_file'    => 0,
            'remove_image' => false,
            'privacy'      => MetaFoxPrivacy::CUSTOM,
            'list'         => [$list1->id, $list2->id],
        ];

        $blog = $repository->updateBlog($user, $item->entityId(), $params);

        $this->assertNotEmpty($blog);
        $this->assertTrue((MetaFoxPrivacy::CUSTOM == $blog->privacy));
    }
}
