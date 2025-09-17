<?php

namespace MetaFox\BackgroundStatus\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use MetaFox\BackgroundStatus\Models\BgsBackground;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\Platform\PackageManager;

/**
 * Class PackageSeeder.
 * @codeCoverageIgnore
 * @ignore
 */
class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filename = base_path('packages/metafox/background-status/resources/collections.json');

        $collections = json_decode(mf_get_contents($filename), true);

        foreach ($collections as $value) {
            $exists = BgsCollection::query()->where([
                ['title', '=', $value['title']],
            ])->count();

            if ($exists) {
                continue;
            }

            /** @var BgsCollection $collection */
            $collection = BgsCollection::query()->updateOrCreate(['title' => $value['title']], [
                'title'            => $value['title'],
                'is_default'       => Arr::get($value, 'is_default', 1),
                'view_only'        => Arr::get($value, 'view_only', 1),
                'total_background' => count($value['items']),
            ]);

            $this->addBackgrounds($collection, $value['items']);
        }
    }

    /**
     * @param BgsCollection       $collection
     * @param array<string,mixed> $items
     */
    private function addBackgrounds(BgsCollection $collection, array $items): void
    {
        $chunks = [];

        $directory = base_path(PackageManager::getAssetPath('metafox/background-status'));

        $storage = app('storage');
        $assetId = 'asset';

        foreach ($items as $index => $item) {
            $localPath = $item['path'];
            $filename  = $directory . '/' . $localPath;

            $origin = $storage->putFileAs($assetId, 'assets/bgs', $filename, $localPath);

            foreach ($item['variants'] as $variant => $localVariantPath) {
                $storage->putFileAs($assetId, 'assets/bgs', $directory . '/' . $localVariantPath, $localVariantPath, [
                    'is_origin' => false,
                    'variant'   => $variant,
                    'origin_id' => $origin->id,
                ]);
            }

            $chunks[] = [
                'collection_id' => $collection->id,
                'image_path'    => $origin->path,
                'server_id'     => $assetId,
                'image_file_id' => $origin->id,
                'view_only'     => 1,
                'ordering'      => Arr::get($item, 'ordering', $index),
            ];
        }

        BgsBackground::query()->insert($chunks);

        /** @var BgsBackground $thumbBackground */
        $thumbBackground = $collection->backgrounds()->first();
        $collection->update([
            'main_background_id' => $thumbBackground->entityId(),
        ]);
    }
}
