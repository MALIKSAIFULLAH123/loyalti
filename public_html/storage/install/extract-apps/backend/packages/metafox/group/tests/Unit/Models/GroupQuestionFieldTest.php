<?php

namespace MetaFox\Group\Tests\Unit\Models;

use MetaFox\Group\Models\QuestionField;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GroupQuestionFieldTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreateInstance()
    {
        $this->markTestIncomplete();
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $resource = QuestionField::factory()
        //    ->setUser($user)
        //    ->setOwner($user)
            ->create();

        $this->assertInstanceOf(QuestionField::class, $resource);
    }
}

// end
