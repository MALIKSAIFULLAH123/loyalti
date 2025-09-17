<?php

namespace MetaFox\Story\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Story\Jobs\VideoStoryProcessingJob;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StorySet;
use MetaFox\Story\Notifications\StoryDoneProcessingNotification;
use MetaFox\Story\Policies\StoryPolicy;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Repositories\StorySetRepositoryInterface;
use MetaFox\Story\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Story\Support\Facades\StoryFacades;
use MetaFox\Story\Support\StorySupport;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\Browse\Scopes\User\BlockedScope;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * @method Story find($id, $columns = ['*'])
 * @method Story getModel()
 */
class StoryRepository extends AbstractRepository implements StoryRepositoryInterface
{
    use UserMorphTrait;

    protected function userRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }

    protected function storySetRepository(): StorySetRepositoryInterface
    {
        return resolve(StorySetRepositoryInterface::class);
    }

    /**
     * @return string
     */
    public function model(): string
    {
        return Story::class;
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Story
     * @throws AuthorizationException
     */
    public function viewStory(User $context, int $id): Story
    {
        $story = $this->find($id);

        policy_authorize(StoryPolicy::class, 'view', $context, $story);

        return $story;
    }

    /**
     * @param User  $context
     * @param User  $owner
     * @param array $attributes
     *
     * @return Story
     * @throws \Exception
     */
    public function createStory(User $context, User $owner, array $attributes): Story
    {
        policy_authorize(StoryPolicy::class, 'create', $context, $owner);

        $lifespan = Arr::get($attributes, 'lifespan', StoryFacades::getLifespanDefault());
        Arr::forget($attributes, 'lifespan');

        $type       = Arr::get($attributes, 'type');
        $attributes = $this->handleFile($attributes);

        $storySet = $this->storySetRepository()->createStorySet($context, [
            'expired_at' => StoryFacades::setExpired($lifespan),
        ]);

        $extra = Arr::get($attributes, 'extra');

        $attributes = array_merge($attributes, [
            'set_id'     => $storySet->entityId(),
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
            'expired_at' => StoryFacades::setExpired($lifespan),
            'extra'      => $extra,
            'is_publish' => StorySupport::STORY_TYPE_LIVE_VIDEO == $type
                ? 1
                : $this->getStoryInProcess($context)->isEmpty(),
        ]);

        $story = $this->getModel()->fill($attributes);

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $story->setPrivacyListAttribute($attributes['list']);
        }

        $story->save();

        if ($type == StorySupport::STORY_TYPE_VIDEO) {
            $tempFile = Arr::get($attributes, 'temp_file');
            $tempFile = upload()->getFile($tempFile);
            Arr::set($attributes, 'story_id', $story->entityId());

            VideoStoryProcessingJob::dispatch($tempFile, $attributes);
        }

        return $story->refresh();
    }

    protected function handleFile(array $attributes): array
    {
        $type     = Arr::get($attributes, 'type');
        $tempFile = Arr::get($attributes, 'temp_file');
        Arr::set($attributes, 'in_process', StorySupport::STATUS_VIDEO_READY);

        if (!$tempFile) {
            if ($type == StorySupport::STORY_TYPE_TEXT) {
                return $this->handleThumbnailFile($attributes);
            }

            return $attributes;
        }

        $tempFile = upload()->getFile($tempFile);

        if (in_array($type, [StorySupport::STORY_TYPE_PHOTO, StorySupport::STORY_TYPE_TEXT])) {
            Arr::set($attributes, 'image_file_id', $tempFile->id);
        }

        if ($type == StorySupport::STORY_TYPE_VIDEO) {
            Arr::set($attributes, 'in_process', StorySupport::STATUS_VIDEO_PROCESS);
        }

        $attributes = $this->handleThumbnailFile($attributes);
        // Delete temp file after done
        upload()->rollUp($attributes['temp_file']);

        return $attributes;
    }

    protected function handleThumbnailFile(array $attributes): array
    {
        if (!Arr::has($attributes, 'thumb_file')) {
            return $attributes;
        }

        $thumbFile = Arr::get($attributes, 'thumb_file');

        if (!is_array($thumbFile)) {
            return $attributes;
        }

        $thumbFile     = Arr::get($thumbFile, 'temp_file');
        $tempThumbFile = upload()->getFile($thumbFile);

        Arr::set($attributes, 'thumbnail_file_id', $tempThumbFile->id);
        Arr::forget($attributes, 'thumb_file');

        upload()->rollUp($thumbFile);

        return $attributes;
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteStory(User $context, int $id): bool
    {
        $story = $this->find($id);

        policy_authorize(StoryPolicy::class, 'delete', $context, $story);

        return $story->delete();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewStoryArchives(User $context, array $attributes): Paginator
    {
        $limit    = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $userId   = Arr::get($attributes, 'user_id', 0);
        $fromDate = Arr::get($attributes, 'from_date');
        $toDate   = Arr::get($attributes, 'to_date');

        $user = $context;

        if ($userId > 0) {
            $user = UserEntity::getById($userId)->detail;
        }

        policy_authorize(StoryPolicy::class, 'viewArchive', $context, $user);

        $table = $this->getModel()->getTable();
        $query = $this->getModel()->newQuery();

        $query->where('user_id', $context->entityId())
            ->where('is_archive', MetaFoxConstant::IS_ACTIVE)
            ->whereIn('type', StoryFacades::allowTypeViewStory());

        if ($fromDate) {
            $query->where(sprintf('%s.created_at', $table), '>=', $fromDate);
        }

        if ($toDate) {
            $query->where(sprintf('%s.created_at', $table), '<=', $toDate);
        }

        $page = $this->resolvePageForDetailView($context, $attributes);

        $query->orderByRaw('date(created_at) desc');
        $query->orderBy('created_at');

        return $query->paginate($limit, ["$table.*"], 'page', $page);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function archive(User $context, int $storyId): bool
    {
        $story = $this->find($storyId);

        policy_authorize(StoryPolicy::class, 'archive', $context, $story);

        $story->update([
            'is_archive' => MetaFoxConstant::IS_ACTIVE,
            'expired_at' => Carbon::now()->subMinute()->timestamp,
        ]);

        app('events')->dispatch('notification.delete_notification_to_follower', [$story], true);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSubQuery(User $context, array $attributes): Builder
    {
        $contextId    = $context->entityId();
        $nowTimestamp = Carbon::now()->subMinute()->timestamp;

        $query = $this->getModel()->newQuery()
            ->select(
                'stories.user_id',
                DB::raw('count(stories.id) as total_stories'),
                DB::raw('count(story_views.id) as total_view')
            )
            ->leftJoin('story_views', function (JoinClause $joinClause) use ($contextId) {
                $joinClause->on('stories.id', '=', 'story_views.story_id');
                $joinClause->where('story_views.user_id', $contextId);
            })
            ->leftJoin('story_privacy_streams as stream', function (JoinClause $joinClause) {
                $joinClause->on('stories.id', '=', 'stream.item_id');
            })
            ->leftJoin('core_privacy_members as member', function (JoinClause $joinClause) {
                $joinClause->on('stream.privacy_id', '=', 'member.privacy_id');
            })
            ->whereIn('stories.type', StoryFacades::allowTypeViewStory())
            ->where('stories.expired_at', '>=', $nowTimestamp)
            ->where('stories.is_archive', MetaFoxConstant::IS_INACTIVE)
            ->where('member.user_id', $contextId);

        $query = $this->getBuilderStoryInProcess($context, $query);

        return $query->groupBy('stories.user_id');
    }

    /**
     * @param User     $context
     * @param StorySet $storySet
     *
     * @return Collection
     */
    public function getStories(User $context, StorySet $storySet): Collection
    {
        $table        = $this->getModel()->getTable();
        $nowTimestamp = Carbon::now()->subMinute()->timestamp;
        $query        = $this->getModel()->newQuery()
            ->select("$table.*")
            ->where('set_id', $storySet->entityId())
            ->whereIn("$table.type", StoryFacades::allowTypeViewStory())
            ->where('stories.expired_at', '>=', $nowTimestamp)
            ->where('stories.is_archive', MetaFoxConstant::IS_INACTIVE)
            ->orderBy('stories.created_at');

        $query = $this->getBuilderStoryInProcess($context, $query);

        $privacyScope = new PrivacyScope();
        $privacyScope->setUserId($context->entityId());
        $query->addScope($privacyScope);

        return $query->get();
    }

    /**
     * @inheritDoc
     */
    public function getSubQueryPrivacy(User $context, array $attributes): Builder
    {
        $contextId = $context->entityId();
        $query     = $this->getModel()->newQuery()
            ->select('stories.set_id')
            ->leftJoin('story_privacy_streams as stream', function (JoinClause $joinClause) {
                $joinClause->on('stories.id', '=', 'stream.item_id');
            })
            ->whereIn('stories.type', StoryFacades::allowTypeViewStory())
            ->leftJoin('core_privacy_members as member', function (JoinClause $joinClause) {
                $joinClause->on('stream.privacy_id', '=', 'member.privacy_id');
            })->where('member.user_id', $contextId);

        $blockedScope = new BlockedScope();
        $blockedScope->setContextId($contextId)
            ->setTable('stories')
            ->setPrimaryKey('user_id');

        $query->addScope($blockedScope);

        return $this->getBuilderStoryInProcess($context, $query);
    }

    /**
     * @inheritDoc
     */
    public function getStoryByAssetId(string $assetId): ?Story
    {
        $story = $this->getModel()
            ->newModelQuery()
            ->where('asset_id', '=', $assetId)
            ->first();

        return $story instanceof Story ? $story : null;
    }

    /**
     * @param string $assetId
     *
     * @return bool
     */
    public function deleteVideoByAssetId(string $assetId): bool
    {
        $story = $this->getStoryByAssetId($assetId);

        if (!$story instanceof Story) {
            return true;
        }

        return (bool) $story->delete();
    }

    /**
     * @inheritDoc
     */
    public function getStoryByItem(int $itemId, string $itemType): ?Story
    {
        return $this->getModel()->newQuery()
            ->where([
                'item_id'   => $itemId,
                'item_type' => $itemType,
            ])->first();
    }

    /**
     * @param int                  $storyId
     * @param array<string, mixed> $attributes
     *
     * @return bool
     * @throws \Exception
     */
    public function doneProcessVideo(int $storyId, array $attributes): bool
    {
        $story = $this->withUserMorphTypeActiveScope()->with(['user', 'owner'])->find($storyId);

        if (!$story instanceof Story) {
            return false;
        }

        // If video is done processing, no longer need this action
        if ($story->is_ready) {
            $this->publishStories($story);

            return true;
        }

        $story->fill($attributes);

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $story->setPrivacyListAttribute($attributes['privacy_list']);
        }

        $story->save();
        $this->publishStories($story);

        // Notify creator that their video is ready
        Notification::send($story->user, new StoryDoneProcessingNotification($story));

        return true;
    }

    /**
     * @param User    $context
     * @param Builder $builder
     *
     * @return Builder
     */
    protected function getBuilderStoryInProcess(User $context, Builder $builder): Builder
    {
        if (version_compare(MetaFox::getApiVersion(), 'v1.10', '<') && MetaFox::isMobile()) {
            return $builder->where('stories.in_process', StorySupport::STATUS_VIDEO_READY);
        }

        $contextId = $context->entityId();
        $builder->where(function (Builder $builder) use ($contextId) {
            $builder->orWhere(function (Builder $builder) use ($contextId) {
                $builder->where('stories.user_id', $contextId)
                    ->whereIn('stories.in_process', [StorySupport::STATUS_VIDEO_PROCESS, StorySupport::STATUS_VIDEO_READY]);
            });
            $builder->orWhere(function (Builder $builder) use ($contextId) {
                $builder->whereNot('stories.user_id', $contextId)
                    ->where('stories.in_process', StorySupport::STATUS_VIDEO_READY)
                    ->where('stories.is_publish', true);
            });
        });

        return $builder;
    }

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Story|null
     */
    public function getStoryArchiveByDate(User $context, array $attributes): ?Story
    {
        $date     = Arr::get($attributes, 'date');
        $operator = Arr::get($attributes, 'operator', '=');
        $sortType = Arr::get($attributes, 'sort_type', Browse::SORT_TYPE_DESC);

        if (!$date) {
            return null;
        }

        $query = $this->getModel()->newQuery()->where('user_id', $context->entityId())
            ->where('is_archive', MetaFoxConstant::IS_ACTIVE)
            ->whereIn('type', StoryFacades::allowTypeViewStory())
            ->whereDate('created_at', $operator, $date)
            ->orderBy('created_at', $sortType);

        return $query->first();
    }

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return int
     */
    protected function resolvePageForDetailView(User $context, array $attributes = []): int
    {
        $storyId = Arr::get($attributes, 'story_id');

        if (!is_numeric($storyId)) {
            return Arr::get($attributes, 'page', 1);
        }

        $story = $this->getModel()->newQuery()->findOrFail($storyId);

        $toDate   = Carbon::make($story->created_at);
        $fromDate = Arr::get($attributes, 'from_date');

        if (!$fromDate || !$toDate) {
            return Arr::get($attributes, 'page', 1);
        }

        $table = $this->getModel()->getTable();
        $query = $this->getModel()->newQuery()
            ->where('user_id', $context->entityId())
            ->where('is_archive', MetaFoxConstant::IS_ACTIVE)
            ->whereIn('type', StoryFacades::allowTypeViewStory());

        $query->whereBetween("$table.created_at", [$fromDate, $toDate]);

        $query->orderByRaw('date(created_at) desc');
        $query->orderBy('created_at');

        $total = $query->count();
        $limit = (int) Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        if (!$total || $total <= $limit) {
            return 1;
        }

        $page    = $total / $limit;
        $surplus = $total % $limit;

        if (0 === $surplus) {
            return $page;
        }

        return $page + 1;
    }

    protected function getStoryInProcess(User $context): Collection
    {
        $table        = $this->getModel()->getTable();
        $nowTimestamp = Carbon::now()->subMinute()->timestamp;

        return $this->getModel()->newQuery()
            ->where("$table.user_id", $context->entityId())
            ->where("$table.in_process", StorySupport::STATUS_VIDEO_PROCESS)
            ->where("$table.expired_at", '>=', $nowTimestamp)
            ->where("$table.is_archive", MetaFoxConstant::IS_INACTIVE)
            ->orderBy("$table.id")
            ->get();
    }

    public function publishStories(Story $story): void
    {
        $table        = $this->getModel()->getTable();
        $nowTimestamp = Carbon::now()->subMinute()->timestamp;

        $query = $this->getModel()->newQuery()
            ->where("$table.user_id", $story->userId())
            ->where("$table.is_publish", false)
            ->where("$table.expired_at", '>=', $nowTimestamp)
            ->where("$table.id", '>=', $story->entityId());

        $storiesInProcess = $this->getStoryInProcess($story->user);

        if ($storiesInProcess->isNotEmpty()) {
            $query->where("$table.id", '<=', $storiesInProcess->first()->id);
        }

        $query->get()->each(function (Story $story) {
            $story->update([
                'is_publish' => true,
            ]);

            app('events')->dispatch('notification.new_post_to_follower', [$story->user, $story->refresh()]);
        });
    }
}
