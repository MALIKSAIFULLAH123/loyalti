<?php

namespace MetaFox\Group\Tests\Unit\Repositories\PrivacyPolicy\CreateResourceOnOwner\Group;

use MetaFox\Activity\Database\Factories\PostFactory;
use MetaFox\Blog\Database\Factories\BlogFactory;
use MetaFox\Core\Repositories\PrivacyPolicyRepository;
use MetaFox\Group\Database\Factories\GroupFactory;
use MetaFox\Group\Database\Factories\MemberFactory;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Photo\Database\Factories\PhotoFactory;
use MetaFox\Platform\Contracts\PrivacyPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Database\Factories\VideoFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CreateResourceOnSecretGroupTest extends TestCase
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

        $groupMember = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $groupAdmin  = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = GroupFactory::new()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::SECRET)
            ->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Group::class, $group);
        $this->assertInstanceOf(User::class, $stranger);
        $this->assertInstanceOf(User::class, $groupMember);
        $this->assertInstanceOf(User::class, $groupAdmin);

        MemberFactory::new()->setOwner($group)->setUser($groupMember)->create();
        $this->assertTrue(Member::query()->where('group_id', $group->entityId())->where(
            'user_id',
            $groupMember->entityId()
        )->where('member_type', Member::MEMBER)->exists());

        MemberFactory::new()->setOwner($group)->setUser($groupAdmin)->setAdmin()->create();
        $this->assertTrue(Member::query()->where('group_id', $group->entityId())->where(
            'user_id',
            $groupAdmin->entityId()
        )->where('member_type', Member::ADMIN)->exists());

        return [$repository, $user, $group, $stranger, $groupMember, $groupAdmin];
    }

    /**
     * @depends testCreateResource
     * @param  array<mixed> $params
     * @return array<mixed>
     */
    public function testCreateResourceSuccess($params)
    {
        /**
         * @var PrivacyPolicy $repository
         * @var User          $user
         * @var User          $group
         * @var User          $stranger
         * @var User          $groupMember
         * @var User          $groupAdmin
         */
        [$repository, $user, $group, $stranger, $groupMember, $groupAdmin] = $params;

        // Owner can post on group.
        $this->assertTrue($repository->checkCreateOnOwner($user, $group));

        // Group can post on itself.
        $this->assertTrue($repository->checkCreateOnOwner($group, $group));

        // Stranger cannot post on group.
        $this->assertFalse($repository->checkCreateOnOwner($stranger, $group));

        // Group member can post on group.
        $this->assertTrue($repository->checkCreateOnOwner($groupMember, $group));

        // Page admin can post on group.
        $this->assertTrue($repository->checkCreateOnOwner($groupAdmin, $group));

        return [$repository, $user, $group, $stranger, $groupMember, $groupAdmin];
    }

    /**
     * @depends testCreateResourceSuccess
     * @param array<mixed> $params
     */
    public function testCreateResourceWithContentByGroupCreatorSuccess($params)
    {
        /**
         * @var PrivacyPolicy $repository
         * @var User          $user
         * @var User          $group
         * @var User          $stranger
         * @var User          $groupMember
         * @var User          $groupAdmin
         */
        [$repository, $user, $group, $stranger, $groupMember, $groupAdmin] = $params;

        $blog  = BlogFactory::new()->setUser($user)->setOwner($group)->make();
        $photo = PhotoFactory::new()->setUser($user)->setOwner($group)->make();
        $post  = PostFactory::new()->setUser($user)->setOwner($group)->make();
        $video = VideoFactory::new()->setUser($user)->setOwner($group)->make();

        // Creator can post any resource on group.
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
     * @depends testCreateResourceSuccess
     * @param array<mixed> $params
     */
    public function testCreateResourceWithContentByGroupAdminSuccess($params)
    {
        /**
         * @var PrivacyPolicy $repository
         * @var User          $user
         * @var User          $group
         * @var User          $stranger
         * @var User          $groupMember
         * @var User          $groupAdmin
         */
        [$repository, $user, $group, $stranger, $groupMember, $groupAdmin] = $params;

        $blog  = BlogFactory::new()->setUser($groupAdmin)->setOwner($group)->make();
        $photo = PhotoFactory::new()->setUser($groupAdmin)->setOwner($group)->make();
        $post  = PostFactory::new()->setUser($groupAdmin)->setOwner($group)->make();
        $video = VideoFactory::new()->setUser($groupAdmin)->setOwner($group)->make();

        // Group admin can post any resource.
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
     * @depends testCreateResourceSuccess
     * @param array<mixed> $params
     */
    public function testCreateResourceWithContentByGroupMemberSuccess($params)
    {
        /**
         * @var PrivacyPolicy $repository
         * @var User          $user
         * @var User          $group
         * @var User          $stranger
         * @var User          $groupMember
         * @var User          $groupAdmin
         */
        [$repository, $user, $group, $stranger, $groupMember, $groupAdmin] = $params;

        $blog  = BlogFactory::new()->setUser($groupMember)->setOwner($group)->make();
        $photo = PhotoFactory::new()->setUser($groupMember)->setOwner($group)->make();
        $post  = PostFactory::new()->setUser($groupMember)->setOwner($group)->make();
        $video = VideoFactory::new()->setUser($groupMember)->setOwner($group)->make();

        // Group member can post blog, photo, post, video.
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
     * @depends testCreateResourceSuccess
     * @param array<mixed> $params
     */
    public function testCreateResourceWithContentByStrangerNotGroupMemberSuccess($params)
    {
        /**
         * @var PrivacyPolicy $repository
         * @var User          $user
         * @var User          $group
         * @var User          $stranger
         * @var User          $groupMember
         * @var User          $groupAdmin
         */
        [$repository, $user, $group, $stranger, $groupMember, $groupAdmin] = $params;

        $blog  = BlogFactory::new()->setUser($stranger)->setOwner($group)->make();
        $photo = PhotoFactory::new()->setUser($stranger)->setOwner($group)->make();
        $post  = PostFactory::new()->setUser($stranger)->setOwner($group)->make();
        $video = VideoFactory::new()->setUser($stranger)->setOwner($group)->make();

        // User does not join group cannot post anything.
        $this->assertFalse($repository->checkCreateResourceOnOwner($blog));
        $this->assertFalse($repository->checkCreateResourceOnOwner($photo));
        $this->assertFalse($repository->checkCreateResourceOnOwner($post));
        $this->assertFalse($repository->checkCreateResourceOnOwner($video));

        foreach ([$blog, $photo, $post, $video] as $resource) {
            // If you try to save resource, it will throw error.
            try {
                $resource->save();
            } catch (\Exception $e) {
                $this->assertInstanceOf(HttpException::class, $e);
                $this->assertEquals(403, $e->getStatusCode());
                $this->assertEquals(__p('core::validation.unable_to_create_this_item_due_to_privacy'), $e->getMessage());
            }
        }
    }
}
