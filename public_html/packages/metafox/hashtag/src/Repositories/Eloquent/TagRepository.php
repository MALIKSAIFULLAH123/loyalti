<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Hashtag\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Hashtag\Models\TagData;
use MetaFox\Hashtag\Policies\HashtagPolicy;
use MetaFox\Hashtag\Repositories\TagRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

class TagRepository extends AbstractRepository implements TagRepositoryInterface
{
    public function model()
    {
        return Tag::class;
    }

    /**
     * @param string $tags
     *
     * @return int[]
     */
    public function getTagIdsForString(string $tags): array
    {
        return $this->getTagIds(explode(',', $tags));
    }

    /**
     * Get list of tag ids of tags string
     * Insert new tags if there are no associate tag_url.
     *
     * @param string[] $tags
     *
     * @return int[]
     */
    public function getTagIds(array $tags): array
    {
        $pairs = [];

        foreach ($tags as $text) {
            $text       = trim($text);
            $textLength = mb_strlen($text);
            $text       = Str::lower($text);

            if (empty($text)) {
                continue;
            }

            $tagUrl = Str::slug($text);
            if ($textLength !== mb_strlen($tagUrl)) {
                $tagUrl = urlencode($text);
            }

            $pairs[$tagUrl] = $text;
        }

        if (empty($pairs)) {
            return [];
        }

        /** @var string[] $founds */
        $founds = $this->findWhereIn('tag_url', array_keys($pairs))->pluck('text', 'tag_url')->toArray();

        foreach ($founds as $found => $existedText) {
            if ($existedText === Arr::get($pairs, $found)) {
                $pairs[$found] = false;
            }
        }

        foreach ($pairs as $tagUrl => $text) {
            if (!$text) {
                continue;
            }

            try {
                $this->create(['text' => $text, 'tag_url' => $tagUrl]);
            } catch (Exception $exception) {
                // @todo catch log?
            }
        }

        $result = $this->findWhereIn('tag_url', array_keys($pairs))->pluck('id');

        return $result->toArray();
    }

    /**
     * Get list of tag ids of tags string
     * Insert new tags if there are no associate tag_url.
     *
     * @param string $tag
     *
     * @return int
     */
    public function getTagId(string $tag): int
    {
        $tagLength = mb_strlen($tag);
        $url       = Str::slug($tag);
        $found     = $this->findByField('tag_url', $tagLength === mb_strlen($url) ? $url : urlencode(Str::lower($tag)))->first();

        return $found->id ?? 0;
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Collection<Model>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function searchHashtags(User $context, array $attributes): Collection
    {
        $limit = $attributes['limit'] ?? 10;
        $q     = $attributes['q'];

        $query = $this->getModel()->newQuery();

        if ($q) {
            $query->where('tag_url', 'like', Str::lower($q) . '%');
        }

        return $query
            ->orderByDesc('total_item')
            ->limit($limit)
            ->get();
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewHashtags(User $context, array $attributes): Paginator
    {
        policy_authorize(HashtagPolicy::class, 'viewAny', $context);

        return $this->getModel()->newModelInstance()
            ->orderBy('total_item', 'desc')
            ->orderBy('id', 'desc')
            ->simplePaginate($attributes['limit']);
    }

    public function suggestionHashtags(User $context, array $attributes): array
    {
        if (!policy_check(HashtagPolicy::class, 'viewAny', $context)) {
            return [];
        }

        $search = Str::lower($attributes['q']);

        $limit  = $attributes['limit'];

        $query = $this->getModel()->newQuery()
            ->orderBy('total_item', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit);

        if ($search != '') {
            $query->where('text', $this->likeOperator(), $search . '%');
        }

        return $query->get()->collect()->map(function (Tag $tag) {
            return $tag->text;
        })->toArray();
    }

    /**
     * @throws AuthorizationException
     */
    public function createHashtag(User $context, array $attributes): Tag
    {
        policy_authorize(HashtagPolicy::class, 'create', $context);

        $data = $this->cleanData($attributes);

        /** @var Tag $hashtag */
        $hashtag = $this->getModel()->newModelInstance()->where('text', $attributes['text'])->first();

        if ($hashtag != null) {
            return $hashtag;
        }

        /** @var Tag $hashtag */
        $hashtag = $this->getModel()->newModelInstance();
        $hashtag->fill($data);
        $hashtag->save();
        $hashtag->refresh();

        return $hashtag;
    }

    /**
     * @param array<string, mixed> $attributes
     * @inheritdoc
     */
    public function cleanData(array $attributes): array
    {
        $parser = parse_input();

        if (isset($attributes['text'])) {
            $attributes['text'] = Str::lower($parser->clean($attributes['text']));
        }

        if (isset($attributes['tag_url'])) {
            $attributes['tag_url'] = $parser->clean($attributes['tag_url']);
        }

        return $attributes;
    }

    /**
     * @throws AuthorizationException
     */
    public function updateTotalItem(User $context, int $id): bool
    {
        /** @var Tag $hashtag */
        $hashtag = $this->find($id);

        policy_authorize(HashtagPolicy::class, 'update', $context);

        $totalItem = TagData::query()->where('tag_id', $hashtag->entityId())->count('id');

        $hashtag->fill(['total_item' => $totalItem]);

        return $hashtag->save();
    }
}
