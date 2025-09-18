<?php

namespace Unit\Policies;

use MetaFox\Group\Models\Category as Resource;
use MetaFox\Group\Policies\CategoryPolicy as Policy;
use Tests\TestCase;

class CategoryPolicyTest extends TestCase
{
    use \Tests\Traits\TestUserPermissions;

    public function policyName(): string
    {
        return Policy::class;
    }

    public function resourceName(): string
    {
        return Resource::class;
    }

    public static function provideUserPermisions()
    {
        return [
            [['core.has_admin_access' => false], 'deleteOwn', false],
            [['core.has_admin_access' => false], 'delete', false],
            [['core.has_admin_access' => false], 'update', false],
            [['core.has_admin_access' => false], 'update', false],
            [['core.has_admin_access' => false], 'create', false],
            [['core.has_admin_access' => true], 'delete', true],
            [['core.has_admin_access' => true], 'update', true],
            [['core.has_admin_access' => true], 'update', true],
            [['core.has_admin_access' => true], 'create', true],
            [[], 'view', true],
            [[], 'viewAny', true],
        ];
    }
}
