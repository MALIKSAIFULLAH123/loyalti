<?php

namespace MetaFox\Activity\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Activity\Models\ActivityTagData;
use MetaFox\Activity\Models\Feed;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateChunkingFeedContent extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected string $modelClass, protected array $feedIds = [])
    {
        parent::__construct();
    }

    public function handle()
    {
        $feeds = Feed::query()
            ->whereIn('id', $this->feedIds)
            ->get();

        if (!$feeds->count()) {
            return;
        }

        $feedMaps = [];
        $itemIds  = [];
        $itemMaps = [];

        foreach ($feeds as $feed) {
            if (!$feed instanceof Feed) {
                continue;
            }

            $feedMaps[$feed->entityId()] = $feed;
            $itemIds[]                   = $feed->itemId();
        }

        $items = $this->modelClass::query()
            ->whereIn('id', $itemIds)
            ->get(['id', 'content'])
            ->toArray();

        array_map(function ($item) use (&$itemMaps) {
            $itemMaps[$item['id']] = $item['content'] ?? '';
        }, $items);

        $feedData    = [];
        $itemHashtag = [];
        $allHashtag  = [];

        foreach ($feedMaps as $feedValue) {
            $itemId = $feedValue->itemId();

            if (!isset($itemMaps[$itemId])) {
                continue;
            }

            $content = $itemMaps[$itemId] ?? '';

            $feedData[] = array_merge($feedValue->toArray(), [
                'content' => $itemMaps[$itemId] ?? '',
            ]);
            if ($content) {
                $hashtag                             = parse_output()->getHashtags($content);
                $itemHashtag[$feedValue->entityId()] = array_unique($hashtag);
                $allHashtag                          = array_merge($allHashtag, $hashtag);
            }
        }
        Feed::query()->upsert($feedData, ['id']);

        $allHashtag = array_unique($allHashtag);

        if (!count($allHashtag)) {
            return;
        }
        $existedHashTag = Tag::query()
            ->whereIn('text', $allHashtag)
            ->pluck('id', 'text')
            ->toArray();
        $insertData     = [];
        $checkDuplicate = [];
        foreach ($itemHashtag as $itemId => $hashtag) {
            $checkDuplicate[$itemId] = [];
            foreach ($hashtag as $value) {
                $tagId = Arr::get($existedHashTag, $value);
                if (!$tagId) {
                    // Tag doesn't exist
                    $tag = new Tag([
                        'text'    => $value,
                        'tag_url' => Str::slug($value),
                    ]);
                    $tag->saveQuietly();
                    $tagId = $tag->entityId();
                }
                $checkDuplicate[$itemId][] = $tagId;
                $insertData[]              = [
                    'item_id' => $itemId,
                    'tag_id'  => $tagId,
                ];
            }
        }
        $query = ActivityTagData::query();
        foreach ($checkDuplicate as $key => $item) {
            if (!count($item)) {
                continue;
            }
            $query->orWhere(function (Builder $whereQuery) use ($key, $item) {
                $whereQuery->where('item_id', $key)
                    ->whereIn('tag_id', $item);
            });
        }
        $query->delete();
        ActivityTagData::query()->insert($insertData);
    }
}
