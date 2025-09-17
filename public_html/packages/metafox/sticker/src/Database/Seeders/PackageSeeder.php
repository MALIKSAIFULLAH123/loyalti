<?php

namespace MetaFox\Sticker\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use MetaFox\Platform\PackageManager;
use MetaFox\Sticker\Jobs\ProcessStickerThumbnailsJob;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet;

/**
 * Class PackageSeeder.
 * @ignore
 * @codeCoverageIgnore
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
        $this->addStickerSets();
    }

    private function addStickerSets(): void
    {
        if (StickerSet::query()->exists()) {
            return;
        }

        $filename = base_path('packages/metafox/sticker/resources/stickers.json');

        $stickerSets = json_decode(mf_get_contents($filename), true);

        foreach ($stickerSets as $index => $value) {
            $exists = StickerSet::query()->where([['title', '=', $value['title']]])->count();

            if ($exists) {
                continue;
            }

            /** @var StickerSet $stickerSet */
            $stickerSet = StickerSet::query()->create([
                'title'      => $value['title'],
                'is_default' => Arr::get($value, 'is_default', 1),
                'view_only'  => Arr::get($value, 'view_only', 1),
                'ordering'   => Arr::get($value, 'ordering', $index),
            ]);

            $this->addStickers($stickerSet, $value['dir']);
        }

        ProcessStickerThumbnailsJob::dispatchSync();
    }

    /**
     * @param StickerSet $stickerSet
     * @param string     $dir
     */
    private function addStickers(StickerSet $stickerSet, string $dir): void
    {
        $stickerData = [];
        $files = app('files')->allFiles(base_path(implode(DIRECTORY_SEPARATOR, [PackageManager::getAssetPath('metafox/sticker'), $dir])));

        foreach ($files as $index => $file) {
            $file = app('storage')
                ->putFileAs('asset', 'assets/sticker/' . $dir, $file->getPathname(), $file->getBasename(), ['user_id' => '1', 'user_type' => 'user']);

            $file->save();

            $stickerData[] = [
                'set_id'        => $stickerSet->id,
                'image_path'    => $file->path,
                'server_id'     => $file->storage_id,
                'image_file_id' => $file->id,
                'view_only'     => Arr::get($file, 'view_only', 1),
                'ordering'      => Arr::get($file, 'ordering', $index),
            ];
        }

        Sticker::query()->insert($stickerData);

        /** @var Sticker $thumbSticker */
        $thumbSticker = $stickerSet->stickers()->first();
        $stickerSet->update([
            'total_sticker' => count($stickerData),
            'thumbnail_id'  => $thumbSticker->id,
        ]);
    }
}
