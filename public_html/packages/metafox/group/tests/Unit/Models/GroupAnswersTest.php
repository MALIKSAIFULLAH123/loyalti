<?php

namespace MetaFox\Group\Tests\Unit\Models;

use MetaFox\Group\Models\Answers;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GroupAnswersTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreateInstance()
    {
        $this->markTestIncomplete();
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $resource = Answers::factory()
        //    ->setUser($user)
        //    ->setOwner($user)
            ->create();

        $this->assertInstanceOf(Answers::class, $resource);
    }
}

// end
