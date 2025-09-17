<?php

namespace MetaFox\Photo\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class UpdatePhotoStatisticJob.
 */
class UpdatePhotoStatisticJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $data = [];

        $data = $this->getDataFromTable('likes', 'total_like', $data);
        $data = $this->getDataFromTable('comments', 'total_comment', $data);

        Photo::withoutEvents(function () use ($data) {
            foreach ($data as $id => $value) {
                Photo::query()
                    ->where('id', $id)
                    ->update($value);
            }
        });

        $this->handlePhotoGroupHasOneItem();
    }

    private function handlePhotoGroupHasOneItem(): void
    {
        $photoGroups = PhotoGroup::query()
            ->where('total_item', 1)
            ->cursor();

        foreach ($photoGroups as $photoGroup) {
            if (!$photoGroup instanceof PhotoGroup) {
                continue;
            }

            $item = $photoGroup->items()?->first()?->detail;

            if (!$item instanceof Photo) {
                continue;
            }

            $item->updateQuietly([
                'total_like'    => $photoGroup->total_like,
                'total_comment' => $photoGroup->total_comment,
            ]);
        }
    }

    private function getDataFromTable(string $tableName, string $countAlias, array $data): array
    {
        if (!Schema::hasTable($tableName)) {
            return $data;
        }

        $items = DB::table($tableName)
            ->selectRaw("count(*) as $countAlias, item_id")
            ->where('item_type', 'photo')
            ->groupBy(['item_type', 'item_id'])
            ->get();

        foreach ($items as $item) {
            if (!isset($data[$item->item_id])) {
                $data[$item->item_id] = [];
            }

            $data[$item->item_id][$countAlias] = $item->$countAlias;
        }

        return $data;
    }
}
