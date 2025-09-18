<?php

namespace MetaFox\Group\Tests\Unit\Models;

use MetaFox\Group\Models\Invite as Resource;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\User\Contracts\UserContract;
use Tests\TestCase;

class GroupInviteTest extends TestCase
{
    /**
     * @return int
     */
    public function testMakeOne()
    {
        $user  = $this->createNormalUser();
        $other = $this->createNormalUser();

        /** @var resource $invite */
        $invite = Resource::factory()
            ->setUser($user)
            ->setOwner($other)
            ->makeOne([
                'status_id'   => 1,
                'group_id'    => 1,
                'invite_type' => 2,
            ]);

        $this->assertNotNull($invite->invite_type);

        $this->assertTrue($invite->saveQuietly());

        $invite->refresh();

        return $invite->entityId();
    }

    /**
     * @param  int  $id
     * @return void
     * @depends testMakeOne
     */
    public function testGroupInviteMorphs(int $id)
    {
        /** @var resource $invite */
        $invite = Resource::find($id);

        $this->assertInstanceOf(ContractUser::class, $invite->user);
        $this->assertInstanceOf(ContractUser::class, $invite->owner);

        if ($invite->group) { // mock group maybe nulled.
            $this->assertInstanceOf(ContractUser::class, $invite->group);
        }

        $this->assertTrue($invite->deleteQuietly());
    }
}

// end
