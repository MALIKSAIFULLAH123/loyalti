<?php

namespace MetaFox\Page\Tests\Unit\Models;

use MetaFox\Page\Models\PageMember;
use MetaFox\Platform\Facades\PolicyGate;
use Tests\TestCase;

class PageMemberTest extends TestCase
{
    public function testPolicy()
    {
        $policy = PolicyGate::getPolicyFor(PageMember::class);
        $this->assertNotNull($policy);
    }
}

// end
