<?php

namespace MetaFox\Group\Tests\Unit\Models;

use MetaFox\Group\Models\Member;
use MetaFox\Platform\Facades\PolicyGate;
use Tests\TestCase;

class GroupMemberTest extends TestCase
{
    /**
     * @return void
     */
    public function testPolicy()
    {
        $policy = PolicyGate::getPolicyFor(Member::class);
        $this->assertNotNull($policy);
    }
}

// end
