<?php

namespace MetaFox\Blog\Tests\Unit\Policies\Category;

use MetaFox\Blog\Models\Category as Resource;
use MetaFox\Blog\Policies\CategoryPolicy as Policy;
use MetaFox\User\Models\User;
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
            [['admincp.has_admin_access' => false], 'deleteOwn', false],
            [['admincp.has_admin_access' => false], 'delete', false],
            [['admincp.has_admin_access' => false], 'update', false],
            [['admincp.has_admin_access' => false], 'update', false],
            [['admincp.has_admin_access' => false], 'create', false],

            [['admincp.has_admin_access' => true], 'deleteOwn', true],
            [['admincp.has_admin_access' => true], 'delete', true],
            [['admincp.has_admin_access' => true], 'update', true],
            [['admincp.has_admin_access' => true], 'update', true],
            [['admincp.has_admin_access' => true], 'create', true],
            [[], 'view', true],
            [[], 'viewAny', true],
        ];
    }

    public function testViewOwner()
    {
        $this->assertTrue($this->mockPolicy()->viewOwner($this->mockUser(), new User()));
    }

    public function testViewActive()
    {
        $policy   = $this->mockPolicy();
        $resource = $this->mockResource();

        $resource->is_active = true;
        $this->assertTrue($policy->viewActive($resource));

        $resource->is_active = false;
        $this->assertFalse($policy->viewActive($resource));

        $resource->is_active = null;
        $this->assertFalse($policy->viewActive($resource));
    }
}
