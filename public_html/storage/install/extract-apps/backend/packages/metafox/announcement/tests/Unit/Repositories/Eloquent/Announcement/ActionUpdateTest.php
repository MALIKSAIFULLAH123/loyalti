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

class ActionUpdateTest extends TestCase
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
    public function testUpdateAnnouncement()
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);

        $style = Style::query()->first();
        $this->assertInstanceOf(Style::class, $style);

        $resource = Announcement::factory()->setUser($user)->setStyle($style)->create();
        $this->assertInstanceOf(Announcement::class, $resource);

        $newSubject = Str::snake($this->faker->name);
        $isActive   = 0;
        $params     = [
            'subject_var' => $newSubject,
            'is_active'   => $isActive,
        ];

        /** @var AnnouncementRepository $repository */
        $repository   = resolve(AnnouncementRepositoryInterface::class);
        $announcement = $repository->updateAnnouncement($user, $resource->entityId(), $params);
        $this->assertInstanceOf(Announcement::class, $announcement);
        $this->assertEquals($newSubject, $announcement->subject_var);
        $this->assertEquals($isActive, $announcement->is_active);
    }
}

// end
