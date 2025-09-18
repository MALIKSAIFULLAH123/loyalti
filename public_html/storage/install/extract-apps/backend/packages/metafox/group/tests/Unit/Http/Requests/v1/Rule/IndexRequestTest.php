<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Rule;

use MetaFox\Group\Http\Requests\v1\Rule\IndexRequest as Request;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class IndexRequestTest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('group_id'),
            $this->failIf('group_id', 'string', 0, []),
            $this->withSampleParameters('page', 'limit')
        );
    }

    public function testSuccess()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $group = Group::factory()->setUser($user)->setPrivacyType(PrivacyTypeHandler::PUBLIC)->create();
        $form  = $this->buildForm(['group_id' => $group->entityId()]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }
}
