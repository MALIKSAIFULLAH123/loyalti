<?php

namespace MetaFox\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use MetaFox\Blog\Models\Category;
use MetaFox\Blog\Repositories\Eloquent\CategoryRepository;

/**
 * Class PackageSeeder.
 *
 * @codeCoverageIgnore
 * @ignore
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
                'name'     => 'blog::phrase.category_business',
                'name_url' => 'business',
                'ordering' => 0,
            ],
            [
                'name'     => 'blog::phrase.category_education',
                'name_url' => 'education',
                'ordering' => 1,
            ],
            [
                'name'     => 'blog::phrase.category_entertainment',
                'name_url' => 'entertainment',
                'ordering' => 2,
            ],
            [
                'name'     => 'blog::phrase.category_family_home',
                'name_url' => 'family-&-home',
                'ordering' => 3,
            ],
            [
                'name'     => 'blog::phrase.category_health',
                'name_url' => 'health',
                'ordering' => 4,
            ],
            [
                'name'     => 'blog::phrase.category_recreation',
                'name_url' => 'recreation',
                'ordering' => 5,
            ],
            [
                'name'     => 'blog::phrase.category_shopping',
                'name_url' => 'shopping',
                'ordering' => 6,
            ],
            [
                'name'     => 'blog::phrase.category_society',
                'name_url' => 'society',
                'ordering' => 7,
            ],
            [
                'name'     => 'blog::phrase.category_sports',
                'name_url' => 'sports',
                'ordering' => 8,
            ],
            [
                'name'     => 'blog::phrase.category_technology',
                'name_url' => 'technology',
                'ordering' => 9,
            ],
        ];

        Category::query()->insert($categories);

    }

    protected function categoryRelationData(): void
    {
        if (!Schema::hasTable('blog_category_relations')) {
            return;
        }

        if ($this->categoryRepository->getRelationModel()->newQuery()->exists()) {
            return;
        }

        $this->categoryRepository->createCategoryRelation();
    }
}
