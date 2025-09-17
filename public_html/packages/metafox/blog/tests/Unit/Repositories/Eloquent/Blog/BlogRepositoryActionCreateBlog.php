<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent\Blog;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Blog\Models\Category;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Blog\Repositories\Eloquent\BlogRepository;
use MetaFox\Core\Models\Attachment;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Friend\Database\Factories\FriendListDataFactory;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Group\Database\Factories\GroupFactory;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Class BlogRepositoryActionCreateBlog.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BlogRepositoryActionCreateBlog extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(BlogRepositoryInterface::class);
        $this->assertInstanceOf(BlogRepository::class, $repository);
        $this->assertTrue(true);
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        return [$user, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array $data
     *
     * @throws AuthorizationException
     */
    public function testCreateBlog(array $data)
    {
        /**
         * @var User                    $user
         * @var BlogRepositoryInterface $repository
         */
        [$user, $repository] = $data;
        $category            = Category::factory()->create();
        $attachment1         = Attachment::factory()->setUser($user)
            ->setData('blog', 'test.jpg', true)->create();

        $params = [
            'title'       => $this->faker->title,
            'categories'  => [$category->id],
            'text'        => $this->faker->text,
            'temp_file'   => $this->createTempFile($user, 'test.jpg', 'blog')->entityId(),
            'draft'       => 0,
            'privacy'     => 0,
            'attachments' => [$attachment1->entityId()],
        ];

        $item = $repository->createBlog($user, $user, $params);

        $checkCountAttachment = 1;
        $this->assertNotEmpty($item->id);
        $this->assertNotEmpty($item->image_path);

        $categoryResult = $item->categories->first();
        $this->assertNotEmpty($categoryResult);
        $this->assertTrue(($categoryResult->id == $category->id));
        $this->assertTrue(($checkCountAttachment == $item->total_attachment));
    }

    /**
     * @depends testInstance
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function testCreateBlogWithOwnerUser(array $data)
    {
        /**
         * @var User                    $user
         * @var BlogRepositoryInterface $repository
         */
        [$user, $repository] = $data;
        $user2               = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $params              = [
            'title'     => $this->faker->title,
            'text'      => $this->faker->text,
            'draft'     => 0,
            'temp_file' => 0,
            'privacy'   => 0,
        ];

        $this->expectException(AuthorizationException::class);
        $repository->createBlog($user, $user2, $params);
    }

    /**
     * @depends testInstance
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function testCreateBlogWithOwnerClosePrivacy(array $data)
    {
        /**
         * @var User                    $user
         * @var BlogRepositoryInterface $repository
         */
        [$user, $repository] = $data;
        $groupOwner          = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $closeGroup          = GroupFactory::new()->setUser($groupOwner)
            ->setPrivacyType(PrivacyTypeHandler::CLOSED)
            ->create();

        $params = [
            'title'     => $this->faker->title,
            'text'      => $this->faker->text,
            'temp_file' => 0,
            'draft'     => 0,
            'privacy'   => 0,
        ];

        $this->expectException(HttpException::class);
        $repository->createBlog($user, $closeGroup, $params);
    }

    /**
     * @depends testInstance
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function testCreateBlogWithOwnerSecretPrivacy(array $data)
    {
        /**
         * @var User                    $user
         * @var BlogRepositoryInterface $repository
         */
        [$user, $repository] = $data;
        $groupOwner          = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $secretGroup         = GroupFactory::new()->setUser($groupOwner)
            ->setPrivacyType(PrivacyTypeHandler::SECRET)
            ->create();

        $params = [
            'title'     => $this->faker->title,
            'text'      => $this->faker->text,
            'temp_file' => 0,
            'draft'     => 0,
            'privacy'   => 0,
        ];

        $this->expectException(AuthorizationException::class);
        $repository->createBlog($user, $secretGroup, $params);
    }

    /**
     * @depends testInstance
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function testCreateBlogWithOwner(array $data)
    {
        /**
         * @var User                    $user
         * @var BlogRepositoryInterface $repository
         */
        [$user, $repository] = $data;
        $publicGroup         = GroupFactory::new()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $params = [
            'title'     => $this->faker->title,
            'text'      => $this->faker->text,
            'temp_file' => 0,
            'draft'     => 0,
            'privacy'   => 0,
        ];
        $item = $repository->createBlog($user, $publicGroup, $params);

        $this->assertNotEmpty($item);
        $this->assertTrue(($item->ownerId() == $publicGroup->entityId()));
    }

    /**
     * @depends testInstance
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function testCreateBlogWithPrivacyCustom(array $data)
    {
        /**
         * @var User                    $user
         * @var BlogRepositoryInterface $repository
         */
        [$user1, $repository] = $data;

        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        FriendFactory::new()->setUser($user1)->setOwner($user2)->create();
        FriendFactory::new()->setUser($user2)->setOwner($user1)->create();

        $list1 = FriendList::factory()->setUser($user1)->create();
        $list2 = FriendList::factory()->setUser($user1)->create();

        FriendListDataFactory::new(['list_id' => $list1->id])->setUser($user2)->create();
        FriendListDataFactory::new(['list_id' => $list2->id])->setUser($user2)->create();

        $params = [
            'title'     => $this->faker->title,
            'text'      => $this->faker->text,
            'draft'     => 0,
            'temp_file' => 0,
            'privacy'   => MetaFoxPrivacy::CUSTOM,
            'list'      => [$list1->id, $list2->id],
        ];

        $item = $repository->createBlog($user1, $user1, $params);

        $this->assertNotEmpty($item);
    }
}
