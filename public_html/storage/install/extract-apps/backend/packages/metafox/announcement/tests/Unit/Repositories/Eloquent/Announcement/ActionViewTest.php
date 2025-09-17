<?php

namespace MetaFox\Announcement\Tests\Unit\Repositories\Eloquent\Announcement;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Style;
use MetaFox\Announcement\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Announcement\Repositories\Eloquent\AnnouncementRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ActionViewTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(AnnouncementRepositoryInterface::class);
        $this->assertInstanceOf(AnnouncementRepository::class, $repository);

        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $style = Style::query()->first();
        $this->assertInstanceOf(Style::class, $style);

        $items = Announcement::factory()->setUser($admin)->setStyle($style)->count(5)->create(['can_be_closed' => 1]);
        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewAnnouncementsWithAdmincpView()
    {
        $user = $this->asAdminUser();

        /** @var AnnouncementRepository $repository */
        $repository    = resolve(AnnouncementRepositoryInterface::class);

        $announcements = $repository->viewAnnouncements($user, [
            'limit' => 2,
        ]);
        $this->assertNotNull($announcements);
    }
}

// end
