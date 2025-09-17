<?php

namespace MetaFox\Page\Tests\Unit\Repositories\PrivacyPolicy\CreateResourceOnOwner\Page;

use MetaFox\Activity\Database\Factories\PostFactory;
use MetaFox\Blog\Database\Factories\BlogFactory;
use MetaFox\Core\Repositories\PrivacyPolicyRepository;
use MetaFox\Page\Database\Factories\PageFactory;
use MetaFox\Page\Database\Factories\PageMemberFactory;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageMember;
use MetaFox\Photo\Database\Factories\PhotoFactory;
use MetaFox\Platform\Contracts\PrivacyPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Database\Factories\VideoFactory;
use Tests\TestCase;

class CreateResourceOnPageTest extends TestCase
{
    /**
     * @return PrivacyPolicy
     */
    public function testCreateInstance()
    {
        $repository = resolve(PrivacyPolicy::class);
        $this->assertInstanceOf(PrivacyPolicyRepository::class, $repository);

        return $repository;
    }

    /**
     * @param PrivacyPolicy $repository
     * @depends testCreateInstance
     * @return array<int, mixed>
     */
    public function testCreateResource($repository)
    {
        $user     = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $stranger = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $pageMember = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $pageAdmin  = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);

        $page = PageFactory::new()->setUser($user)->create();
        // Set creator.
        PageMemberFactory::new()->setOwner($page)->setUser($user)->setAdmin()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertInstanceOf(User::class, $stranger);
        $this->assertInstanceOf(User::class, $pageMember);
        $this->assertInstanceOf(User::class, $pageAdmin);

        PageMemberFactory::new()->setOwner($page)->setUser($pageMember)->create();

        $this->assertTrue(PageMember::query()->where('page_id', $page->entityId())->where(
            'user_id',
            $pageMember->entityId()
        )->where('member_type', PageMember::MEMBER)->exists());

        PageMemberFactory::new()->setOwner($page)->setUser($pageAdmin)->setAdmin()->create();
        $this->assertTrue(PageMember::query()->where('page_id', $page->entityId())->where(
            'user_id',
            $pageAdmin->entityId()
        )->where('member_type', PageMember::ADMIN)->exists());

        return [$repository, $user, $page, $stranger, $pageMember, $pageAdmin];
    }

    /**
     * @depends testCreateResource
     * @param array<mixed> $params
     */
    public function testCreateResourceSuccess($params)
    {
        /**
         * @var PrivacyPolicy $repository
         * @var User          $user
         * @var User          $page
         * @var User          $stranger
         * @var User          $pageMember
         * @var User          $pageAdmin
         */
        [$repository, $user, $page, $stranger, $pageMember, $pageAdmin] = $params;

        // Owner can post on page.
        $this->assertTrue($repository->checkCreateOnOwner($user, $page));

        // Page can post on itself.
        $this->assertTrue($repository->checkCreateOnOwner($page, $page));

        // Stranger can post on page.
        $this->assertTrue($repository->checkCreateOnOwner($stranger, $page));

        // Page member can post on page.
        $this->assertTrue($repository->checkCreateOnOwner($pageMember, $page));

        // Page admin can post on page.
        $this->assertTrue($repository->checkCreateOnOwner($pageAdmin, $page));
    }

    /**
     * @depends testCreateResource
     * @param array<mixed> $params
     */
    public function testCreateResourceWithContentByPageCreatorSuccess($params)
    {
        /**
         * @var PrivacyPolicy $repository
         * @var User          $user
         * @var User          $page
         */
        [$repository, $user, $page] = $params;

        $blog  = BlogFactory::new()->setUser($user)->setOwner($page)->make();
        $photo = PhotoFactory::new()->setUser($user)->setOwner($page)->make();
        $post  = PostFactory::new()->setUser($user)->setOwner($page)->make();
        $video = VideoFactory::new()->setUser($user)->setOwner($page)->make();

        // User can post on his wall.
        $this->assertTrue($repository->checkCreateResourceOnOwner($blog));
        $this->assertTrue($repository->checkCreateResourceOnOwner($photo));
        $this->assertTrue($repository->checkCreateResourceOnOwner($post));
        $this->assertTrue($repository->checkCreateResourceOnOwner($video));

        // Try to save the model itself.
        $this->assertNotEmpty($blog->save());
        $this->assertNotEmpty($photo->save());
        $this->assertNotEmpty($post->save());
        $this->assertNotEmpty($video->save());
    }

    /**
     * @depends testCreateResource
     * @param array<mixed> $params
     */
    public function testCreateResourceWithContentByPageAdminSuccess($params)
    {
        /**
         * @var PrivacyPolicy $repository
         * @var User          $user
         * @var User          $page
         * @var User          $stranger
         * @var User          $pageMember
         * @var User          $pageAdmin
         */
        [$repository, $user, $page, $stranger, $pageMember, $pageAdmin] = $params;

        $blog  = BlogFactory::new()->setUser($pageAdmin)->setOwner($page)->make();
        $photo = PhotoFactory::new()->setUser($pageAdmin)->setOwner($page)->make();
        $post  = PostFactory::new()->setUser($pageAdmin)->setOwner($page)->make();
        $video = VideoFactory::new()->setUser($pageAdmin)->setOwner($page)->make();

        // Page admins can post any resources he wants.
        $this->assertTrue($repository->checkCreateResourceOnOwner($blog));
        $this->assertTrue($repository->checkCreateResourceOnOwner($photo));
        $this->assertTrue($repository->checkCreateResourceOnOwner($post));
        $this->assertTrue($repository->checkCreateResourceOnOwner($video));

        // Try to save the model itself.
        $this->assertNotEmpty($blog->save());
        $this->assertNotEmpty($photo->save());
        $this->assertNotEmpty($post->save());
        $this->assertNotEmpty($video->save());
    }

    /**
     * @depends testCreateResource
     * @param array<mixed> $params
     */
    public function testCreateResourceWithContentByPageMemberSuccess($params)
    {
        /**
         * @var PrivacyPolicy $repository
         * @var User          $user
         * @var User          $page
         * @var User          $stranger
         * @var User          $pageMember
         * @var User          $pageAdmin
         */
        [$repository, $user, $page, $stranger, $pageMember, $pageAdmin] = $params;

        $blog  = BlogFactory::new()->setUser($pageMember)->setOwner($page)->make();
        $photo = PhotoFactory::new()->setUser($pageMember)->setOwner($page)->make();
        $post  = PostFactory::new()->setUser($pageMember)->setOwner($page)->make();
        $video = VideoFactory::new()->setUser($pageMember)->setOwner($page)->make();

        // Page member can post blog, photo, post, video.
        $this->assertTrue($repository->checkCreateResourceOnOwner($blog));
        $this->assertTrue($repository->checkCreateResourceOnOwner($photo));
        $this->assertTrue($repository->checkCreateResourceOnOwner($post));
        $this->assertTrue($repository->checkCreateResourceOnOwner($video));

        // Try to save the model itself.
        $this->assertNotEmpty($blog->save());
        $this->assertNotEmpty($photo->save());
        $this->assertNotEmpty($post->save());
        $this->assertNotEmpty($video->save());
    }

    /**
     * @depends testCreateResource
     * @param array<mixed> $params
     */
    public function testCreateResourceWithContentByStrangerSuccess($params)
    {
        /**
         * @var PrivacyPolicy $repository
         * @var User          $user
         * @var User          $page
         * @var User          $stranger
         * @var User          $pageMember
         * @var User          $pageAdmin
         */
        [$repository, $user, $page, $stranger, $pageMember, $pageAdmin] = $params;

        $blog  = BlogFactory::new()->setUser($stranger)->setOwner($page)->make();
        $photo = PhotoFactory::new()->setUser($stranger)->setOwner($page)->make();
        $post  = PostFactory::new()->setUser($stranger)->setOwner($page)->make();
        $video = VideoFactory::new()->setUser($stranger)->setOwner($page)->make();

        // Stranger can only post blog, photo, post, video.
        $this->assertTrue($repository->checkCreateResourceOnOwner($blog));
        $this->assertTrue($repository->checkCreateResourceOnOwner($photo));
        $this->assertTrue($repository->checkCreateResourceOnOwner($post));
        $this->assertTrue($repository->checkCreateResourceOnOwner($video));

        // Try to save the model itself.
        $this->assertNotEmpty($blog->save());
        $this->assertNotEmpty($photo->save());
        $this->assertNotEmpty($post->save());
        $this->assertNotEmpty($video->save());
    }
}
