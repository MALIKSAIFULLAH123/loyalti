<?php

namespace MetaFox\Page\Tests\Unit\Policies;

use MetaFox\Page\Models\Category;
use MetaFox\Page\Policies\CategoryPolicy;
use Tests\TestCase;
use Tests\Traits\TestUserPermissions;

class PageCategoryPolicyTest extends TestCase
{
    use TestUserPermissions;

    public function policyName(): string
    {
        return CategoryPolicy::class;
    }

    public function resourceName(): string
    {
        return Category::class;
    }

    public static function provideUserPermisions()
    {
        foreach (['create', 'update', 'delete'] as $action) {
            yield 'admin can ' . $action => [
                ['core.has_admin_access' => true], $action, true,
            ];
        }

        foreach (['view', 'viewAny'] as $action) {
            yield 'Anyone can ' . $action => [
                [], $action, true,
            ];
        }
    }
}
