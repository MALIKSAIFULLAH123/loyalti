<?php

namespace MetaFox\Group\Tests\Feature\Privacy;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\PrivacyStream;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GroupClosedTest extends TestCase
{
    public function testCreateInstance()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $group = Group::factory()
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::CLOSED)
            ->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Group::class, $group);

        $this->assertTrue(PrivacyStream::query()->where(['item_id' => $group->entityId(), 'privacy_id' => MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID])->exists());
    }
}
