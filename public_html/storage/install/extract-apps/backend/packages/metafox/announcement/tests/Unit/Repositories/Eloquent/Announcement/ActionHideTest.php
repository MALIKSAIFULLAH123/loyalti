<?php

namespace MetaFox\Announcement\Tests\Unit\Repositories\Eloquent\Announcement;

use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Style;
use MetaFox\Announcement\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Announcement\Repositories\Eloquent\AnnouncementRepository;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ActionHideTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(AnnouncementRepositoryInterface::class);
        $this->assertInstanceOf(AnnouncementRepository::class, $repository);
    }

    /**
     * @depends testInstance
     */
    public function testDeleteAnnouncement()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);

        $style = Style::query()->first();
        $this->assertInstanceOf(Style::class, $style);

        $resource = Announcement::factory()->setUser($user)->setStyle($style)->create(['can_be_closed' => 1]);
        $this->assertInstanceOf(Announcement::class, $resource);

        /** @var AnnouncementRepository $repository */
        $repository   = resolve(AnnouncementRepositoryInterface::class);
        $announcement = $repository->hideAnnouncement($user, $resource->entityId());
        $this->assertInstanceOf(Announcement::class, $announcement);
    }
}

// end
