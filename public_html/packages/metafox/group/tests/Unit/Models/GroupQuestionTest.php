<?php

namespace MetaFox\Group\Tests\Unit\Models;

use MetaFox\Group\Models\Question;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GroupQuestionTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreateInstance()
    {
        $this->markTestIncomplete();
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $resource = Question::factory()
        //    ->setUser($user)
        //    ->setOwner($user)
            ->create();

        $this->assertInstanceOf(Question::class, $resource);
    }
}

// end
