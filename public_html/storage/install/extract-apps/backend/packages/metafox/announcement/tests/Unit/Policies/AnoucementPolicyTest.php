<?php

namespace MetaFox\Announcement\Tests\Unit\Policies;

use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Policies\AnnouncementPolicy;
use Tests\TestCase;
use Tests\Traits\TestUserPermissions;

class AnoucementPolicyTest extends TestCase
{
    use TestUserPermissions;

    public function policyName(): string
    {
        return AnnouncementPolicy::class;
    }

    public function resourceName(): string
    {
        return Announcement::class;
    }

    public static function provideUserPermisions()
    {
        return [
            // viewAny
            [['announcement.view' => false], 'viewAny', false],
            [['announcement.view' => true], 'viewAny', true],
            // view
            [['announcement.view' => false], 'view', false],
            [['announcement.view' => true], 'view', true],
            // create
            [['admincp.has_admin_access' => false], 'create', false],
            [['admincp.has_admin_access' => true], 'create', true],
            // update
            [['admincp.has_admin_access' => false], 'update', false],
            [['admincp.has_admin_access' => true], 'update', true],
            // delete
            [['admincp.has_admin_access' => false], 'delete', false],
            [['admincp.has_admin_access' => true], 'delete', true],
            // deleteOwn
            [['admincp.has_admin_access' => false], 'deleteOwn', false],
            [['admincp.has_admin_access' => true], 'deleteOwn', true],
            // markAsRead
            [['announcement.view' => false], 'markAsRead', false],
            [['announcement.view' => true], 'markAsRead', true],
        ];
    }
}
