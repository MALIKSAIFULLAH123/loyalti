<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Hashtag\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface TagRepositoryInterface.
 * @mixin BaseRepository
 */
interface TagRepositoryInterface
{
    /**
     * @param string $tags
     *
     * @return array<int, mixed>
     */
    public function getTagIdsForString(string $tags): array;

    /**
     * Get list of tag ids of tags string
     * Insert new tags if there are no associate tag_url.
     *
     * @param string[] $tags
     *
     * @return int[]
     */
    public function getTagIds(array $tags): array;

    /**
     * @param string $tag
     *
     * @return int
     */
    public function getTagId(string $tag): int;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Collection<Model>
     */
    public function searchHashtags(User $context, array $attributes): Collection;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     */
    public function viewHashtags(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Tag
     */
    public function createHashtag(User $context, array $attributes): Tag;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     */
    public function updateTotalItem(User $context, int $id): bool;

    /**
     * Clean input data before fill in.
     *
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public function cleanData(array $attributes): array;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return array<int, mixed>
     */
    public function suggestionHashtags(User $context, array $attributes): array;
}
