<?php

namespace MetaFox\Group\Tests\Unit\Support;

use MetaFox\Group\Database\Factories\GroupFactory;
use MetaFox\Group\Database\Factories\MemberFactory;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Photo\Database\Factories\PhotoFactory;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GroupPrivacyTypeHandlerTest extends TestCase
{
    public function testCreateResource()
    {
        $owner = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $publicGroup = GroupFactory::new()->setUser($owner)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $closedGroup = GroupFactory::new()->setUser($owner)
            ->setPrivacyType(PrivacyTypeHandler::CLOSED)
            ->create();

        $secretGroup = GroupFactory::new()->setUser($owner)
            ->setPrivacyType(PrivacyTypeHandler::SECRET)
            ->create();

        // Add member
        MemberFactory::new()->setOwner($publicGroup)->setUser($owner)->create();
        MemberFactory::new()->setOwner($closedGroup)->setUser($owner)->create();
        MemberFactory::new()->setOwner($secretGroup)->setUser($owner)->create();

        $photoPublicGroup = PhotoFactory::new()->setUser($owner)->setOwner($publicGroup)->create([]);

        $photoClosedGroup = PhotoFactory::new()->setUser($owner)->setOwner($closedGroup)->create([]);

        $photoSecretGroup = PhotoFactory::new()->setUser($owner)->setOwner($secretGroup)->create([]);

        $service = resolve(PrivacyTypeHandler::class);
        $this->assertInstanceOf(PrivacyTypeHandler::class, $service);

        return [
            $service,
            $owner,
            $publicGroup,
            $closedGroup,
            $secretGroup,
            $photoPublicGroup,
            $photoClosedGroup,
            $photoSecretGroup,
        ];
    }

    /**
     * @depends testCreateResource
     * @param array<int, mixed> $params
     */
    public function testOwnerGroupCanViewSuccess($params)
    {
        /**
         * @var PrivacyTypeHandler $service
         * @var User               $owner
         * @var Group              $publicGroup
         * @var Group              $closedGroup
         * @var Group              $secretGroup
         * @var Content            $photoPublicGroup
         * @var Content            $photoClosedGroup
         * @var Content            $photoSecretGroup
         */
        [
            $service,
            $owner,
            $publicGroup,
            $closedGroup,
            $secretGroup,
            $photoPublicGroup,
            $photoClosedGroup,
            $photoSecretGroup,
        ] = $params;

        $this->assertTrue($service->checkCanViewGroup($owner, $publicGroup));
        $this->assertTrue($service->checkCanViewGroup($owner, $closedGroup));
        $this->assertTrue($service->checkCanViewGroup($owner, $secretGroup));

        $this->assertTrue($service->checkCanViewMember($owner, $publicGroup));
        $this->assertTrue($service->checkCanViewMember($owner, $closedGroup));
        $this->assertTrue($service->checkCanViewMember($owner, $secretGroup));

        $this->assertTrue($service->checkCanViewContent($owner, $photoPublicGroup));
        $this->assertTrue($service->checkCanViewContent($owner, $photoClosedGroup));
        $this->assertTrue($service->checkCanViewContent($owner, $photoSecretGroup));
    }

    /**
     * @depends testCreateResource
     * @param  array<int, mixed>        $params
     * @return array<int,        mixed>
     */
    public function testStrangerCanView($params)
    {
        /**
         * @var PrivacyTypeHandler $service
         * @var User               $owner
         * @var Group              $publicGroup
         * @var Group              $closedGroup
         * @var Group              $secretGroup
         * @var Content            $photoPublicGroup
         * @var Content            $photoClosedGroup
         * @var Content            $photoSecretGroup
         */
        [
            $service,
            $owner,
            $publicGroup,
            $closedGroup,
            $secretGroup,
            $photoPublicGroup,
            $photoClosedGroup,
            $photoSecretGroup,
        ] = $params;

        $stranger = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertTrue($service->checkCanViewGroup($stranger, $publicGroup));
        $this->assertTrue($service->checkCanViewGroup($stranger, $closedGroup));
        $this->assertFalse($service->checkCanViewGroup($stranger, $secretGroup));

        $this->assertTrue($service->checkCanViewMember($stranger, $publicGroup));
        $this->assertTrue($service->checkCanViewMember($stranger, $closedGroup));
        $this->assertFalse($service->checkCanViewMember($stranger, $secretGroup));

        $this->assertTrue($service->checkCanViewContent($stranger, $photoPublicGroup));
        $this->assertFalse($service->checkCanViewContent($stranger, $photoClosedGroup));
        $this->assertFalse($service->checkCanViewContent($stranger, $photoSecretGroup));

        return [
            $service,
            $owner,
            $publicGroup,
            $closedGroup,
            $secretGroup,
            $photoPublicGroup,
            $photoClosedGroup,
            $photoSecretGroup,
            $stranger,
        ];
    }

    /**
     * @depends testStrangerCanView
     * @param array<int, mixed> $params
     */
    public function testStrangerCanViewGroupAfterJoin($params)
    {
        /**
         * @var PrivacyTypeHandler $service
         * @var User               $owner
         * @var Group              $publicGroup
         * @var Group              $closedGroup
         * @var Group              $secretGroup
         * @var Content            $photoPublicGroup
         * @var Content            $photoClosedGroup
         * @var Content            $photoSecretGroup
         * @var User               $stranger
         */
        [
            $service,
            $owner,
            $publicGroup,
            $closedGroup,
            $secretGroup,
            $photoPublicGroup,
            $photoClosedGroup,
            $photoSecretGroup,
            $stranger
        ] = $params;

        // Add member to group secret.
        MemberFactory::new()->setOwner($secretGroup)->setUser($stranger)->create();

        $this->assertTrue($service->checkCanViewGroup($stranger, $secretGroup));
        $this->assertTrue($service->checkCanViewMember($stranger, $secretGroup));
        $this->assertTrue($service->checkCanViewContent($stranger, $photoSecretGroup));

        // Add member to group closed.
        MemberFactory::new()->setOwner($closedGroup)->setUser($stranger)->create();

        $this->assertTrue($service->checkCanViewGroup($stranger, $closedGroup));
        $this->assertTrue($service->checkCanViewMember($stranger, $closedGroup));
        $this->assertTrue($service->checkCanViewContent($stranger, $photoClosedGroup));
    }
}
