<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Member;

use MetaFox\Group\Http\Requests\v1\Member\DeleteGroupAdminRequest as Request;
use MetaFox\Group\Models\Category;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestFormRequest;

/**
 * Class DeleteGroupAdminRequestTest.
 */
class DeleteGroupAdminRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function testCreateGroup(): Group
    {
        $category = Category::factory()->create();
        $user     = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $group    = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create(['category_id' => $category->entityId()]);

        $this->assertNotEmpty($group);

        return $group;
    }

    /**
     * @depends testCreateGroup
     */
    public function testSuccess(Group $group)
    {
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $form = $this->buildForm([
            'group_id'  => $group->entityId(),
            'user_id'   => $user2->entityId(),
            'is_delete' => 0,
        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('group_id', 'user_id', 'is_delete'),
            $this->failIf('group_id', null, 0, 'string'),
            $this->failIf('user_id', 0, null, ['string'], [0]),
            $this->failIf('is_delete', 'string', null, ['string'], [0]),
            $this->passIf('is_delete', 0, 1)
        );
    }
}
