<?php

namespace MetaFox\Music\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use MetaFox\Music\Models\Genre;
use MetaFox\Music\Repositories\Eloquent\GenreRepository;

/**
 * Class PackageSeeder.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSeeder extends Seeder
{
    /**
     * @var GenreRepository
     */
    private GenreRepository $repository;

    /**
     * PrivacyDatabaseSeeder constructor.
     *
     * @param GenreRepository $repository
     */
    public function __construct(GenreRepository $repository)
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
        $this->genres();
        $this->categoryRelationData();
        $this->createStorageDisk();
    }

    private function createStorageDisk(): void
    {
        app('storage')->tap('music', [
            'label'     => 'Music',
            'is_system' => true,
        ]);
    }

    private function genres()
    {
        $genres = [
            [
                'name'     => 'music::phrase.category_alternative',
                'name_url' => 'alternative',
                'ordering' => 0,
            ],
            [
                'name'     => 'music::phrase.category_classic_rock',
                'name_url' => 'classic-rock',
                'ordering' => 1,
            ],
            [
                'name'     => 'music::phrase.category_country',
                'name_url' => 'country',
                'ordering' => 2,
            ],
            [
                'name'     => 'music::phrase.category_electronica',
                'name_url' => 'electronica',
                'ordering' => 3,
            ],
            [
                'name'     => 'music::phrase.category_folk',
                'name_url' => 'folk',
                'ordering' => 4,
            ],
            [
                'name'     => 'music::phrase.category_hardcore',
                'name_url' => 'hardcore',
                'ordering' => 5,
            ],
            [
                'name'     => 'music::phrase.category_hip_hop',
                'name_url' => 'hip-hop',
                'ordering' => 6,
            ],
            [
                'name'     => 'music::phrase.category_house',
                'name_url' => 'house',
                'ordering' => 7,
            ],
            [
                'name'     => 'music::phrase.category_indie',
                'name_url' => 'indie',
                'ordering' => 8,
            ],
            [
                'name'     => 'music::phrase.category_jazz',
                'name_url' => 'jazz',
                'ordering' => 9,
            ],
        ];

        $musicGenres = Genre::query()->exists();
        if ($musicGenres) {
            return;
        }

        Genre::query()->insert($genres);
    }


    protected function categoryRelationData(): void
    {
        if (!Schema::hasTable('music_genre_relations')) {
            return;
        }

        if ($this->repository->getRelationModel()->newQuery()->exists()) {
            return;
        }

        $this->repository->createCategoryRelation();
    }
}
