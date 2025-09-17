<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Question;

use MetaFox\Group\Http\Requests\v1\Question\FormRequest;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use Tests\TestCase;
use Tests\TestFormRequest;

class CreateFormRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return FormRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->failIf('group_id', null, 'string')
        );
    }

    public function testInstance()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertInstanceOf(User::class, $user);

        $group = Group::factory()
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->setUser($user)
            ->create();

        $this->assertInstanceOf(Group::class, $group);

        return $group;
    }

    /**
     * @depends testInstance
     */
    public function testCreateFormSuccess(Group $group)
    {
        $form = $this->buildForm([
            'group_id' => $group->entityId(),
        ]);

        $form->validateResolved();

        $this->assertIsArray($form->validated());
    }
}
