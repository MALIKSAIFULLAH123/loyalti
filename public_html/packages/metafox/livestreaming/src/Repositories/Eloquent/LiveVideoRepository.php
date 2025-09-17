<?php

namespace MetaFox\LiveStreaming\Repositories\Eloquent;

use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\LiveStreaming\Jobs\CheckLiveVideoInterrupt;
use MetaFox\LiveStreaming\Jobs\CheckViewerInterrupt;
use MetaFox\LiveStreaming\Jobs\DeleteVideoAsset;
use MetaFox\LiveStreaming\Jobs\DisableStreamKeyByLimit;
use MetaFox\LiveStreaming\Jobs\SendStartLiveStreamNotification;
use MetaFox\LiveStreaming\Jobs\WarningLiveTimeLimit;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\LiveStreaming\Models\PlaybackData;
use MetaFox\LiveStreaming\Models\StreamingService;
use MetaFox\LiveStreaming\Models\UserStreamKey;
use MetaFox\LiveStreaming\Policies\LiveVideoPolicy;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\UserStreamKeyRepositoryInterface;
use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\DurationScope;
use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\ViewScope;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Contracts\ActionEntity;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\TagScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\Platform\Traits\Eloquent\Model\HasFilterTagUserTrait;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\UserReactedTrait;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Traits\UserMorphTrait;
use MrShan0\PHPFirestore\Fields\FireStoreObject;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class LiveVideoRepository.
 */
class LiveVideoRepository extends AbstractRepository implements LiveVideoRepositoryInterface
{
    use UserReactedTrait;
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;
    use RepoTrait;
    use IsFriendTrait {
        IsFriendTrait::getTaggedFriends as getTaggedFriendsTrait;
    }
    use HasFilterTagUserTrait;

    public const FIREBASE_COLLECTION         = 'live_video';
    public const FIREBASE_LIKE_COLLECTION    = 'live_video_like';
    public const FIREBASE_COMMENT_COLLECTION = 'live_video_comment';
    public const FIREBASE_VIEW_COLLECTION    = 'live_video_view';
    public const TYPE_MOBILE                 = 'mobile';
    public const TYPE_OBS                    = 'obs';
    public const TYPE_WEBCAM                 = 'webcam';

    public const STATUS_ACTIVE  = 'active';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_IDLE    = 'idle';
    public const STATUS_DELETED = 'deleted';

    public const DEFAULT_VIDEO_PLAYBACK     = 'https://stream.mux.com/';
    public const DEFAULT_THUMBNAIL_PLAYBACK = 'https://image.mux.com/';

    public const ACTION_USER_GO_LIVE           = 'user.go_live';
    public const ACTION_USER_GO_LIVE_WEBCAM    = 'user.go_live_webcam';
    public const SERVICE_MUX                   = 'mux';
    public const FIREBASE_COMMENT_LIMIT        = 100;
    public const FIREBASE_LIKE_LIMIT           = 100;
    public function model(): string
    {
        return Model::class;
    }

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws Exception
     */
    public function handleMuxWebhook(array $data): bool
    {
        $dateTime = $data['created_at'] ?? null;
        $type     = $data['type'] ?? null;
        if (!$type) {
            return false;
        }
        $data      = collect($data['data']);
        $streamKey = $data->get('stream_key');
        $streamId  = $data->get('live_stream_id');
        if (!$streamKey && !$streamId) {
            return false;
        }
        // Find live video
        /** @var ?Model $liveVideo */
        $query  = $this->getModel()->newQuery()->where('stream_key', $streamKey);
        if ($streamId) {
            $query->orWhere('live_stream_id', $streamId);
        }
        $liveVideo = $query->first();

        $service    = $this->getServiceManager();
        $muxService = $service->getStreamingServiceByDriver(self::SERVICE_MUX);
        if (!$muxService instanceof StreamingService) {
            return false;
        }
        $className = $muxService->service_class;
        // Create if not exist
        if (!$liveVideo && $type == $className::VIDEO_LIVE_STREAM_ACTIVE) {
            $this->createLiveVideoWithStreamKey($streamKey, $data->get('active_asset_id'), $type);

            return true;
        }
        if ($liveVideo && $dateTime && $liveVideo->updated_at
            && strtotime($dateTime) < strtotime($liveVideo->updated_at)
            && $type !== $className::VIDEO_ASSET_TYPE_LIVE_STREAM_COMPLETED) {
            return false;
        }
        switch ($type) {
            case $className::VIDEO_LIVE_STREAM_ACTIVE:
                // Live stream start and send notify / add firebase
                if ($liveVideo && $liveVideo->view_id != 2) {
                    $this->startLiveStream($liveVideo->id, $liveVideo, $dateTime);
                }
                if ($liveVideo && $liveVideo->live_type == 'mobile') {
                    $this->getLiveVideoRepository()->validateLimitTime($liveVideo->user, $liveVideo->live_stream_id, $liveVideo, false, true);
                }
                $this->getUserStreamKeyRepository()->updateUserStreamKey($liveVideo->stream_key, $type, $liveVideo);
                break;
            case $className::VIDEO_LIVE_STREAM_IDLE:
                $this->getUserStreamKeyRepository()->updateUserStreamKey($liveVideo->stream_key, $type, $liveVideo);
                if ($liveVideo && $liveVideo->view_id != 2) {
                    $this->stopLiveStream($liveVideo->id, $liveVideo, $dateTime);
                }
                break;
            case $className::VIDEO_ASSET_TYPE_LIVE_STREAM_COMPLETED:
                if ($liveVideo && $liveVideo->view_id != 2) {
                    $this->updateAssets($liveVideo, [
                        'playback_ids' => $data->get('playback_ids'),
                        'asset_id'     => $data->get('id'),
                        'duration'     => $data->get('duration'),
                    ]);
                }
                break;
            case $className::VIDEO_LIVE_STREAM_DISABLED:
            case $className::VIDEO_LIVE_STREAM_DELETED:
            case $className::VIDEO_LIVE_STREAM_DISCONNECTED:
                $this->getUserStreamKeyRepository()->updateUserStreamKey($streamKey, $type, $liveVideo);
                break;
        }

        return true;
    }

    public function createLiveVideoWithStreamKey(string $streamKey, ?string $activeAssetId = null, ?string $type = '', ?string $liveType = self::TYPE_OBS): mixed
    {
        /** @var UserStreamKey $streamKey */
        $streamKey = UserStreamKey::query()
            ->where('stream_key', $streamKey)
            ->where('is_streaming', 0)
            ->first();
        if (!$streamKey) {
            return false;
        }

        if ($activeAssetId) {
            $streamKey->asset_id = $activeAssetId;
            $streamKey->save();
        }

        $attributes = [
            'title'          => '',
            'stream_key'     => $streamKey->stream_key,
            'user_id'        => $streamKey->userId(),
            'user_type'      => $streamKey->userType(),
            'owner_id'       => $streamKey->userId(),
            'owner_type'     => $streamKey->userType(),
            'live_stream_id' => $streamKey->live_stream_id,
            'live_type'      => $liveType,
            'asset_id'       => $activeAssetId,
            'is_landscape'   => 1,
            'view_id'        => 2,
            'privacy'        => MetaFoxPrivacy::EVERYONE,
        ];

        $attributes['is_approved'] = policy_check(LiveVideoPolicy::class, 'autoApprove', $streamKey->user, $streamKey->user);

        if ($streamKey->playback_ids) {
            $playback = unserialize($streamKey->playback_ids);
            if (is_array($playback) && count($playback)) {
                $attributes['playback'] = [
                    'playback_id' => $playback[0]['id'] ?? '',
                    'privacy'     => 0, // Always public
                ];
            }
        }
        $model = $this->getModel()->newModelInstance();
        $model->fill($attributes);

        $model->save();
        $model->refresh();

        $this->getUserStreamKeyRepository()->updateUserStreamKey($streamKey->stream_key, $type, $model);

        // Limit live time
        $this->validateLimitTime($streamKey->user, $streamKey->live_stream_id, $model);

        return $model;
    }

    public function createLiveVideo(ContractUser $context, ContractUser $owner, array $attributes): Model
    {
        app('events')->dispatch('livestreaming.pre_live_video_create', [$context, $attributes], true);
        $attributes = array_merge($attributes, [
            'user_id'        => $context->entityId(),
            'user_type'      => $context->entityType(),
            'owner_id'       => $owner->entityId(),
            'owner_type'     => $owner->entityType(),
            'live_type'      => self::TYPE_MOBILE,
            'live_stream_id' => $attributes['id'] ?? null,
        ]);

        if (!isset($attributes['title'])) {
            $attributes['title'] = '';
        }

        if (isset($attributes['temp_file']) && $attributes['temp_file'] > 0) {
            $tempFile                    = upload()->getFile($attributes['temp_file']);
            $attributes['image_file_id'] = $tempFile->id;

            // Delete temp file after done
            upload()->rollUp($attributes['temp_file']);
        }

        if (isset($attributes['playback_ids']) && is_array($attributes['playback_ids'])) {
            $playbacks = array_map(function ($playback) {
                return [
                    'playback_id' => $playback['id'] ?? '',
                    'privacy'     => 0, // Always public
                ];
            }, $attributes['playback_ids']);
            $attributes['playback'] = array_shift($playbacks);
        }

        $forceApproved = Arr::get($attributes, 'force_approved', false);
        if (!$forceApproved) {
            $attributes['is_approved'] = policy_check(LiveVideoPolicy::class, 'autoApprove', $context, $owner);
        }

        if ($owner->hasPendingMode()) {
            $attributes['is_approved'] = 1;
        }
        /** @var Model $model */
        $model = $this->getModel()->newModelInstance();
        $model->fill($attributes);

        if (isset($attributes['privacy']) && $attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $model->setPrivacyListAttribute($attributes['list']);
        }

        $model->save();

        $model->refresh();

        $model->allow_feed = 1;
        $model->save();

        return $model;
    }

    public function createStoryItem(Model $liveVideo): void
    {
        if (!$liveVideo->to_story) {
            return;
        }
        app('events')->dispatch(
            'livestreaming.create_story',
            [$liveVideo, $this->getThumbnailPlayback($liveVideo->entityId()), $this->getVideoPlayback($liveVideo->entityId())],
            true
        );
    }

    public function updateLiveVideo(ContractUser $context, int $id, array $attributes, bool $isGoLive = false): Model
    {
        $liveVideo = $this->withUserMorphTypeActiveScope()->find($id);

        if (!$liveVideo instanceof Model) {
            throw new ModelNotFoundException();
        }
        $removeImage = Arr::get($attributes, 'remove_image', 0);

        if (!isset($attributes['title'])) {
            $attributes['title'] = '';
        }

        if ($removeImage) {
            $image = $liveVideo->image_file_id;
            app('storage')->deleteFile($image, null);
            $attributes['image_file_id'] = null;
        }

        if (isset($attributes['temp_file']) && $attributes['temp_file'] > 0) {
            $tempFile                    = upload()->getFile($attributes['temp_file']);
            $attributes['image_file_id'] = $tempFile->id;

            // Delete temp file after done
            upload()->rollUp($attributes['temp_file']);
        }

        if ($isGoLive && !isset($attributes['is_approved'])) {
            $attributes['is_approved'] = policy_check(LiveVideoPolicy::class, 'autoApprove', $context, UserEntity::getById($attributes['owner_id'])->detail);
        }

        $updatePlayback = false;
        if (!empty($attributes['playback_ids'])) {
            $playbacks = array_map(function ($playback) {
                return [
                    'playback_id' => $playback['id'] ?? '',
                    'privacy'     => 0, // Always public
                ];
            }, $attributes['playback_ids']);
            $attributes['playback'] = array_shift($playbacks);
            $updatePlayback         = true;
        }

        if (isset($attributes['duration'])) {
            $attributes['duration'] = (int) $attributes['duration'];
        }

        $attributes['allow_feed'] = 1;

        $liveVideo->fill($attributes);

        if (isset($attributes['privacy']) && $attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $liveVideo->setPrivacyListAttribute($attributes['list']);
        }

        $liveVideo->save();

        $liveVideo->refresh();

        if (!$liveVideo->is_streaming && $liveVideo->view_id != 2
            && $liveVideo->isApproved() && isset($attributes['tagged_friends'])) {
            $this->updateTagFriends($liveVideo, $attributes['tagged_friends']);
        }

        if ($updatePlayback) {
            app('events')->dispatch('livestreaming.updated_assets', [$liveVideo, $this->getThumbnailPlayback($liveVideo->entityId()), $this->getVideoPlayback($liveVideo->entityId())]);
        }
        $this->updateFeedStatus($liveVideo);

        return $liveVideo;
    }

    protected function updateFeedStatus(Model $liveVideo): void
    {
        app('events')->dispatch('activity.feed.mark_as_pending', [$liveVideo]);
    }

    public function deleteLiveVideo(ContractUser $context, int $id): int
    {
        $liveVideo = $this->withUserMorphTypeActiveScope()->find($id);
        if (!empty($liveVideo)) {
            DeleteVideoAsset::dispatch($liveVideo->asset_id, $liveVideo->live_stream_id);
        }

        app('events')->dispatch('livestreaming.delete_live_video', [$liveVideo]);

        return $this->delete($id);
    }

    /**
     * @param  int         $id
     * @param  Model|null  $liveVideo
     * @param  string|null $dateTime
     * @return bool
     * @throws Exception
     */
    public function startLiveStream(int $id, ?Model $liveVideo = null, string $dateTime = null): bool
    {
        if (!$liveVideo) {
            $liveVideo = $this->withUserMorphTypeActiveScope()->find($id);
        }

        if ($liveVideo->is_streaming) {
            return false;
        }

        $liveVideo->is_streaming = 1;
        $liveVideo->allow_feed   = 1;
        $liveVideo->view_id      = 0;
        $liveVideo->updated_at   = $dateTime ?? now();

        $liveVideo->saveQuietly();

        $liveVideo->refresh();

        $this->publishVideoActivity($liveVideo);

        $this->saveToFirebase($liveVideo);

        return true;
    }

    public function publishVideoActivity(Model $liveVideo, bool $noFeed = false): bool
    {
        $isApprove = $liveVideo->isApproved();
        if (!$noFeed && $isApprove) {
            app('events')->dispatch('activity.feed.create_from_resource', [$liveVideo], true);
        }

        if ($liveVideo->activity_feed instanceof HasHashTag) {
            // Update hashtag
            app('events')->dispatch('hashtag.create_hashtag', [$liveVideo->user, $liveVideo->activity_feed, $liveVideo->activity_feed->content], true);
        }

        // Send notification to all friends/follower except off notification users
        if (!$liveVideo->owner instanceof HasPrivacyMember) {
            SendStartLiveStreamNotification::dispatch($liveVideo->id);
        }

        // Update tag friends
        if ($isApprove && $liveVideo->tagged_friends) {
            $this->updateTagFriends($liveVideo, $liveVideo->tagged_friends);
        }

        // Save firebase
        if ($noFeed) {
            $this->saveToFirebase($liveVideo, '');
        }

        // Story
        if ($isApprove) {
            $this->createStoryItem($liveVideo);
        }

        return true;
    }

    public function stopLiveStream(int $id, ?Model $liveVideo = null, string $dateTime = null, bool $isDelete = false): Model
    {
        if (!$liveVideo) {
            $liveVideo = $this->withUserMorphTypeActiveScope()->find($id);
        }

        if (!$liveVideo->is_streaming) {
            return $liveVideo;
        }

        $service       = $this->getServiceManager();
        $serviceName   = $service->getDefaultServiceName();
        $serviceDriver = $service->getDefaultServiceProvider();
        if ($serviceDriver && in_array($liveVideo->live_type, [self::TYPE_OBS, self::TYPE_WEBCAM]) && $serviceName == self::SERVICE_MUX) {
            $serviceDriver->executeApi('live-streams/' . $liveVideo->live_stream_id . '/disable', 'PUT');
        }
        $liveVideo->is_streaming = 0;
        $liveVideo->saveQuietly();
        $liveVideo->refresh();
        $this->saveToFirebase($liveVideo, $isDelete ? self::STATUS_DELETED : self::STATUS_IDLE);

        app('events')->dispatch('livestreaming.stop_live_video', [$liveVideo]);

        return $liveVideo;
    }

    public function saveToFirebase(Model $liveVideo, string $status = self::STATUS_ACTIVE): bool
    {
        $streamKey = $liveVideo->stream_key;
        if (!$streamKey) {
            return false;
        }
        $document = app('firebase.firestore')->getDocument(self::FIREBASE_COLLECTION, $streamKey);
        $fields   = ['module_name', 'resource_name', 'title', 'stream_key', 'playback', 'user', 'id', 'created_at', 'image', 'location_latitude', 'location_longitude', 'location_name', 'is_streaming', 'is_approved', 'location_address'];
        $data     = $liveVideo->toArray();
        $item     = $document ?: [];
        foreach ($data as $key => $value) {
            if (in_array($key, $fields)) {
                $item[$key] = $value;
            }
        }
        if (!empty($status)) {
            $item['status'] = $status;
            if ($status == self::STATUS_ACTIVE) {
                $item['active_date'] = now()->toIso8601String();
            }
        }
        if ($status == self::STATUS_WAITING) {
            $limitTime = (int) $liveVideo->user->getPermissionValue('live_video.limit_live_stream_time');
            if ($limitTime > 0) {
                $item['end_date'] = now()->addMinutes($limitTime)->toIso8601String();
            } else {
                $item['end_date'] = null;
            }
        }
        $item['time_limit_warning'] = 0;
        $user                       = new UserEntityDetail($liveVideo->userEntity);
        $avatar                     = $user->resource->profile?->avatars;
        $item['playback']           = new FireStoreObject($liveVideo->playback?->toArray());
        $item['user']               = new FireStoreObject([
            'id'            => $user->resource->entityId(),
            'module_name'   => $user->resource->entityType(),
            'resource_name' => $user->resource->entityType(),
            'full_name'     => $user->resource->full_name,
            'user_name'     => $user->resource->user_name,
            'avatar'        => $avatar ? new FireStoreObject($avatar) : null,
        ]);

        return app('firebase.firestore')->addDocument(self::FIREBASE_COLLECTION, $liveVideo->stream_key, $item);
    }

    /**
     * @param  ContractUser           $context
     * @param  ContractUser           $owner
     * @param  array                  $attributes
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewLiveVideos(ContractUser $context, ContractUser $owner, array $attributes): Paginator
    {
        $limit = $attributes['limit'];

        $view = $attributes['view'];

        $this->withUserMorphTypeActiveScope();

        if ($view == Browse::VIEW_FEATURE) {
            return $this->findFeature($limit);
        }

        if (Browse::VIEW_PENDING == $view) {
            if (Arr::get($attributes, 'user_id') == 0 || Arr::get($attributes, 'user_id') != $context->entityId()) {
                if (!$context->hasPermissionTo('live_video.approve')) {
                    throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
                }
            }
        }
        $query = $this->buildQueryViewLiveVideos($context, $owner, $attributes);

        $relations = ['liveVideoText', 'user', 'userEntity'];

        /* @var \Illuminate\Pagination\Paginator $liveVideoData */
        return $query
            ->with($relations)
            ->simplePaginate($limit, ['livestreaming_live_videos.*']);
    }

    /**
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function buildQueryViewLiveVideos(ContractUser $context, ContractUser $owner, array $attributes): Builder
    {
        $sort       = $attributes['sort'];
        $sortType   = $attributes['sort_type'];
        $when       = $attributes['when'] ?? '';
        $view       = $attributes['view'] ?? '';
        $search     = $attributes['q'] ?? '';
        $duration   = $attributes['duration'] ?? '';
        $streaming  = $attributes['streaming'] ?? null;
        $searchTag  = $attributes['tag'] ?? '';
        $profileId  = $attributes['user_id']; //$profileId == $owner->entityId() if has param user_id
        $isFeatured = $attributes['is_featured'] ?? null;

        if ($context->entityId() && $profileId == $context->entityId() && $view != Browse::VIEW_PENDING) {
            $view = Browse::VIEW_MY;
        }

        // Scopes.
        $privacyScope = new PrivacyScope();
        $privacyScope
            ->setUserId($context->entityId())
            ->setModerationPermissionName('live_video.moderate')
            ->setHasUserBlock(true);

        $sortScope     = new SortScope($sort, $sortType);
        $whenScope     = new WhenScope($when);
        $durationScope = new DurationScope($duration);

        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)->setView($view)->setProfileId($profileId);

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        if ($searchTag != '') {
            $query = $query->addScope(new TagScope($searchTag));
        }

        if ($streaming) {
            $query->where('livestreaming_live_videos.is_streaming', 1);
        }

        $query->addScope(new FeaturedScope($isFeatured));

        if ($owner->entityId() != $context->entityId()) {
            $privacyScope->setOwnerId($owner->entityId());

            $viewScope->setIsViewOwner(true);

            if (!policy_check(LiveVideoPolicy::class, 'approve', $context, resolve(Model::class))) {
                $query->where('livestreaming_live_videos.is_approved', '=', 1);
            }
        }

        $query = $this->applyDisplayLiveVideoSetting($query, $owner, $view);

        return $query
            ->addScope($privacyScope)
            ->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($viewScope)
            ->addScope($durationScope);
    }

    /**
     * @param  Builder      $query
     * @param  ContractUser $owner
     * @param  string       $view
     * @return Builder
     */
    private function applyDisplayLiveVideoSetting(Builder $query, ContractUser $owner, string $view): Builder
    {
        if (in_array($view, [Browse::VIEW_MY, ViewScope::VIEW_MY_STREAMING])) {
            return $query;
        }

        if (!$owner instanceof HasPrivacyMember) {
            $query->where('livestreaming_live_videos.owner_type', '=', $owner->entityType());
        }

        return $query;
    }

    public function findFeature(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_featured', Model::IS_FEATURED)
            ->where('is_approved', Model::IS_APPROVED)
            ->orderByDesc(HasFeature::FEATURED_AT_COLUMN)
            ->simplePaginate($limit);
    }

    public function findSponsor(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_sponsor', Model::IS_SPONSOR)
            ->where('is_approved', Model::IS_APPROVED)
            ->simplePaginate($limit);
    }

    /**
     * @throws AuthorizationException
     */
    public function viewLiveVideo(ContractUser $context, int $id): Model
    {
        $liveVideo = $this
            ->withUserMorphTypeActiveScope()
            ->with(['liveVideoText', 'user', 'userEntity', 'attachments'])
            ->find($id);

        if ($context->isGuest()) {
            return $liveVideo->refresh();
        }

        $liveVideo->incrementTotalView();
        $liveVideo->refresh();

        return $liveVideo;
    }

    public function pingStreaming(int $id): void
    {
        $liveVideo = $this->withUserMorphTypeActiveScope()->find($id);

        $liveVideo->last_ping = time();
        $liveVideo->saveQuietly();

        CheckLiveVideoInterrupt::dispatch($id)->delay(Carbon::now()->addSeconds(60));
    }

    /**
     * @throws AuthenticationException
     */
    public function pingViewer(int $id): void
    {
        $context   = user();
        $userId    = $context->entityId();
        $liveVideo = $this->withUserMorphTypeActiveScope()->find($id);
        $doc       = app('firebase.firestore')->getDocument(self::FIREBASE_VIEW_COLLECTION, $liveVideo->stream_key);

        if (!isset($doc['view'])) {
            return;
        }

        foreach ($doc['view'] as $index => $view) {
            $user = $view->getData();
            if ($user['id'] == $userId) {
                $user['last_ping']   = time();
                $doc['view'][$index] = new FireStoreObject($user);
            }
        }

        app('firebase.firestore')->updateDocument(self::FIREBASE_VIEW_COLLECTION, $liveVideo->stream_key, $doc);

        CheckViewerInterrupt::dispatch($id, $userId)->delay(Carbon::now()->addSeconds(90));
    }

    public function getVideoPlayback(int $id): string
    {
        $liveVideo = $this->withUserMorphTypeActiveScope()->find($id);
        if (!$liveVideo || !$liveVideo->playback) {
            return '';
        }

        $customVideoUrl  = Settings::get('livestreaming.custom_video_playback_url');
        $defaultVideoUrl = self::DEFAULT_VIDEO_PLAYBACK;
        $videoPlayback   = !empty($customVideoUrl) ? trim($customVideoUrl, '/') . '/' : $defaultVideoUrl;

        return $videoPlayback . $liveVideo->playback->playback_id . '.m3u8';
    }

    public function getThumbnailPlayback(int $id): array
    {
        $liveVideo = $this->withUserMorphTypeActiveScope()->find($id);
        if (!$liveVideo) {
            return [];
        }

        if ($liveVideo->images) {
            return $liveVideo->images;
        }

        if (!$liveVideo->playback) {
            return [];
        }

        $customThumbnailUrl  = Settings::get('livestreaming.custom_thumbnail_playback_url');
        $defaultThumbnailUrl = self::DEFAULT_THUMBNAIL_PLAYBACK;
        $thumbnailPlayback   = !empty($customThumbnailUrl) ? trim($customThumbnailUrl, '/') . '/' : $defaultThumbnailUrl;

        return [
            'origin' => $thumbnailPlayback . $liveVideo->playback->playback_id . '/thumbnail.png',
        ];
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @throws Exception
     */
    public function startGoLive(ContractUser $context, ContractUser $owner, array $attributes): Model
    {
        $attributes['owner_id']   = $owner->entityId();
        $attributes['owner_type'] = $owner->entityType();

        if ($owner->hasPendingMode()) {
            $attributes['is_approved'] = 1;
        }

        if ($owner instanceof HasPrivacyMember && $owner instanceof PostBy) {
            // Only when the content is created on owner
            $attributes['privacy'] = $owner->getPrivacyPostBy();
        }

        $streamKey = $attributes['stream_key'];

        if (isset($attributes['type']) && $attributes['type'] == self::TYPE_WEBCAM) {
            $this->createLiveVideoWithStreamKey($streamKey, null, self::ACTION_USER_GO_LIVE_WEBCAM, self::TYPE_WEBCAM);
            $attributes['webcam_config'] = json_encode($attributes['webcam_player']);
        }

        /** @var Model $liveVideo */
        $liveVideo = $this->getModel()->newQuery()->where('stream_key', $streamKey)->firstOrFail();
        $liveVideo = $this->updateLiveVideo($context, $liveVideo->id, $attributes, true);

        $this->getUserStreamKeyRepository()->updateUserStreamKey($liveVideo->stream_key, self::ACTION_USER_GO_LIVE, $liveVideo);

        $this->validateLimitTime($context, $liveVideo->live_stream_id, $liveVideo, true);
        $this->startLiveStream($liveVideo->id);

        return $liveVideo;
    }

    /**
     * @throws Exception
     */
    public function updateViewerCount(Model $liveVideo, array $attributes): Model|bool
    {
        $doc = app('firebase.firestore')->getDocument(self::FIREBASE_VIEW_COLLECTION, $liveVideo->stream_key);

        $context = user();

        $user   = new UserEntityDetail($context);
        $avatar = $user->resource->profile?->avatars;

        $data = new FireStoreObject([
            'id'            => $user->resource->entityId(),
            'full_name'     => $user->resource->full_name ?? '',
            'user_name'     => $user->resource->user_name ?? '',
            'module_name'   => $user->resource->entityType() ?? 'user',
            'resource_name' => $user->resource->entityType() ?? 'user',
            'avatar'        => $avatar ? new FireStoreObject($avatar) : null,
            'short_name'    => $user->resource->short_name ?? '',
            'timestamp'     => time(),
            'last_ping'     => time(),
        ]);

        if (isset($doc['view'])) {
            foreach ($doc['view'] as $view) {
                $user = $view->getData();
                if ($user['id'] == $context->entityId()) {
                    return $liveVideo;
                }
            }

            $doc['view'][]       = $data;
            $doc['total_viewer'] = count($doc['view'] ?? []);
            app('firebase.firestore')->updateDocument(self::FIREBASE_VIEW_COLLECTION, $liveVideo->stream_key, $doc);
        } else {
            $doc = [
                'id'            => $liveVideo->entityId(),
                'resource_name' => $liveVideo->entityType(),
                'module_name'   => 'livestreaming',
                'view'          => [$data],
                'total_viewer'  => 1,
            ];
            app('firebase.firestore')->addDocument(self::FIREBASE_VIEW_COLLECTION, $liveVideo->stream_key, $doc);
        }
        $liveVideo->total_viewer = count($doc['view'] ?? []);
        $liveVideo->saveQuietly();

        return $liveVideo->refresh();
    }

    /**
     * @throws Exception
     */
    public function removeViewerCount(Model $liveVideo, ContractUser $context): Model|bool
    {
        $doc = app('firebase.firestore')->getDocument(self::FIREBASE_VIEW_COLLECTION, $liveVideo->stream_key);
        foreach ($doc['view'] as $index => $view) {
            $user = $view->getData();
            if ($user['id'] == $context->entityId()) {
                array_splice($doc['view'], $index, 1);
            }
        }

        $doc['total_viewer'] = count($doc['view'] ?? []);

        $liveVideo->total_viewer = count($doc['view'] ?? []);
        $liveVideo->saveQuietly();

        return app('firebase.firestore')->updateDocument(self::FIREBASE_VIEW_COLLECTION, $liveVideo->stream_key, $doc);
    }

    public function addLiveLike(Model $liveVideo, Entity $like): bool
    {
        $doc = app('firebase.firestore')->getDocument(self::FIREBASE_LIKE_COLLECTION, $liveVideo->stream_key);

        $data = ResourceGate::asDetail($like)->toArray(request());
        /** @var array $user */
        $user = $data['user'];
        /** @var JsonResource $reaction */
        $reaction = $data['reaction'];

        if (!$user || !$reaction) {
            return false;
        }

        $data['timestamp'] = time();

        $avatar = Arr::get($user, 'avatar');

        $data['user'] = new FireStoreObject([
            'id'            => Arr::get($user, 'id'),
            'full_name'     => Arr::get($user, 'full_name', ''),
            'user_name'     => Arr::get($user, 'user_name', ''),
            'module_name'   => Arr::get($user, 'resource_name') ?? 'user',
            'resource_name' => Arr::get($user, 'resource_name') ?? 'user',
            'avatar'        => $avatar ? new FireStoreObject($avatar) : null,
            'short_name'    => Arr::get($user, 'short_name', ''),
            'url'           => Arr::get($user, 'url', ''),
        ]);
        $data['reaction'] = new FireStoreObject([
            'id'            => $reaction->resource->entityId(),
            'module_name'   => $reaction->resource->entityType() ?? 'preaction',
            'resource_name' => $reaction->resource->entityType() ?? 'like',
            'title'         => $reaction->resource->title ?? 'Like',
            'icon'          => $reaction->resource->icon ?? '',
            'icon_mobile'   => $reaction->resource->icon_mobile ?? '',
            'color'         => "#{$reaction->resource->color}",
        ]);

        $mostReactions = $this->userMostReactions($liveVideo->user, $liveVideo);

        $mostReactions = $mostReactions->toArray(request());

        $mostReactionsInformation = $this->getItemReactionAggregation($liveVideo->user, $liveVideo);

        if (is_array($mostReactions) && count($mostReactions)) {
            foreach ($mostReactions as &$mostReaction) {
                $mostReaction = new FireStoreObject($mostReaction);
            }
            unset($mostReaction);
        } else {
            $mostReactions = null;
        }

        if (count($mostReactionsInformation)) {
            foreach ($mostReactionsInformation as &$mostReactionsInformationItem) {
                $mostReactionsInformationItem = new FireStoreObject($mostReactionsInformationItem);
            }
            unset($mostReactionsInformationItem);
        } else {
            $mostReactionsInformation = null;
        }

        $liveVideo->refresh();

        try {
            if (isset($doc['like'])) {
                $doc['like'][]                     = new FireStoreObject($data);
                $doc['total_like']                 = $liveVideo->total_like;
                $doc['most_reactions']             = $mostReactions;
                $doc['most_reactions_information'] = $mostReactionsInformation;

                if (count($doc['like']) > self::FIREBASE_LIKE_LIMIT) {
                    array_shift($doc['like']);
                }

                return app('firebase.firestore')->updateDocument(self::FIREBASE_LIKE_COLLECTION, $liveVideo->stream_key, $doc);
            }

            $doc = [
                'id'                         => $liveVideo->entityId(),
                'resource_name'              => $liveVideo->entityType(),
                'module_name'                => 'livestreaming',
                'total_like'                 => $liveVideo->total_like,
                'most_reactions'             => $mostReactions,
                'most_reactions_information' => $mostReactionsInformation,
                'like'                       => [new FireStoreObject($data)],
            ];

            return app('firebase.firestore')->addDocument(self::FIREBASE_LIKE_COLLECTION, $liveVideo->stream_key, $doc);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepareCommentData(ActionEntity $comment): array
    {
        $commentDetail            = ResourceGate::asDetail($comment)->toArray(request());
        $user                     = ResourceGate::asDetail($comment->user);
        $avatar                   = $user->resource->profile?->avatars;
        $mostReactions            = $this->userMostReactions($comment->user, $comment);
        $mostReactions            = $mostReactions->toArray(request());
        $mostReactionsInformation = $this->getItemReactionAggregation($comment->user, $comment);

        if (is_array($mostReactions) && count($mostReactions)) {
            foreach ($mostReactions as &$mostReaction) {
                $mostReaction = new FireStoreObject($mostReaction);
            }
            unset($mostReaction);
        } else {
            $mostReactions = null;
        }

        if (count($mostReactionsInformation)) {
            foreach ($mostReactionsInformation as &$mostReactionsInformationItem) {
                $mostReactionsInformationItem = new FireStoreObject($mostReactionsInformationItem);
            }
            unset($mostReactionsInformationItem);
        } else {
            $mostReactionsInformation = null;
        }

        $extraData = $commentDetail['extra_data'] instanceof JsonResource ? $commentDetail['extra_data']->toArray(request()) : null;
        if (!empty($extraData['image'])) {
            $extraData['image'] = new FireStoreObject($extraData['image']);
        }
        if (!empty($extraData['params'])) {
            $extraData['params'] = new FireStoreObject((array) $extraData['params']);
        }
        $totalLike = $comment?->total_like ?? 0;
        $data      = [
            'user' => new FireStoreObject([
                'id'            => $user->resource->entityId(),
                'full_name'     => $user->resource->full_name ?? '',
                'user_name'     => $user->resource->user_name ?? '',
                'module_name'   => $user->resource->entityType() ?? 'user',
                'resource_name' => $user->resource->entityType() ?? 'user',
                'avatar'        => $avatar ? new FireStoreObject($avatar) : null,
                'short_name'    => $user->resource->short_name ?? '',
                'url'           => $user->resource->toUrl() ?? '',
            ]),
            'id'            => $comment->entityId(),
            'module_name'   => $comment->entityType(),
            'resource_name' => $comment->entityType(),
            'text'          => $commentDetail['text'],
            'text_parsed'   => $commentDetail['text_parsed'],
            'text_raw'      => $commentDetail['text_raw'],
            'is_liked'      => $commentDetail['is_liked'],
            'link'          => $commentDetail['link'],
            'extra'         => new FireStoreObject($commentDetail['extra']),
            'extra_data'    => $extraData ? new FireStoreObject($extraData) : null,
            'total_like'    => $totalLike,
            'statistic'     => new FireStoreObject([
                'total_like' => $totalLike,
            ]),
            'most_reactions'             => $mostReactions,
            'most_reactions_information' => $mostReactionsInformation,
            'comment_type_id'            => $comment->item_type,
            'comment_item_id'            => $comment->item_id,
            'like_type_id'               => 'comment',
            'like_item_id'               => $comment->entityId(),
            'timestamp'                  => time(),
        ];
        if ($comment->parent_id > 0) {
            $commentDetail  = ResourceGate::asDetail($comment->parentComment)->toArray(request());
            $parentComment  = $comment->parentComment;
            $user           = new UserEntityDetail($parentComment->user);
            $data['parent'] = new FireStoreObject([
                'id'             => $parentComment->entityId(),
                'module_name'    => $parentComment->entityType(),
                'resource_name'  => $parentComment->entityType(),
                'text'           => $commentDetail['text'],
                'text_parsed'    => $commentDetail['text_parsed'],
                'text_raw'       => $commentDetail['text_raw'],
                'is_liked'       => $commentDetail['is_liked'],
                'link'           => $commentDetail['link'],
                'user_name'      => $user->resource->user_name ?? '',
                'user_full_name' => $user->resource->full_name ?? '',
                'user_id'        => $user->resource->entityId(),
            ]);
        }
        if ($totalLike && app_active('metafox/like')) {
            $allLike = DB::table('likes')
                ->where(['item_id' => $comment->entityId(), 'item_type' => $comment->entityType()])
                ->pluck('reaction_id', 'user_id')
                ->toArray();
            $data['lv_user_reacted'] = array_map(function ($reactionId, $userId) {
                return new FireStoreObject([
                    'user_id'     => $userId,
                    'reaction_id' => $reactionId,
                ]);
            }, array_values($allLike), array_keys($allLike));
        }

        return $data;
    }

    /**
     * @throws AuthenticationException
     */
    public function addLiveComment(Model $liveVideo, ActionEntity $comment): bool
    {
        $doc = app('firebase.firestore')->getDocument(self::FIREBASE_COMMENT_COLLECTION, $liveVideo->stream_key);

        $data = $this->prepareCommentData($comment);

        try {
            if (isset($doc['comment'])) {
                $doc['comment'][]     = new FireStoreObject($data);
                $doc['total_comment'] = $liveVideo->total_comment;

                if (count($doc['comment']) > self::FIREBASE_COMMENT_LIMIT) {
                    array_shift($doc['comment']);
                }

                return app('firebase.firestore')->updateDocument(self::FIREBASE_COMMENT_COLLECTION, $liveVideo->stream_key, $doc);
            }

            $doc = [
                'id'            => $liveVideo->entityId(),
                'resource_name' => $liveVideo->entityType(),
                'module_name'   => 'livestreaming',
                'total_comment' => 0,
                'comment'       => [new FireStoreObject($data)],
            ];

            return app('firebase.firestore')->addDocument(self::FIREBASE_COMMENT_COLLECTION, $liveVideo->stream_key, $doc);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @throws AuthenticationException
     */
    public function updateLiveComment(Model $liveVideo, ActionEntity $comment): bool
    {
        $doc = app('firebase.firestore')->getDocument(self::FIREBASE_COMMENT_COLLECTION, $liveVideo->stream_key);

        foreach ($doc['comment'] as $index => $item) {
            $item = $item->getData();

            if ((!isset($item['id'])) || ($item['id'] != $comment->entityId())) {
                continue;
            }

            $data                   = $this->prepareCommentData($comment);
            $doc['comment'][$index] = new FireStoreObject($data);
        }

        return app('firebase.firestore')->updateDocument(self::FIREBASE_COMMENT_COLLECTION, $liveVideo->stream_key, $doc);
    }

    public function removeLiveComment(Model $liveVideo, ActionEntity $comment): bool
    {
        $doc = app('firebase.firestore')->getDocument(self::FIREBASE_COMMENT_COLLECTION, $liveVideo->stream_key);

        $doc['comment'] = array_filter($doc['comment'], static function ($element) use ($comment) {
            $element = $element->getData();

            return (!isset($element['id'])) || ($element['id'] != $comment->entityId());
        });
        $doc['comment']       = array_values($doc['comment']);
        $doc['total_comment'] = $liveVideo->total_comment;

        return app('firebase.firestore')->updateDocument(self::FIREBASE_COMMENT_COLLECTION, $liveVideo->stream_key, $doc);
    }

    public function removeLiveLike(Model $liveVideo, Entity $like): bool
    {
        $doc = app('firebase.firestore')->getDocument(self::FIREBASE_LIKE_COLLECTION, $liveVideo->stream_key);

        $doc['like'] = array_filter($doc['like'], static function ($element) use ($like) {
            $element = $element->getData();

            return (!isset($element['id'])) || ($element['id'] != $like->entityId());
        });

        $doc['like']       = array_values($doc['like']);
        $doc['total_like'] = $liveVideo->total_like;

        $mostReactions     = $this->userMostReactions($liveVideo->user, $liveVideo);
        $mostReactions     = $mostReactions->toArray(request());

        $mostReactionsInformation = $this->getItemReactionAggregation($liveVideo->user, $liveVideo);

        if (is_array($mostReactions) && count($mostReactions)) {
            foreach ($mostReactions as &$mostReaction) {
                $mostReaction = new FireStoreObject($mostReaction);
            }
            unset($mostReaction);
        } else {
            $mostReactions = null;
        }

        if (count($mostReactionsInformation)) {
            foreach ($mostReactionsInformation as &$mostReactionsInformationItem) {
                $mostReactionsInformationItem = new FireStoreObject($mostReactionsInformationItem);
            }
            unset($mostReactionsInformationItem);
        } else {
            $mostReactionsInformation = null;
        }

        $doc['most_reactions'] = $mostReactions;

        $doc['most_reactions_information'] = $mostReactionsInformation;

        return app('firebase.firestore')->updateDocument(self::FIREBASE_LIKE_COLLECTION, $liveVideo->stream_key, $doc);
    }

    public function updateAssets(Model $liveVideo, array $playback = []): bool
    {
        $service     = $this->getServiceManager();
        $serviceName = $service->getDefaultServiceName();
        if ($serviceName != self::SERVICE_MUX) {
            return false;
        }
        $id = $liveVideo->entityId();

        if (empty($playback['asset_id']) && empty($playback['playback_ids'])) {
            PlaybackData::query()->where('live_id', $id)->delete();
            $this->getPlaybackDataRepository()->updatePlaybackData($id, [
                'playback_id' => $playback['playback_ids'][0]['id'],
                'privacy'     => 0,
            ]);
            $liveVideo->asset_id = $playback['asset_id'];
            $liveVideo->duration = (int) (Arr::get($playback, 'duration') ?? 0);
            $liveVideo->saveQuietly();

            return true;
        }

        $livestreamId  = $liveVideo->live_stream_id;
        $serviceDriver = $service->getDefaultServiceProvider();
        $liveStream    = $serviceDriver?->executeApi('live-streams/' . $livestreamId, 'GET', true);

        if (!empty($liveStream['recent_asset_ids'])) {
            $assetId = end($liveStream['recent_asset_ids']);
            $asset   = $serviceDriver->executeApi('assets/' . $assetId, 'GET', true);
            if (!empty($asset['playback_ids'])) {
                PlaybackData::query()->where('live_id', $id)->delete();
                $this->getPlaybackDataRepository()->updatePlaybackData($id, [
                    'playback_id' => $asset['playback_ids'][0]['id'],
                    'privacy'     => 0,
                ]);

                $liveVideo->asset_id = $assetId;
                $liveVideo->duration = (int) ($asset['duration'] ?? 0);
                $liveVideo->saveQuietly();

                app('events')->dispatch('livestreaming.updated_assets', [$liveVideo, $this->getThumbnailPlayback($liveVideo->entityId()), $this->getVideoPlayback($liveVideo->entityId())]);

                return true;
            }
        }

        return false;
    }

    public function getDuration(Model $liveVideo): string
    {
        $value = (int) $liveVideo->duration;

        if ($value <= 0) {
            return '';
        }

        $hour   = floor($value / 3600);
        $min    = floor(($value - $hour * 3600) / 60);
        $second = $value - $hour * 3600 - $min * 60;
        $result = [];

        if ($hour) {
            $result[] = str_pad($hour, 2, '0', STR_PAD_LEFT);
        }
        $result[] = str_pad($min, 2, '0', STR_PAD_LEFT);
        $result[] = str_pad($second, 2, '0', STR_PAD_LEFT);

        return implode(':', $result);
    }

    public function getTaggedFriends(ContractUser $context, Model $liveVideo): Collection
    {
        if (!app_active('metafox/friend')) {
            return new Collection([]);
        }

        if (!$liveVideo->isApproved()) {
            return UserEntity::getByIds($liveVideo->tagged_friends ?? []);
        } else {
            $taggedFriendQuery = $this->getTaggedFriendsTrait($liveVideo);
            if (!$taggedFriendQuery instanceof Builder) {
                return new Collection([]);
            }

            return $taggedFriendQuery->get(['user_entities.*']);
        }
    }

    public function validateLimitTime(ContractUser $context, string $streamId, ?Model $liveVideo = null, ?bool $warning = false, ?bool $mobileLive = false): void
    {
        $limitTime = (int) $context->getPermissionValue('live_video.limit_live_stream_time');
        $liveId    = $liveVideo?->id;
        $streamKey = $liveVideo?->stream_key;
        if ($limitTime === 0 || !$liveId) {
            return;
        }

        if ($mobileLive) {
            WarningLiveTimeLimit::dispatch($liveId)->delay(now()->addMinutes($limitTime - 4));
            DisableStreamKeyByLimit::dispatch($liveId, $streamId)->delay(now()->addMinutes($limitTime));
            $document = app('firebase.firestore')->getDocument(self::FIREBASE_COLLECTION, $streamKey);
            if ($document) {
                $document['end_date'] = now()->addMinutes($limitTime)->toIso8601String();
                app('firebase.firestore')->addDocument(self::FIREBASE_COLLECTION, $streamKey, $document);
            }

            return;
        }

        if ($warning) {
            WarningLiveTimeLimit::dispatch($liveId)->delay(now()->addMinutes($limitTime - 4));

            return;
        }
        DisableStreamKeyByLimit::dispatch($liveId, $streamId)->delay(now()->addMinutes($limitTime));
    }

    public function updateTagFriends(Model $liveVideo, array $tags): void
    {
        $oldTaggedFriends = app('events')->dispatch('friend.get_owner_tag_friends', [$liveVideo], true);

        // In case Friend app is not active
        if (null === $oldTaggedFriends) {
            return;
        }

        //If no containing any friends, delete all tagged friends from video
        if (!count($tags)) {
            app('events')->dispatch('friend.delete_item_tag_friend', [$liveVideo], true);

            return;
        }

        // Old friend ids that existed in database
        $oldFriendIds = $oldTaggedFriends
            ->pluck('owner_id')
            ->toArray();

        // Filter with new tagged friends
        $keptFriendIds = array_diff($tags, $oldFriendIds);

        // Filter deleted tagged friends
        $deletedFriendIds = array_diff($oldFriendIds, $tags);

        if (count($deletedFriendIds)) {
            app('events')->dispatch('friend.delete_item_tag_friend', [$liveVideo, $deletedFriendIds]);
        }

        $keptTaggedFriends = $this->handleKeptTaggedFriends($liveVideo, $tags, $keptFriendIds);

        if (!count($keptTaggedFriends)) {
            return;
        }

        $extra = $this->transformTaggedFriends($liveVideo->user, $liveVideo->user, $liveVideo->owner, $keptTaggedFriends);

        $keptTaggedFriends = Arr::get($extra, 'tagged_friends');

        if (!is_array($keptTaggedFriends) || !count($keptTaggedFriends)) {
            return;
        }

        app('events')->dispatch('friend.create_tag_friends', [$liveVideo->user, $liveVideo, $keptTaggedFriends], true);
    }

    protected function handleKeptTaggedFriends(Model $liveVideo, array $tags, array $keptFriendIds): array
    {
        $keptTaggedFriends = [];
        $exists            = [];

        foreach ($tags as $tag) {
            if (!in_array($tag, $keptFriendIds) && !Arr::has($exists, $tag)) {
                continue;
            }

            $friend = UserEntity::getById($tag)->detail;

            if (false === app('events')->dispatch('core.can_tag_friend', [$liveVideo?->owner, $friend], true)) {
                continue;
            }

            $keptTaggedFriends[] = [
                'friend_id' => $tag,
                'is_tag'    => 1,
                'px'        => 0,
                'py'        => 0,
            ];

            $exists[$tag] = true;
        }

        return $keptTaggedFriends;
    }

    /**
     * @inheritDoc
     */
    public function validateStreamKey(ContractUser $context, array $attributes): bool
    {
        return $this->getUserStreamKeyRepository()->getModel()->newQuery()
            ->where([
                'stream_key' => $attributes['stream_key'],
                'user_id'    => $context->entityId(),
                'user_type'  => $context->entityType(),
            ])
            ->exists();
    }

    /**
     * @inheritDoc
     */
    public function getLiveByStreamKey(ContractUser $context, array $attributes): int
    {
        $streamKey = $attributes['stream_key'];

        /** @var Model $liveVideo */
        $liveVideo = $this->getModel()->newQuery()
            ->where('stream_key', $streamKey)
            ->first();

        if (!$liveVideo) {
            return 0;
        }

        return $liveVideo->id;
    }
}
