<?php

namespace MetaFox\Event\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use MetaFox\Event\Models\Category;
use MetaFox\Event\Repositories\Eloquent\CategoryRepository;

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

        $id = 0;

        $categories = [
            [
                'name'     => 'event::phrase.category_arts',
                'name_url' => 'arts',
                'ordering' => ++$id,
            ],
            [
                'name'     => 'event::phrase.category_comedy',
                'name_url' => 'comedy',
                'ordering' => ++$id,
            ],
            [
                'name'     => 'event::phrase.category_movies',
                'name_url' => 'movies',
                'ordering' => ++$id,
            ],
            [
                'name'     => 'event::phrase.category_music',
                'name_url' => 'music',
                'ordering' => ++$id,
            ],
            [
                'name'     => 'event::phrase.category_other',
                'name_url' => 'other',
                'ordering' => ++$id,
            ],
            [
                'name'     => 'event::phrase.category_party',
                'name_url' => 'party',
                'ordering' => ++$id,
            ],
            [
                'name'     => 'event::phrase.category_sports',
                'name_url' => 'sports',
                'ordering' => ++$id,
            ],
            [
                'name'     => 'event::phrase.category_tv',
                'name_url' => 'tv',
                'ordering' => ++$id,
            ],
        ];
        Category::query()->insert($categories);
    }

    protected function categoryRelationData(): void
    {
        if (!Schema::hasTable('event_category_relations')) {
            return;
        }

        if ($this->categoryRepository->getRelationModel()->newQuery()->exists()) {
            return;
        }

        $this->categoryRepository->createCategoryRelation();
    }
}
