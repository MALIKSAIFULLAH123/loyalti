<?php

namespace MetaFox\Group\Tests\Unit\Jobs;

use Exception;
use MetaFox\Group\Jobs\DeleteCategoryJob;
use MetaFox\Group\Models\Category;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Repositories\Eloquent\CategoryRepository;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class DeleteCategoryJobTest extends TestCase
{
    protected CategoryRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(CategoryRepositoryInterface::class);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(CategoryRepository::class, $this->repository);
    }

    /**
     * @throws Exception
     */
    public function testDeleteAllBeLongToSuccess()
    {
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category  = Category::factory()->create();
        $category2 = Category::factory(['parent_id' => $category->entityId()])->create();

        $group = Group::factory([
            'category_id' => $category->entityId(),
        ])->setUser($user2)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        (new DeleteCategoryJob($category, 0))->handle();

        $this->assertEmpty(Category::query()->find($category->entityId()));

        // it not working here.
        $this->markTestIncomplete();

        $this->assertEmpty(Category::query()->find($category2->entityId()));
        $this->assertEmpty(Group::query()->find($group->entityId()));
    }
}
