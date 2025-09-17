<?php

namespace MetaFox\Group\Tests\Feature\Privacy;

use MetaFox\Core\Models\Privacy;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\PrivacyStream;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GroupSecretTest extends TestCase
{
    public function testCreateInstance()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $group = Group::factory()
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::SECRET)
            ->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Group::class, $group);

        $privacyList = Privacy::query()->where([
            'item_id'      => $group->entityId(),
            'item_type'    => $group->entityType(),
            'privacy'      => MetaFoxPrivacy::FRIENDS,
            'privacy_type' => Group::GROUP_MEMBERS,
        ])->get();

        $this->assertSame(1, $privacyList->count());

        foreach ($privacyList as $privacy) {
            $this->assertInstanceOf(Privacy::class, $privacy);
            $this->assertTrue(PrivacyStream::query()->where(['item_id' => $group->entityId(), 'privacy_id' => $privacy->privacy_id])->exists());
        }
    }
}
