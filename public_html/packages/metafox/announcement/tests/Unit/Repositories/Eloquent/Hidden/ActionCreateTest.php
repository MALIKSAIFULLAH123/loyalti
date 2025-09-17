<?php

namespace MetaFox\Announcement\Tests\Unit\Repositories\Eloquent\Hidden;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Hidden;
use MetaFox\Announcement\Models\Style;
use MetaFox\Announcement\Repositories\Eloquent\HiddenRepository;
use MetaFox\Announcement\Repositories\HiddenRepositoryInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ActionCreateTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(HiddenRepositoryInterface::class);
        $this->assertInstanceOf(HiddenRepository::class, $repository);
    }

    public function testCreateResource(): Announcement
    {
        $user  = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $style = Style::query()->first();
        $this->assertInstanceOf(Style::class, $style);

        $resource = Announcement::factory()->setUser($user)->setStyle($style)->create(['can_be_closed' => 1]);
        $this->assertInstanceOf(Announcement::class, $resource);

        return $resource;
    }

    /**
     * @param Announcement $resource
     * @depends testCreateResource
     * @depends testInstance
     */
    public function testCreateHidden(Announcement $resource)
    {
        $user       = $this->createNormalUser();
        $this->be($user);
        $repository = resolve(HiddenRepositoryInterface::class);
        $item       = $repository->createHidden($user, $resource);
        $this->assertInstanceOf(Hidden::class, $item);
        $this->assertNotEmpty($item->entityId());
    }
}

// end
