<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Rule;

use MetaFox\Group\Http\Requests\v1\Rule\StoreRequest as Request;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('group_id', 'title'),
            $this->failIf('group_id', 0, null, 'string'),
            $this->failIf('title', 0, null, str_pad('A', 1000, 'A')),
            $this->failIf('description', 0, str_pad('A', 1000, 'A')),
        );
    }

    public function testSuccess(): Group
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $group = Group::factory()->setUser($user)->setPrivacyType(PrivacyTypeHandler::PUBLIC)->create();
        $form  = $this->buildForm([
            'group_id'    => $group->entityId(),
            'title'       => $this->faker->title,
            'description' => $this->faker->text,
        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());

        return $group;
    }
}
