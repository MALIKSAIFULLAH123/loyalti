<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Member;

use MetaFox\Group\Http\Requests\v1\Member\StoreRequest as Request;
use MetaFox\Group\Models\Category;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
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

    public function testSuccess()
    {
        $category = Category::factory()->create();
        $user     = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create(['category_id' => $category->entityId()]);
        $form = $this->buildForm([
            'group_id' => $group->entityId(),

        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('group_id'),
            $this->failIf('group_id', null, 'string', 0)
        );
    }
}
