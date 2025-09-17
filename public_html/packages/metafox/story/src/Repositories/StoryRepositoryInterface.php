<?php

namespace MetaFox\Story\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StorySet;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * @mixin BaseRepository
 * Interface StoryRepositoryInterface.
 * @method Story find($id, $columns = ['*'])
 * @method Story getModel()
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface StoryRepositoryInterface
{
    /**
     * @param User $context
     * @param int  $id
     *
     * @return Story
     */
    public function viewStory(User $context, int $id): Story;

    /**
     * @param User  $context
     * @param User  $owner
     * @param array $attributes
     *
     * @return Story
     */
    public function createStory(User $context, User $owner, array $attributes): Story;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     */
    public function deleteStory(User $context, int $id): bool;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Paginator
     */
    public function viewStoryArchives(User $context, array $attributes): Paginator;

    /**
     * @param User $context
     * @param int  $storyId
     *
     * @return bool
     */
    public function archive(User $context, int $storyId): bool;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Builder
     */
    public function getSubQuery(User $context, array $attributes): Builder;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Builder
     */
    public function getSubQueryPrivacy(User $context, array $attributes): Builder;

    /**
     * @param User     $context
     * @param StorySet $storySet
     *
     * @return Collection
     */
    public function getStories(User $context, StorySet $storySet): Collection;

    /**
     * @param string $assetId
     *
     * @return Story|null
     */
    public function getStoryByAssetId(string $assetId): ?Story;

    /**
     * @param string $assetId
     *
     * @return bool
     */
    public function deleteVideoByAssetId(string $assetId): bool;

    /**
     * @param int    $itemId
     * @param string $itemType
     *
     * @return Story|null
     */
    public function getStoryByItem(int $itemId, string $itemType): ?Story;

    /**
     * @param int   $storyId
     * @param array $attributes
     *
     * @return bool
     */
    public function doneProcessVideo(int $storyId, array $attributes): bool;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Story|null
     */
    public function getStoryArchiveByDate(User $context, array $attributes): ?Story;

    /**
     * @param Story $story
     * @return void
     */
    public function publishStories(Story $story): void;
}
