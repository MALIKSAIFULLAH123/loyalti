<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Group;

use MetaFox\Group\Http\Requests\v1\Group\RuleConfirmationRequest;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use Tests\TestFormRequest;

class RuleConfirmationRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return RuleConfirmationRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('group_id', 'is_rule_confirmation'),
            $this->failIf('group_id', 0, null, [], 'string'),
            $this->failIf('is_rule_confirmation', 'string'),
        );
    }

    public function testInstance()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertInstanceOf(User::class, $user);

        $this->be($user);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $this->assertInstanceOf(Group::class, $group);

        return [$user, $group];
    }

    /**
     * @depends testInstance
     */
    public function testSuccess(array $data)
    {
        [$user, $group] = $data;

        $this->be($user);

        $form = $this->buildForm([
            'group_id'             => $group->id,
            'is_rule_confirmation' => $this->faker->boolean,
        ]);

        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }
}
