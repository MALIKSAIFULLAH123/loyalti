<?php

namespace MetaFox\Announcement\Tests\Unit\Repositories\Eloquent\Announcement;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Style;
use MetaFox\Announcement\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Announcement\Repositories\Eloquent\AnnouncementRepository;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ActionCreateTest extends TestCase
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
    public function testCreateAnnouncement()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);

        $style = Style::factory()->create(['name' => 'phpunit']);

        $this->assertInstanceOf(Style::class, $style);
        $params = Announcement::factory()->makeOne([
            'is_active' => 1,
            'style'     => $style->entityId(),
        ])->toArray();

        /** @var AnnouncementRepository $repository */
        $repository   = resolve(AnnouncementRepositoryInterface::class);
        $announcement = $repository->createAnnouncement($user, $params);
        $this->assertInstanceOf(Announcement::class, $announcement);
    }
}

// end
