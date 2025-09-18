<?php

namespace MetaFox\Forum\Tests\Unit\Models;

use MetaFox\Forum\Models\Forum;
use Tests\TestCase;

class ForumTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreateInstance()
    {
        $user = $this->asSuperAdminUser();
        $this->actingAs($user);

        // todo testing: add ForumFactory.
        $resource = new Forum([]);

        $this->assertInstanceOf(Forum::class, $resource);
    }
}

// end
