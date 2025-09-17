<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\Page;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class UpdateCoverTest extends TestCase
{
    protected PageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(PageRepository::class);
    }

    /**
     * @throws AuthorizationException
     */
    public function testSuccess()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);

        $page = Page::factory()->setUser($user)->create(['category_id' => 1]);

        $image = UploadedFile::fake()->image('cover.jpg');

        $this->actingAs($page->user);

        $this->skipPolicies(PhotoPolicy::class);

        $this->repository->updateCover($user, $page->entityId(), ['image' => $image]);
        $page->refresh();

        $this->assertNotEmpty($page->cover);
    }
}
