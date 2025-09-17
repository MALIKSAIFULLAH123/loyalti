<?php

namespace MetaFox\Group\Tests\Feature\ViewResource;

use MetaFox\Blog\Database\Factories\BlogFactory;
use MetaFox\Blog\Models\Blog;
use MetaFox\Core\Repositories\PrivacyPolicyRepository;
use MetaFox\Group\Database\Factories\GroupFactory;
use MetaFox\Group\Database\Factories\MemberFactory;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Photo\Database\Factories\PhotoFactory;
use MetaFox\Photo\Models\Photo;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\PrivacyPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\User\Repositories\Eloquent\UserPrivacyRepository;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use Tests\TestCase;

class GroupClosedMemberCanViewResourceTest extends TestCase
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
     * @return array<mixed>
     */
    public function testCreateResource(PrivacyPolicy $repository)
    {
        $userPrivacyService = resolve(UserPrivacyRepositoryInterface::class);
        $this->assertInstanceOf(UserPrivacyRepository::class, $userPrivacyService);

        $owner    = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $stranger = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $groupMember = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $groupAdmin  = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = GroupFactory::new()
            ->setUser($owner)
            ->setPrivacyType(PrivacyTypeHandler::CLOSED)
            ->create();

        MemberFactory::new()->setOwner($group)->setUser($groupMember)->create();
        MemberFactory::new()->setOwner($group)->setUser($groupAdmin)->setAdmin()->create();

        $this->assertInstanceOf(User::class, $owner);
        $this->assertInstanceOf(Group::class, $group);
        $this->assertInstanceOf(User::class, $stranger);
        $this->assertInstanceOf(User::class, $groupMember);
        $this->assertInstanceOf(User::class, $groupAdmin);

        $photo = PhotoFactory::new()->setUser($owner)->setOwner($group)->create();
        $blog  = BlogFactory::new()->setUser($owner)->setOwner($group)->create();

        $this->assertInstanceOf(Content::class, $photo);
        $this->assertInstanceOf(Content::class, $blog);

        return [$repository, $userPrivacyService, $owner, $group, $stranger, $groupMember, $groupAdmin, $photo, $blog];
    }

    /**
     * @depends testCreateResource
     * @param  array<mixed> $params
     * @return array<mixed>
     */
    public function testCanViewDefaultSuccess($params)
    {
        /**
         * @var PrivacyPolicy                  $repository
         * @var UserPrivacyRepositoryInterface $userPrivacyService
         * @var User                           $owner
         * @var User                           $group
         * @var User                           $stranger
         * @var User                           $groupMember
         * @var User                           $groupAdmin
         * @var Content                        $photo
         * @var Content                        $blog
         */
        [
            $repository, $userPrivacyService, $owner, $group, $stranger, $groupMember, $groupAdmin, $photo, $blog,
        ] = $params;

        // Owner can view.
        $this->assertTrue($owner->can('viewAny', [Blog::class, $group]));
        $this->assertTrue($owner->can('viewAny', [Photo::class, $group]));
        $this->assertTrue($owner->can('view', [Blog::class, $blog]));
        $this->assertTrue($owner->can('view', [Photo::class, $photo]));
        $this->assertTrue($owner->can('comment', [Blog::class, $blog]));
        $this->assertTrue($owner->can('comment', [Photo::class, $photo]));
        $this->assertTrue($owner->can('like', [Blog::class, $blog]));
        $this->assertTrue($owner->can('like', [Photo::class, $photo]));
        $this->assertFalse($owner->can('share', [Blog::class, $blog]));
        $this->assertFalse($owner->can('share', [Photo::class, $photo]));

        // Group admin can view.
        $this->assertTrue($groupAdmin->can('viewAny', [Blog::class, $group]));
        $this->assertTrue($groupAdmin->can('viewAny', [Photo::class, $group]));
        $this->assertTrue($groupAdmin->can('view', [Blog::class, $blog]));
        $this->assertTrue($groupAdmin->can('view', [Photo::class, $photo]));
        $this->assertTrue($groupAdmin->can('comment', [Blog::class, $blog]));
        $this->assertTrue($groupAdmin->can('comment', [Photo::class, $photo]));
        $this->assertTrue($groupAdmin->can('like', [Blog::class, $blog]));
        $this->assertTrue($groupAdmin->can('like', [Photo::class, $photo]));
        $this->assertFalse($groupAdmin->can('share', [Blog::class, $blog]));
        $this->assertFalse($groupAdmin->can('share', [Photo::class, $photo]));

        // Group member can view.
        $this->assertTrue($groupMember->can('viewAny', [Blog::class, $group]));
        $this->assertTrue($groupMember->can('viewAny', [Photo::class, $group]));
        $this->assertTrue($groupMember->can('view', [Blog::class, $blog]));
        $this->assertTrue($groupMember->can('view', [Photo::class, $photo]));
        $this->assertTrue($groupMember->can('comment', [Blog::class, $blog]));
        $this->assertTrue($groupMember->can('comment', [Photo::class, $photo]));
        $this->assertTrue($groupMember->can('like', [Blog::class, $blog]));
        $this->assertTrue($groupMember->can('like', [Photo::class, $photo]));
        $this->assertFalse($groupMember->can('share', [Blog::class, $blog]));
        $this->assertFalse($groupMember->can('share', [Photo::class, $photo]));

        // Stranger can view group but cant view post.
        $this->assertTrue($stranger->can('viewAny', [Blog::class, $group]));
        $this->assertTrue($stranger->can('viewAny', [Photo::class, $group]));
        $this->assertFalse($stranger->can('view', [Blog::class, $blog]));
        $this->assertFalse($stranger->can('view', [Photo::class, $photo]));
        $this->assertFalse($stranger->can('comment', [Blog::class, $blog]));
        $this->assertFalse($stranger->can('comment', [Photo::class, $photo]));
        $this->assertFalse($stranger->can('like', [Blog::class, $blog]));
        $this->assertFalse($stranger->can('like', [Photo::class, $photo]));
        $this->assertFalse($stranger->can('share', [Blog::class, $blog]));
        $this->assertFalse($stranger->can('share', [Photo::class, $photo]));

        return [
            $repository, $userPrivacyService, $owner, $group, $stranger, $groupMember, $groupAdmin, $photo, $blog,
        ];
    }

    /**
     * @depends testCanViewDefaultSuccess
     * @param  array<mixed> $params
     * @return array<mixed>
     */
    public function testCanViewByMemberSuccess($params)
    {
        /**
         * @var PrivacyPolicy                  $repository
         * @var UserPrivacyRepositoryInterface $userPrivacyService
         * @var User                           $owner
         * @var User                           $group
         * @var User                           $stranger
         * @var User                           $groupMember
         * @var User                           $groupAdmin
         * @var Content                        $photo
         * @var Content                        $blog
         */
        [
            $repository, $userPrivacyService, $owner, $group, $stranger, $groupMember, $groupAdmin, $photo, $blog,
        ] = $params;

        $userPrivacyService->updateUserPrivacy($group->entityId(), [
            'blog.view_browse_blogs'   => Group::MEMBER_PRIVACY,
            'photo.view_browse_photos' => Group::MEMBER_PRIVACY,
        ]);

        // Owner can view.
        $this->assertTrue($owner->can('viewAny', [Blog::class, $group]));
        $this->assertTrue($owner->can('viewAny', [Photo::class, $group]));
        $this->assertTrue($owner->can('view', [Blog::class, $blog]));
        $this->assertTrue($owner->can('view', [Photo::class, $photo]));

        // Group admin can view.
        $this->assertTrue($groupAdmin->can('viewAny', [Blog::class, $group]));
        $this->assertTrue($groupAdmin->can('viewAny', [Photo::class, $group]));
        $this->assertTrue($groupAdmin->can('view', [Blog::class, $blog]));
        $this->assertTrue($groupAdmin->can('view', [Photo::class, $photo]));

        // Group member can view.
        $this->assertTrue($groupMember->can('viewAny', [Blog::class, $group]));
        $this->assertTrue($groupMember->can('viewAny', [Photo::class, $group]));
        $this->assertTrue($groupMember->can('view', [Blog::class, $blog]));
        $this->assertTrue($groupMember->can('view', [Photo::class, $photo]));

        // Stranger cannot view.
        $this->assertFalse($stranger->can('viewAny', [Blog::class, $group]));
        $this->assertFalse($stranger->can('viewAny', [Photo::class, $group]));
        $this->assertFalse($stranger->can('view', [Blog::class, $blog]));
        $this->assertFalse($stranger->can('view', [Photo::class, $photo]));

        return [
            $repository, $userPrivacyService, $owner, $group, $stranger, $groupMember, $groupAdmin, $photo, $blog,
        ];
    }

    /**
     * @depends testCanViewByMemberSuccess
     * @param array<mixed> $params
     */
    public function testCanViewOnlyByAdminSuccess($params)
    {
        /**
         * @var PrivacyPolicy                  $repository
         * @var UserPrivacyRepositoryInterface $userPrivacyService
         * @var User                           $owner
         * @var User                           $group
         * @var User                           $stranger
         * @var User                           $groupMember
         * @var User                           $groupAdmin
         * @var Content                        $photo
         * @var Content                        $blog
         */
        [
            $repository, $userPrivacyService, $owner, $group, $stranger, $groupMember, $groupAdmin, $photo, $blog,
        ] = $params;

        $userPrivacyService->updateUserPrivacy($group->entityId(), [
            'blog.view_browse_blogs'   => Group::ADMIN_PRIVACY,
            'photo.view_browse_photos' => Group::ADMIN_PRIVACY,
        ]);

        // Owner can view.
        $this->assertTrue($owner->can('viewAny', [Blog::class, $group]));
        $this->assertTrue($owner->can('viewAny', [Photo::class, $group]));
        $this->assertTrue($owner->can('view', [Blog::class, $blog]));
        $this->assertTrue($owner->can('view', [Photo::class, $photo]));

        // Group admin can view.
        $this->assertTrue($groupAdmin->can('viewAny', [Blog::class, $group]));
        $this->assertTrue($groupAdmin->can('viewAny', [Photo::class, $group]));
        $this->assertTrue($groupAdmin->can('view', [Blog::class, $blog]));
        $this->assertTrue($groupAdmin->can('view', [Photo::class, $photo]));

        // Group member cannot view.
        $this->assertFalse($groupMember->can('viewAny', [Blog::class, $group]));
        $this->assertFalse($groupMember->can('viewAny', [Photo::class, $group]));
        $this->assertFalse($groupMember->can('view', [Blog::class, $blog]));
        $this->assertFalse($groupMember->can('view', [Photo::class, $photo]));

        // Stranger cannot view.
        $this->assertFalse($stranger->can('viewAny', [Blog::class, $group]));
        $this->assertFalse($stranger->can('viewAny', [Photo::class, $group]));
        $this->assertFalse($stranger->can('view', [Blog::class, $blog]));
        $this->assertFalse($stranger->can('view', [Photo::class, $photo]));
    }
}
