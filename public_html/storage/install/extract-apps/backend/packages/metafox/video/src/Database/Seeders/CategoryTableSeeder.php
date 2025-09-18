<?php

namespace MetaFox\Video\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use MetaFox\Video\Models\Category;
use MetaFox\Video\Repositories\Eloquent\CategoryRepository;

class CategoryTableSeeder extends Seeder
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
    public function run(): void
    {
        $this->categoryData();
        $this->categoryRelationData();
    }

    protected function categoryData(): void
    {
        if (Category::query()->exists()) {
            return;
        }

        $categories = [
            ['name' => 'video::phrase.category_gaming', 'name_url' => 'gaming', 'ordering' => 1],
            ['name' => 'video::phrase.category_film_entertainment', 'name_url' => 'film-&-entertainment', 'ordering' => 2],
            ['name' => 'video::phrase.category_comedy', 'name_url' => 'comedy', 'ordering' => 3],
            ['name' => 'video::phrase.category_music', 'name_url' => 'music', 'ordering' => 4],
        ];

        foreach ($categories as $category) {
            Category::query()->Create($category);
        }
    }

    protected function categoryRelationData(): void
    {
        if (!Schema::hasTable('video_category_relations')) {
            return;
        }

        if ($this->categoryRepository->getRelationModel()->newQuery()->exists()) {
            return;
        }

        $this->categoryRepository->createCategoryRelation();
    }
}
