<?php

namespace Foxexpert\Sevent\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Foxexpert\Sevent\Models\Category;
use Foxexpert\Sevent\Models\CategoryRelation;
use Foxexpert\Sevent\Models\CategoryData;
use Foxexpert\Sevent\Models\PrivacyStream;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Models\SeventText;
use Metafox\Search\Models\Search;
use Foxexpert\Sevent\Repositories\Eloquent\CategoryRepository;
use MetaFox\Platform\UserRole;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\StaticPage\Models\StaticPage;
use MetaFox\StaticPage\Models\StaticPageContent;

/**
 * Class PackageSeeder.
 * @codeCoverageIgnore
 * @ignore
 */
class PackageSeeder extends Seeder
{
    private CategoryRepository $categoryRepository;
    protected $_categories;

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

        $eventCategories = [
            [
                'name'     => 'Conferences',
                'name_url' => 'conferences',
                'ordering' => 0,
            ],
            [
                'name'     => 'Workshops & Seminars',
                'name_url' => 'workshops-and-seminars',
                'ordering' => 1,
            ],
            [
                'name'     => 'Concerts & Music Festivals',
                'name_url' => 'concerts-and-music-festivals',
                'ordering' => 2,
            ],
            [
                'name'     => 'Sports & Fitness',
                'name_url' => 'sports-and-fitness',
                'ordering' => 3,
            ],
            [
                'name'     => 'Art Exhibitions & Shows',
                'name_url' => 'art-exhibitions-and-shows',
                'ordering' => 4,
            ],
            [
                'name'     => 'Film & Theatre',
                'name_url' => 'film-and-theatre',
                'ordering' => 5,
            ],
            [
                'name'     => 'Food & Drink',
                'name_url' => 'food-and-drink',
                'ordering' => 6,
            ],
            [
                'name'     => 'Technology & Innovation',
                'name_url' => 'technology-and-innovation',
                'ordering' => 7,
            ],
            [
                'name'     => 'Networking & Social Events',
                'name_url' => 'networking-and-social-events',
                'ordering' => 8,
            ],
            [
                'name'     => 'Charity & Fundraisers',
                'name_url' => 'charity-and-fundraisers',
                'ordering' => 9,
            ],
            [
                'name'     => 'Education & Learning',
                'name_url' => 'education-and-learning',
                'ordering' => 10,
            ],
            [
                'name'     => 'Travel & Tourism',
                'name_url' => 'travel-and-tourism',
                'ordering' => 11,
            ],
            [
                'name'     => 'Health & Wellness',
                'name_url' => 'health-and-wellness',
                'ordering' => 12,
            ],
        ];        
        
        Category::query()->insert($eventCategories);

    }

    protected function categoryRelationData(): void
    {
        if (!Schema::hasTable('sevent_category_relations')) {
            return;
        }

        if ($this->categoryRepository->getRelationModel()->newQuery()->exists()) {
            return;
        }

        $this->categoryRepository->createTopLevelCategoryRelation();
        $this->categoryRepository->createCategoryRelation();
    }
}
