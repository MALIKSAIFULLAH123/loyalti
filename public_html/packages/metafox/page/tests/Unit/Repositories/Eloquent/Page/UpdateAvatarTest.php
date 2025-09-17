<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\Page;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class UpdateAvatarTest extends TestCase
{
    private PageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(PageRepository::class);
    }

    public function testInstance()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        $category = Category::factory()->create();

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        $this->assertInstanceOf(Page::class, $page);

        return $page;
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException|ValidationException
     */
    public function testSuccess(Page $page)
    {
        $user  = $page->user;
        $image = UploadedFile::fake()->image('test.jpg', 1501, 1000);

        $this->actingAs($user);

        $this->skipPolicies(PhotoPolicy::class);
        $this->repository->updateAvatar($user, $page->entityId(), ['image' => $image, 'image_crop' => $this->imageBase64]);
        $page->refresh();

        $this->assertNotEmpty($page->avatar);

        return $page;
    }

    /**
     * @depends testSuccess
     * @throws AuthorizationException|ValidationException
     */
    public function testUpdateCropSuccess(Page $page)
    {
        $user = $page->user;
        $this->actingAs($page->user);
        $oldPath = $page->avatar;
        $this->skipPolicies(PhotoPolicy::class);
        $this->repository->updateAvatar($user, $page->entityId(), ['image' => null, 'image_crop' => $this->imageBase64]);
        $page->refresh();

        $this->assertNotEmpty($page->avatar);
        $this->assertNotSame($oldPath, $page->avatar);
    }
}
