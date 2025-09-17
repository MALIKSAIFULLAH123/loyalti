<?php

namespace MetaFox\Group\Tests\Unit\Models;

use MetaFox\Group\Models\Rule;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GroupRuleTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreateInstance()
    {
        $this->markTestIncomplete();
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $resource = Rule::factory()
            //    ->setUser($user)
            //    ->setOwner($user)
            ->create();

        $this->assertInstanceOf(Rule::class, $resource);
    }
}

// end
