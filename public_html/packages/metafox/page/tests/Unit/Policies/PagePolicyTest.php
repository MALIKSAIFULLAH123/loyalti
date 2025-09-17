<?php

namespace MetaFox\Page\Tests\Unit\Policies;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Policies\PagePolicy;
use Tests\TestCase;
use Tests\Traits\TestUserPermissions;

class PagePolicyTest extends TestCase
{
    use TestUserPermissions;

    public function policyName(): string
    {
        return PagePolicy::class;
    }

    public function resourceName(): string
    {
        return Page::class;
    }

    public static function provideUserPermisions()
    {
        foreach (['viewAny', 'view', 'update', 'delete'] as $action) {
            yield 'Moderator can ' . $action => [['page.moderate' => true], $action, true];
        }

        yield 'Test View Permission' => [
            ['page.view' => false], 'view', false,
        ];
    }

    public function testGetEntityType()
    {
        $policy = $this->mockPolicy();

        $this->assertSame('page', $policy->getEntityType());
    }
}
