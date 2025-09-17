<?php

namespace MetaFox\Announcement\Tests\Unit\Models;

use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Style;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCreateAnnouncement()
    {
        $user = $this->asAdminUser();

        $style = Style::query()->first();
        $this->assertInstanceOf(Style::class, $style);

        $model = Announcement::factory()
            ->setUser($user)
            ->setStyle($style)
            ->create();
        $model->refresh();

        $this->assertInstanceOf(Content::class, $model);
        $this->assertInstanceOf(HasTotalView::class, $model);
        $this->assertInstanceOf(Announcement::class, $model);
        $this->assertNotEmpty($model->entityId());
        $this->assertNotEmpty($model->user->entityId());
        $this->assertNotEmpty($model->style?->entityId());
        $this->assertNotNull($model->total_view);
    }
}
