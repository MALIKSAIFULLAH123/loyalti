<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Invite;

use MetaFox\Group\Http\Requests\v1\Invite\StoreRequest;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class IndexRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return StoreRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('group_id', 'user_ids'),
            $this->failIf('user_ids', 0, null, 'string', []),
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)->setPrivacyType(PrivacyTypeHandler::PUBLIC)->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(User::class, $user1);
        $this->assertInstanceOf(Group::class, $group);

        return [$user, $user1, $group];
    }

    /**
     * @depends testInstance
     */
    public function testRequestSuccess(array $params)
    {
        [$user, $user1, $group] = $params;

        $this->actingAs($user);

        $form = $this->buildForm([
            'group_id' => $group->entityId(),
            'user_ids' => [
                $user1->entityId(),
            ],
        ]);

        $form->validateResolved();
        $data = $form->validated();
        $this->assertNotEmpty($data);
        $this->assertNotEmpty($data['user_ids']);
    }
}
