<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Tests\Unit\Models;

use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\SavedList;
use MetaFox\User\Models\User;
use Tests\TestCase;

class SavedListTest extends TestCase
{
    public function testCreateSavedList()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $model = SavedList::factory()->setUser($user)->create();

        $this->assertInstanceOf(SavedList::class, $model);
        $this->assertInstanceOf(User::class, $model->user);
        $this->assertNotEmpty($model->name);
    }

    public function testPolicy()
    {
        $policy = PolicyGate::getPolicyFor(SavedList::class);
        $this->assertNotNull($policy);
    }
}
