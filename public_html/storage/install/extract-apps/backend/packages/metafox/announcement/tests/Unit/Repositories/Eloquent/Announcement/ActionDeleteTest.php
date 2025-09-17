<?php

namespace MetaFox\Announcement\Tests\Unit\Repositories\Eloquent\Announcement;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\AnnouncementText;
use MetaFox\Announcement\Models\Style;
use MetaFox\Announcement\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Announcement\Repositories\Eloquent\AnnouncementRepository;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ActionDeleteTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(AnnouncementRepositoryInterface::class);
        $this->assertInstanceOf(AnnouncementRepository::class, $repository);
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testDeleteAnnouncement()
    {
        $user = $this->asAdminUser();

        $style = Style::query()->first();
        $this->assertInstanceOf(Style::class, $style);

        $resource = Announcement::factory()->setUser($user)->setStyle($style)->create();
        $this->assertInstanceOf(Announcement::class, $resource);

        /** @var AnnouncementRepository $repository */
        $repository = resolve(AnnouncementRepositoryInterface::class);
        $repository->deleteAnnouncement($user, $resource->entityId());

        $this->assertEmpty(Announcement::query()->find($resource->entityId()));
        $this->assertEmpty(AnnouncementText::query()->find($resource->entityId()));
    }
}

// end
