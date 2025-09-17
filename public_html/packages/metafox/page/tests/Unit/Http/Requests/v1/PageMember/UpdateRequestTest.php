<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\PageMember;

use MetaFox\Page\Http\Requests\v1\PageMember\UpdateRequest as Request;
use MetaFox\Page\Models\PageMember;
use MetaFox\Platform\UserRole;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class UpdateRequestTest.
 */
class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('member_type', 'user_id'),
            $this->failIf('member_type', 'string', null),
            $this->passIf('member_type', 0, 1),
            $this->failIf('user_id', 0, null, 'string'),
        );
    }

    public function testSuccess()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $form = $this->buildForm([
            'member_type' => PageMember::MEMBER,
            'user_id'     => $user->entityId(),

        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }
}
