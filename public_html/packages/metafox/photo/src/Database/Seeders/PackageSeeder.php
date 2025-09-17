<?php

namespace MetaFox\Photo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use MetaFox\Photo\Models\Category;
use MetaFox\Photo\Repositories\Eloquent\CategoryRepository;

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
        $categories = [
            [
                'name'     => 'photo::phrase.category_another',
                'ordering' => 0,
                'name_url' => 'another',
            ],
            [
                'name'     => 'photo::phrase.category_artisan_crafts',
                'ordering' => 1,
                'name_url' => 'artisan-crafts',
            ],
            [
                'name'     => 'photo::phrase.category_cartoons_comics',
                'ordering' => 2,
                'name_url' => 'cartoons-&-comics',
            ],
            [
                'name'     => 'photo::phrase.category_comedy',
                'ordering' => 3,
                'name_url' => 'comedy',
            ],
            [
                'name'     => 'photo::phrase.category_community_projects',
                'ordering' => 4,
                'name_url' => 'community-projects',
            ],
            [
                'name'     => 'photo::phrase.category_contests',
                'ordering' => 5,
                'name_url' => 'contests',
            ],
            [
                'name'     => 'photo::phrase.category_customization',
                'ordering' => 6,
                'name_url' => 'customization',
            ],
            [
                'name'     => 'photo::phrase.category_designs_interfaces',
                'ordering' => 7,
                'name_url' => 'designs-&-interfaces',
            ],
            [
                'name'     => 'photo::phrase.category_digital_art',
                'ordering' => 8,
                'name_url' => 'digital-art',
            ],
            [
                'name'     => 'photo::phrase.category_fan_art',
                'ordering' => 9,
                'name_url' => 'fan-art',
            ],
            [
                'name'     => 'photo::phrase.category_film_animation',
                'ordering' => 10,
                'name_url' => 'film-&-animation',
            ],
            [
                'name'     => 'photo::phrase.category_fractal_art',
                'ordering' => 11,
                'name_url' => 'fractal-art',
            ],
            [
                'name'     => 'photo::phrase.category_game_development_art',
                'ordering' => 12,
                'name_url' => 'game-development-art',
            ],
            [
                'name'     => 'photo::phrase.category_literature',
                'ordering' => 13,
                'name_url' => 'literature',
            ],
            [
                'name'     => 'photo::phrase.category_people',
                'ordering' => 14,
                'name_url' => 'people',
            ],
            [
                'name'     => 'photo::phrase.category_pets_animals',
                'ordering' => 15,
                'name_url' => 'pets-&-animals',
            ],
            [
                'name'     => 'photo::phrase.category_photography',
                'ordering' => 16,
                'name_url' => 'photography',
            ],
            [
                'name'     => 'photo::phrase.category_resources_stock_images',
                'ordering' => 17,
                'name_url' => 'resources-&-stock-images',
            ],
            [
                'name'     => 'photo::phrase.category_science_technology',
                'ordering' => 18,
                'name_url' => 'science-&-technology',
            ],
            [
                'name'     => 'photo::phrase.category_sports',
                'ordering' => 19,
                'name_url' => 'sports',
            ],
            [
                'name'     => 'photo::phrase.category_traditional_art',
                'ordering' => 20,
                'name_url' => 'traditional-art',
            ],
        ];

        if (Category::query()->exists()) {
            return;
        }

        Category::query()->insert($categories);
    }

    protected function categoryRelationData(): void
    {
        if (!Schema::hasTable('photo_category_relations')) {
            return;
        }

        if ($this->categoryRepository->getRelationModel()->newQuery()->exists()) {
            return;
        }

        $this->categoryRepository->createCategoryRelation();
    }
}
