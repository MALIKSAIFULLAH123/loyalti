<?php

namespace MetaFox\Announcement\Tests\Unit\Models;

use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Hidden as Model;
use MetaFox\Announcement\Models\Style;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class HiddenTest extends TestCase
{
    public function testCreateAnnouncement(): Announcement
    {
        $user  = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $style = Style::query()->first();

        $this->assertInstanceOf(Style::class, $style);

        $item = Announcement::factory()->setUser($user)->setStyle($style)->create();
        $this->assertInstanceOf(Announcement::class, $item);

        return $item;
    }

    /**
     * A basic unit test example.
     *
     * @depends testCreateAnnouncement
     * @return void
     */
    public function testCreateHidden(Announcement $item)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $model = Model::factory()->setUser($user)->setAnnouncement($item)->create();

        $this->assertInstanceOf(Model::class, $model);
        $this->assertNotEmpty($model->entityId());
    }
}
