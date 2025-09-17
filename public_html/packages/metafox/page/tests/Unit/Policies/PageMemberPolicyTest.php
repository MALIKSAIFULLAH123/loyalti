<?php

namespace MetaFox\Page\Tests\Unit\Policies;

use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Policies\PageMemberPolicy;
use Tests\TestCase;

class PageMemberPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        $this->markTestIncomplete('To be implemented later to follow new Unit Test writing style');
    }

    public function policyName(): string
    {
        return PageMemberPolicy::class;
    }

    public function resourceName(): string
    {
        return PageMember::class;
    }

    public static function provideUserPermisions()
    {
        return [];
    }
}
