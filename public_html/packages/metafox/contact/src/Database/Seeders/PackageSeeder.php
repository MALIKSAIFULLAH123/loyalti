<?php

namespace MetaFox\Contact\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use MetaFox\Contact\Models\Category;
use MetaFox\Contact\Repositories\Eloquent\CategoryRepository;

/**
 * stub: packages/database/seeder-database.stub.
 */

/**
 * Class PackageSeeder.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSeeder extends Seeder
{
    private CategoryRepository $categoryRepository;

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
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
    }

    private function categories()
    {
        if (Category::query()->exists()) {
            return;
        }

        $categories = [
            [
                'name'     => 'contact::phrase.category_sales',
                'name_url' => 'sales',
                'ordering' => 0,
            ],
            [
                'name'     => 'contact::phrase.category_support',
                'name_url' => 'support',
                'ordering' => 1,
            ],
            [
                'name'     => 'contact::phrase.category_suggestions',
                'name_url' => 'suggestions',
                'ordering' => 2,
            ],
        ];

        Category::query()->insert($categories);
    }

    protected function categoryRelationData(): void
    {
        if (!Schema::hasTable('contact_category_relations')) {
            return;
        }

        if ($this->categoryRepository->getRelationModel()->newQuery()->exists()) {
            return;
        }

        $this->categoryRepository->createCategoryRelation();
    }
}
