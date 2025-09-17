<?php

namespace MetaFox\Importer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Comment\Models\Comment;
use MetaFox\Like\Models\Like;
use MetaFox\Like\Models\LikeAgg;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateChunkingLikeComment extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected array $photoGroupIds = [])
    {
        parent::__construct();
    }

    public function handle(): void
    {
        if (!count($this->photoGroupIds)) {
            return;
        }

        $photoGroups = PhotoGroup::query()
            ->whereIn('id', $this->photoGroupIds)
            ->get();

        if (!$photoGroups->count()) {
            return;
        }

        foreach ($photoGroups as $photoGroup) {
            $photoGroupItems = $photoGroup
                ->hasMany(PhotoGroupItem::class, 'group_id', 'id')
                ->orderBy('item_id')
                ->get();

            if (!$photoGroupItems->count()) {
                continue;
            }

            $photoItems = $photoGroupItems
                ->map->only(['item_id', 'item_type'])
                ->toArray();

            $firstPhoto = $photoItems[0] ?? null;

            if (!$firstPhoto) {
                continue;
            }

            $this->updateModelData($photoGroup, $firstPhoto);
        }
    }

    private function updateModelData(PhotoGroup $photoGroup, array $photo): void
    {
        $modelClasses = [Like::class, LikeAgg::class, Comment::class];

        foreach ($modelClasses as $modelClass) {
            $modelClass::query()
                ->where([
                    'item_id'   => $photo['item_id'],
                    'item_type' => $photo['item_type'],
                ])->update([
                    'item_id'   => $photoGroup->entityId(),
                    'item_type' => $photoGroup->entityType(),
                ]);
        }
    }
}
