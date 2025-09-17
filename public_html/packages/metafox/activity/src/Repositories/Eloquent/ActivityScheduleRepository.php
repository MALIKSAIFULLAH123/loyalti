<?php

namespace MetaFox\Activity\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Activity\Jobs\SendScheduledPost;
use MetaFox\Activity\Models\ActivitySchedule;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Policies\FeedPolicy;
use MetaFox\Activity\Repositories\ActivityScheduleRepositoryInterface;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class ActivityScheduleRepository.
 * @method ActivitySchedule getModel()
 */
class ActivityScheduleRepository extends AbstractRepository implements ActivityScheduleRepositoryInterface
{
    public function model()
    {
        return ActivitySchedule::class;
    }

    public function getForEdit(User $context, int $id): ActivitySchedule
    {
        $scheduled = $this->find($id);

        policy_authorize(FeedPolicy::class, 'moderateScheduled', $context, $scheduled);

        return $scheduled;
    }

    public function viewScheduledPosts(User $context, User $owner, ?array $attributes = []): Paginator
    {
        policy_authorize(FeedPolicy::class, 'schedulePost', $context);

        $entityId   = Arr::get($attributes, 'entity_id');
        $entityType = Arr::get($attributes, 'entity_type');

        $cond = [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ];

        if ($entityId && $entityType) {
            $cond = [
                'user_id'   => $entityId,
                'user_type' => $entityType,
            ];
        }

        $limit = Arr::get($attributes, 'limit');
        $query = $this->getModel()->newQuery()
            ->where($cond);

        return $query
            ->orderBy('schedule_time')
            ->simplePaginate($limit, ['activity_schedules.*']);
    }

    public function viewScheduledPost(User $context, int $id): ActivitySchedule
    {
        $scheduled = $this->find($id);

        policy_authorize(FeedPolicy::class, 'moderateScheduled', $context, $scheduled);

        return $scheduled;
    }

    /**
     * @param ActivitySchedule $schedule
     * @param int|null         $limit
     * @return array
     */
    public function getScheduledTaggedFriend(ActivitySchedule $schedule, ?int $limit = 3): array
    {
        $taggedFriends = Arr::get($schedule->data ?? [], 'tagged_friends');
        if (!is_array($taggedFriends) || !count($taggedFriends)) {
            return [];
        }
        $taggedFriends = array_map(function ($friend) {
            return $friend['friend_id'] ?? 0;
        }, $taggedFriends);

        $query = UserEntity::query()
            ->whereIn('id', $taggedFriends);

        return $query->paginate($limit, ['user_entities.*'])->items();
    }

    public function getScheduledEmbedObject(ActivitySchedule $schedule, bool $toForm = false): ?array
    {
        app('events')->dispatch('feed.schedule.get_embed_object', [$schedule, $toForm], true);
        $data = $schedule->data;

        if ($data['post_type'] == 'activity_post') {
            return null;
        }

        $data['resource_name'] = 'scheduled_embed';
        $data['module_name']   = 'feed';
        $data['id']            = $schedule->id;
        $data['type_id']       = $data['post_type'];
        $data['schedule_type'] = $data['schedule_type'] ?? 'feed';

        if (Arr::get($data, 'unset_privacy')) {
            unset($data['privacy']);
        }

        if (!$toForm) {
            $data['statistic'] = [];
        }

        if ($data['post_type'] == 'link') {
            foreach ($data as $key => $item) {
                if (str_contains($key, 'link_')) {
                    $data[str_replace('link_', '', $key)] = $item;
                    unset($data[$key]);
                }
            }
            $data['link']          = Arr::get($data, 'url');
            $data['schedule_type'] = 'core';
            $toForm && $data['has_embed'] = false;
        }

        unset($data['tagged_friends'], $data['post_type'], $data['user_status'], $data['content'], $data['schedule_time']);

        return $data;
    }

    /**
     * @param int      $isTemp
     * @param int|null $limit
     * @throws AuthorizationException
     */
    public function sendScheduledPost(int $isTemp, ?int $limit = 5): void
    {
        $query = $this->getModel()
            ->newModelQuery()
            ->where('is_temp', '=', $isTemp);

        $total          = $query->count();
        $scheduledPosts = $query->simplePaginate($limit);
        if (!$scheduledPosts->count()) {
            return;
        }
        /** @var FeedRepositoryInterface $feedRepository */
        $feedRepository = resolve(FeedRepositoryInterface::class);
        foreach ($scheduledPosts as $scheduledPost) {
            if (!$scheduledPost instanceof ActivitySchedule) {
                continue;
            }

            if (!UserPrivacy::hasAccess($scheduledPost->user, $scheduledPost->owner, 'feed.share_on_wall')) {
                $scheduledPost->delete();
                continue;
            }

            try {
                $response = $feedRepository->createFeed($scheduledPost->user, $scheduledPost->user, $scheduledPost->owner, $scheduledPost->data);
                $feed     = Arr::get($response, 'feed');
                if ($feed instanceof Feed && $feed->item instanceof Content) {
                    $feed->item->created_at = $scheduledPost->schedule_time;
                    $feed->item->updated_at = $scheduledPost->schedule_time;
                    $feed->item->saveQuietly();
                }
            } catch (\Exception $e) {
                // TODO Improve send notification for user
            }
            $scheduledPost->delete();
        }
        if ($total > $limit) {
            SendScheduledPost::dispatch($isTemp);
        }
    }

    public function monitorScheduledPost(): array
    {
        $now        = Carbon::now();
        $schedule   = $this->getModel()->newModelQuery()
            ->where('is_temp', 0)
            ->where('schedule_time', '<=', $now)
            ->orderBy('schedule_time')
            ->first();
        $randomTemp = 0;
        if ($schedule instanceof ActivitySchedule) {
            $randomTemp = $now->timestamp;
            $this->getModel()->newQuery()
                ->where('schedule_time', '<=', $now)
                ->update([
                    'is_temp' => $randomTemp,
                ]);
        }

        // Reset stuck schedule
        $this->getModel()->newQuery()
            ->where('is_temp', '>', 0)
            ->where('is_temp', '<=', $now->timestamp - 3600) // Reset posts don't send after 1 hour
            ->update([
                'is_temp' => 0,
            ]);

        return [
            'schedule' => $schedule,
            'isTemp'   => $randomTemp,
        ];
    }

    /**
     * @param User $user
     * @param int  $id
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteScheduledPost(User $user, int $id): bool
    {
        $resource = $this->find($id);

        policy_authorize(FeedPolicy::class, 'moderateScheduled', $user, $resource);

        app('events')->dispatch('feed.schedule.delete_scheduled', [$resource], true);

        return (bool) $this->delete($id);
    }

    /**
     * @param User $user
     * @param int  $id
     * @return bool
     * @throws AuthorizationException
     */
    public function sendNowScheduledPost(User $user, int $id): bool
    {
        $scheduledPost = $this->find($id);
        $data          = $scheduledPost->data;

        $quotaCheckData = [
            'where'        => [
                'from_resource' => Feed::FROM_FEED_RESOURCE,
            ],
            'created_at'   => $data['schedule_time'],
            'second_extra' => [
                'entity_type' => ActivitySchedule::ENTITY_TYPE,
                'column'      => 'schedule_time',
            ],
        ];

        app('quota')->checkQuotaControlWhenCreateItem($user, Feed::ENTITY_TYPE, 1, $quotaCheckData);

        policy_authorize(FeedPolicy::class, 'moderateScheduled', $user, $scheduledPost);

        if (!UserPrivacy::hasAccess($scheduledPost->user, $scheduledPost->owner, 'feed.share_on_wall')) {
            abort(403, __p('activity::phrase.unable_to_share_this_post_due_to_privacy_setting'));
        }

        /** @var FeedRepositoryInterface $feedRepository */
        $feedRepository = resolve(FeedRepositoryInterface::class);
        unset($data['schedule_time']);
        $feedRepository->createFeed($scheduledPost->user, $scheduledPost->user, $scheduledPost->owner, $data);

        return (bool) $this->delete($id);
    }

    public function updateScheduledPost(User $context, User $user, int $id, array $params): ActivitySchedule
    {
        $scheduled = $this->with(['user', 'userEntity', 'owner', 'ownerEntity'])->find($id);

        policy_authorize(FeedPolicy::class, 'moderateScheduled', $context, $scheduled);

        $attribute = [
            'post_type'     => Arr::get($params, 'post_type'),
            'schedule_time' => Arr::get($params, 'schedule_time'),
            'content'       => Arr::get($params, 'user_status') ?: Arr::get($params, 'content'),
        ];

        $response = app('events')->dispatch('feed.schedule.edit', [$user, $scheduled, $params], true);

        $success   = Arr::get($response, 'success', false);
        $newParams = Arr::get($response, 'new_params');

        if ($response && !$success) {
            $errorMessage = Arr::get($response, 'error_message', __('validation.invalid'));

            $errorCode = Arr::get($response, 'error_code', 400);

            abort($errorCode, $errorMessage);
        }

        $attribute['data'] = $newParams ?? $params;

        $scheduled->fill($attribute);

        $scheduled->save();

        $scheduled->refresh();

        return $scheduled;
    }

    /**
     * @param User  $context
     * @param User  $owner
     * @param array $params
     * @return ActivitySchedule
     */
    public function createSchedule(User $context, User $user, User $owner, array $params): ActivitySchedule
    {
        $attribute = [
            'user_id'       => $user->entityId(),
            'user_type'     => $user->entityType(),
            'owner_id'      => $owner->entityId(),
            'owner_type'    => $owner->entityType(),
            'post_type'     => Arr::get($params, 'post_type'),
            'schedule_time' => Arr::get($params, 'schedule_time'),
            'content'       => Arr::get($params, 'user_status') ?: Arr::get($params, 'content'),
        ];

        $attribute['data']            = $params;
        $attribute['data']['privacy'] = (int) Arr::get($params, 'privacy', 0);
        $schedule                     = new ActivitySchedule($attribute);
        $schedule->save();

        return $schedule;
    }
}
