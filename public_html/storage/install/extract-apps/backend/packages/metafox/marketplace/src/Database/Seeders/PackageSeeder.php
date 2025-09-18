<?php

namespace MetaFox\Marketplace\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use MetaFox\Marketplace\Repositories\Eloquent\CategoryRepository;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;

/**
 * Class PackageSeeder.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class PackageSeeder extends Seeder
{
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $repository;

    /**
     * PrivacyDatabaseSeeder constructor.
     *
     * @param CategoryRepository $repository
     */
    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->categories();
        $this->categoryRelationData();
        $this->removeUnusedMenuItems();
    }

    private function categories()
    {
        $categories = [
            ['name' => 'marketplace::phrase.category_community', 'name_url' => 'community', 'ordering' => 0],
            ['name' => 'marketplace::phrase.category_houses', 'name_url' => 'houses', 'ordering' => 1],
            ['name' => 'marketplace::phrase.category_jobs', 'name_url' => 'jobs', 'ordering' => 2],
            ['name' => 'marketplace::phrase.category_pets', 'name_url' => 'pets', 'ordering' => 3],
            ['name' => 'marketplace::phrase.category_rentals', 'name_url' => 'rentals', 'ordering' => 4],
            ['name' => 'marketplace::phrase.category_services', 'name_url' => 'services', 'ordering' => 5],
            ['name' => 'marketplace::phrase.category_stuff', 'name_url' => 'stuff', 'ordering' => 6],
            ['name' => 'marketplace::phrase.category_tickets', 'name_url' => 'tickets', 'ordering' => 7],
            ['name' => 'marketplace::phrase.category_vehicles', 'name_url' => 'vehicles', 'ordering' => 8],
        ];

        if ($this->repository->getModel()->newQuery()->exists()) {
            return;
        }
        foreach ($categories as $category) {
            $this->repository->getModel()->create($category);
        }
    }

    private function removeUnusedMenuItems(): void
    {
        $repository = resolve(MenuItemRepositoryInterface::class);

        $repository->deleteMenuItem([
            'menu'       => 'marketplace.sidebarMenu',
            'name'       => 'landing',
            'resolution' => 'mobile',
        ]);

        $repository->deleteMenuItem([
            'menu'       => 'marketplace.sidebarMenu',
            'name'       => 'add',
            'resolution' => 'mobile',
        ]);
    }

    protected function categoryRelationData(): void
    {
        if (!Schema::hasTable('marketplace_category_relations')) {
            return;
        }

        if ($this->repository->getRelationModel()->newQuery()->exists()) {
            return;
        }

        $this->repository->createCategoryRelation();
    }
}
