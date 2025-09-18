<?php

namespace MetaFox\Saved\Tests\Unit\Models;

use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\SavedAgg;
use Tests\TestCase;

class SavedAggTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreateInstance()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $resource = SavedAgg::factory()->setUser($user)->create(['item_type' => 'test']);

        $this->assertInstanceOf(SavedAgg::class, $resource);
    }
}
