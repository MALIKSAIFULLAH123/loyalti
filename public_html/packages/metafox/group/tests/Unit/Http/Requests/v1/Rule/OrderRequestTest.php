<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Rule;

use Illuminate\Validation\ValidationException;
use MetaFox\Group\Http\Requests\v1\Rule\OrderingRequest as Request;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class OrderRequestTest.
 */
class OrderRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('group_id', 'order_ids'),
        );
    }

    public function testSuccess(): Group
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $group = Group::factory()->setUser($user)->setPrivacyType(PrivacyTypeHandler::PUBLIC)->create();
        $form  = $this->buildForm([
            'group_id'  => $group->entityId(),
            'order_ids' => [1 => 1],
        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());

        return $group;
    }

    public function testGroupExist()
    {
        $form = $this->buildForm([
            'group_id'  => 0,
            'order_ids' => [1 => 1],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testGroupNumeric()
    {
        $form = $this->buildForm([
            'group_id'  => 'Test',
            'order_ids' => [1 => 1],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}
