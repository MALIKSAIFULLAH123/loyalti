<?php

namespace MetaFox\Story\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use MetaFox\Platform\PackageManager;
use MetaFox\Story\Models\BackgroundSet;
use MetaFox\Story\Models\StoryBackground;

/**
 * Class StoryBackgroundTableSeeder.
 * @codeCoverageIgnore
 * @ignore
 */
class StoryBackgroundTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->addBackgroundSets();
    }

    private function addBackgroundSets(): void
    {
        if (BackgroundSet::query()->exists()) {
            return;
        }

        $filename = base_path('packages/metafox/story/resources/collections.json');

        $collections = json_decode(mf_get_contents($filename), true);

        foreach ($collections as $value) {
            $exists = BackgroundSet::query()->where([
                ['title', '=', $value['title']],
            ])->count();

            if ($exists) {
                continue;
            }

            /** @var BackgroundSet $collection */
            $collection = BackgroundSet::query()->updateOrCreate(['title' => $value['title']], [
                'title'            => $value['title'],
                'is_default'       => Arr::get($value, 'is_default', 1),
                'view_only'        => Arr::get($value, 'view_only', 1),
                'total_background' => count($value['items']),
            ]);

            $this->addBackgrounds($collection, $value['items']);
        }
    }

    /**
     * @param BackgroundSet       $collection
     * @param array<string,mixed> $items
     */
    private function addBackgrounds(BackgroundSet $collection, array $items): void
    {
        $chunks = [];

        $directory = base_path(PackageManager::getAssetPath('metafox/story'));

        $storage = app('storage');
        $assetId = 'asset';

        foreach ($items as $index => $item) {
            $localPath = $item['path'];
            $filename = $directory . '/' . $localPath;

            $origin = $storage->putFileAs($assetId, 'assets/story', $filename, $localPath);

            foreach ($item['variants'] as $variant => $localVariantPath) {
                $storage->putFileAs($assetId, 'assets/story', $directory . '/' . $localVariantPath, $localVariantPath, [
                    'is_origin' => false,
                    'variant'   => $variant,
                    'origin_id' => $origin->id,
                ]);
            }

            $chunks[] = [
                'set_id'        => $collection->id,
                'image_path'    => $origin->path,
                'server_id'     => $assetId,
                'image_file_id' => $origin->id,
                'view_only'     => 1,
                'ordering'      => Arr::get($item, 'ordering', $index),
            ];
        }

        StoryBackground::query()->insert($chunks);

        /** @var StoryBackground $thumbBackground */
        $thumbBackground = $collection->backgrounds()->first();
        $collection->update([
            'main_background_id' => $thumbBackground->entityId(),
        ]);
    }
}
