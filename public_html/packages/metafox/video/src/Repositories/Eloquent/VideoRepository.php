<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use MetaFox\Core\Support\FileSystem\UploadFile;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\TempFileModel;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Traits\UserMorphTrait;
use MetaFox\Video\Jobs\FetchVideoFileThumbnailJob;
use MetaFox\Video\Jobs\VideoProcessingJob;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Notifications\VideoDoneProcessingNotification;
use MetaFox\Video\Notifications\VideoProcessedFailedNotification;
use MetaFox\Video\Policies\CategoryPolicy;
use MetaFox\Video\Policies\VideoPolicy;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use MetaFox\Video\Support\Browse\Scopes\Video\SortScope;
use MetaFox\Video\Support\Browse\Scopes\Video\ViewScope;

/**
 * Class VideoRepository.
 * @method Model getModel()
 * @method Model find($id, $columns = ['*'])
 * @method Model newModelInstance()
 *
 * @property Model $model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class VideoRepository extends AbstractRepository implements VideoRepositoryInterface
{
    use HasApprove;
    use HasFeatured;
    use HasSponsor;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    public function model(): string
    {
        return Model::class;
    }

    /**
     * @return CategoryRepositoryInterface
     */
    private function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function viewVideos(ContractUser $context, ContractUser $owner, array $attributes): Paginator
    {
        $limit     = $attributes['limit'];
        $profileId = $attributes['user_id'];
        $view      = $attributes['view'];

        $this->withUserMorphTypeActiveScope();
        if ($view == Browse::VIEW_FEATURE) {
            return $this->findFeature($limit);
        }

        if ($context->entityId() && $profileId == $context->entityId() && $view != Browse::VIEW_PENDING) {
            $attributes['view'] = $view = Browse::VIEW_MY;
        }

        if (Browse::VIEW_PENDING == $view) {
            if (Arr::get($attributes, 'user_id') == 0 || Arr::get($attributes, 'user_id') != $context->entityId()) {
                if ($context->isGuest() || !$context->hasPermissionTo('video.approve')) {
                    throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
                }
            }
        }

        $categoryId = Arr::get($attributes, 'category_id', 0);

        if ($categoryId > 0) {
            $category = $this->categoryRepository()->find($categoryId);

            policy_authorize(CategoryPolicy::class, 'viewActive', $context, $category);
        }
        $query     = $this->buildQueryViewVideos($context, $owner, $attributes);
        $relations = ['videoText', 'user', 'userEntity', 'categories'];

        /* @var \Illuminate\Pagination\Paginator $videoData */
        return $query
            ->with($relations)
            ->simplePaginate($limit, ['videos.*']);
    }

    public function viewVideo(ContractUser $context, int $id): Model
    {
        $video = $this
            ->withUserMorphTypeActiveScope()
            ->with(['videoText', 'user', 'userEntity', 'categories'])
            ->find($id);

        policy_authorize(VideoPolicy::class, 'view', $context, $video);

        return $video->refresh();
    }

    /**
     * @inheritdoc
     */
    public function createVideo(ContractUser $context, ContractUser $owner, array $attributes): Model
    {
        $this->checkCreatePermission($context, $owner, $attributes);

        app('events')->dispatch('video.pre_video_create', [$context, $attributes], true);

        $thumbnailLink   = Arr::get($attributes, 'thumbnail');
        $videoUrl        = Arr::get($attributes, 'video_url');
        $isVideoFileLink = $videoUrl && (Arr::get($attributes, 'is_file') ? true : false);

        if (Arr::has($attributes, 'text')) {
            $text = Arr::get($attributes, 'text');

            if (null === $text) {
                $text = MetaFoxConstant::EMPTY_STRING;
            }

            $attributes = array_merge($attributes, [
                'text' => $text,
            ]);
        }

        if (isset($attributes['content'])) {
            $attributes['content'] = $this->cleanContent($attributes['content']);
        }

        if ($videoUrl) {
            $thumbnail                   = $thumbnailLink ? $this->createThumbnailFromLink($context, $thumbnailLink) : null;
            $attributes['image_file_id'] = $thumbnail instanceof StorageFile ? $thumbnail->entityId() : null;
            $attributes['destination']   = $isVideoFileLink ? $videoUrl : null;
        }

        if (Arr::get($attributes, 'is_posted_from_feed', 0) && $context->entityId() != $owner->entityId()) {
            $privacy = UserPrivacy::getProfileSetting($owner->entityId(), 'feed:view_wall');
            Arr::set($attributes, 'privacy', $privacy);
        }

        $videoTempFile = null;
        $tempFile      = Arr::get($attributes, 'temp_file', 0);
        $jobExtra      = [];

        if ($tempFile > 0) {
            $attributes['in_process']    = Model::STATUS_PROCESS;
            $attributes['image_file_id'] = null;

            $videoTempFile = upload()->getFile($tempFile);
        }

        $thumbTempFile = Arr::get($attributes, 'thumb_temp_file', 0);
        if ($thumbTempFile > 0) {
            $thumbnailTemp                   = upload()->getFile($thumbTempFile);
            $attributes['thumbnail_file_id'] = $thumbnailTemp->entityId();

            // Delete temp file after done
            upload()->rollUp($thumbTempFile);
        }

        $attributes['title'] = $this->cleanTitle($attributes['title']);

        $attributes = array_merge($attributes, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'owner_id'    => $owner->entityId(),
            'owner_type'  => $owner->entityType(),
            'is_approved' => policy_check(VideoPolicy::class, 'autoApprove', $context, $owner),
        ]);

        /** @var Model $model */
        $model = $this->getModel()->newModelInstance();
        $model->fill($attributes);

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $model->setPrivacyListAttribute($attributes['list']);
        }

        $model->raw_processing_data = $jobExtra;
        $model->save();

        $this->updateProcessingData($model, $jobExtra);

        if (null !== $videoTempFile) {
            VideoProcessingJob::dispatch($videoTempFile, $model->entityId());
        }

        $model->refresh();
        $model->loadMissing('activity_feed');

        if ($isVideoFileLink) {
            FetchVideoFileThumbnailJob::dispatch($model->entityId(), $videoUrl);
        }

        return $model;
    }

    private function checkCreatePermission(ContractUser $context, ContractUser $owner, array $attributes): void
    {
        policy_authorize(VideoPolicy::class, 'create', $context, $owner);

        if (Arr::get($attributes, 'video_url')) {
            policy_authorize(VideoPolicy::class, 'shareVideoUrl', $context, $owner);

            return;
        }

        policy_authorize(VideoPolicy::class, 'uploadVideoFile', $context, $owner);
    }

    /**
     * @param ContractUser         $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Model
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function updateVideo(ContractUser $context, int $id, array $attributes): Model
    {
        $removeThumbnail = Arr::get($attributes, 'remove_thumbnail', 0);
        $thumbTempFile   = Arr::get($attributes, 'thumb_temp_file', 0);

        $video = $this
            ->withUserMorphTypeActiveScope()
            ->with(['group', 'album'])
            ->find($id);

        policy_authorize(VideoPolicy::class, 'update', $context, $video);

        if (isset($attributes['title'])) {
            $attributes['title'] = $this->cleanTitle($attributes['title']);
        }

        $attributes = $this->handleContent($attributes, 'text');

        $groupAttributes = null;

        if (null !== $video->group) {
            $groupAttributes = $attributes;

            $this->prepareDataForGroupUpdate($video->group, $video, $attributes, $groupAttributes);
        }

        if ($removeThumbnail) {
            $oldFile = $video->thumbnail_file_id;
            app('storage')->deleteFile($oldFile, null);
            $attributes['thumbnail_file_id'] = null;
        }

        if ($thumbTempFile > 0) {
            $tempFile = upload()->getFile($thumbTempFile);

            $attributes['thumbnail_file_id'] = $tempFile->id;

            // Delete temp file after done
            upload()->rollUp($thumbTempFile);
        }

        if (Arr::has($attributes, 'album')) {
            $attributes = $this->getPrivacyFromAlbum($attributes['album'], $attributes, $video);

            if ($this->shouldResetPhotoGroupPrivacy($attributes, $groupAttributes)) {
                $groupAttributes['privacy'] = $video->privacy;
            }
        }

        $this->setContent($attributes);

        $video->fill($attributes);

        if (Arr::get($attributes, 'privacy') == MetaFoxPrivacy::CUSTOM) {
            $video->setPrivacyListAttribute($attributes['list']);
        }

        $video->save();

        $video->refresh();

        //Update photo group
        if (is_array($groupAttributes)) {
            app('events')->dispatch('photo.update_photo_group', [$context, $video->group, $groupAttributes], true);
        }

        $this->updateExtra($video, $attributes);

        if (null === $video->group) {
            // Only update feed status when posting video in app, for other video with photo set => will handle by photo app
            app('events')->dispatch('activity.feed.mark_as_pending', [$video]);
        }

        return $video;
    }

    public function getPrivacyFromAlbum(?int $albumId, array $attributes, ?Model $video = null): array
    {
        if (null === $albumId) {
            return $this->getPrivacyForRemovingAlbum($video, $attributes);
        }

        if ($albumId == 0) {
            return $this->getPrivacyForRemovingAlbum($video, $attributes);
        }

        /** @var Album $album */
        $album = $this->getAlbumRepository()->find($albumId);

        return $this->getPrivacyForNormalAlbum($album, $attributes);
    }

    /**
     * @inerhitDoc
     */
    public function deleteVideo(ContractUser $context, int $id): bool
    {
        $resource = $this
            ->withUserMorphTypeActiveScope()
            ->with(['group'])
            ->find($id);

        if (!$resource->delete()) {
            return false;
        }

        if ($resource->group instanceof Content) {
            app('events')->dispatch('photo.group.update_search_for_first_media', [
                $resource->group,
                $resource->group->content,
                $resource->group->total_item > 1 ? $resource->group->total_item - 1 : 0,
            ], true);
        }

        return true;
    }

    /**
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    private function buildQueryViewVideos(ContractUser $context, ContractUser $owner, array $attributes): Builder
    {
        $sort       = $attributes['sort'];
        $sortType   = $attributes['sort_type'];
        $when       = $attributes['when'];
        $view       = $attributes['view'];
        $search     = $attributes['q'];
        $categoryId = $attributes['category_id'];
        $profileId  = $attributes['user_id'];
        $isFeatured = Arr::get($attributes, 'is_featured');

        // Scopes.
        $privacyScope = new PrivacyScope();
        $privacyScope->setUserId($context->entityId())
            ->setModerationPermissionName('video.moderate')
            ->setHasUserBlock(true);

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)->setView($view)->setProfileId($profileId);

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        if ($owner->entityId() != $context->entityId()) {
            $privacyScope->setOwnerId($owner->entityId());
            $viewScope->setIsViewOwner(true);

            if ($owner instanceof User) {
                $query->where('videos.user_id', $owner->entityId());
            }

            if (!policy_check(VideoPolicy::class, 'approve', $context, resolve(Model::class))) {
                $query->where('videos.is_approved', '=', 1);
            }

            $query->where(function (Builder $query) use ($context) {
                $query->where('videos.in_process', '!=', Model::STATUS_PROCESS)
                    ->orWhere('videos.user_id', $context->entityId());
            });
        }

        if ($categoryId > 0) {
            if (!is_array($categoryId)) {
                $categoryId = $this->categoryRepository()->getChildrenIds($categoryId);
            }

            $categoryScope = new CategoryScope();
            $categoryScope->setCategories($categoryId);
            $query = $query->addScope($categoryScope);
        }

        $query = $this->applyDisplayVideoSetting($query, $owner, $view);

        $query->addScope(new FeaturedScope($isFeatured));

        if (!$isFeatured) {
            $query->addScope($privacyScope);
        }

        return $query
            ->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($viewScope);
    }

    public function findFeature(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_featured', HasFeature::IS_FEATURED)
            ->where('is_approved', '=', 1)
            ->orderByDesc(HasFeature::FEATURED_AT_COLUMN)
            ->simplePaginate($limit);
    }

    public function findSponsor(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_sponsor', \MetaFox\Platform\Contracts\HasSponsor::IS_SPONSOR)
            ->where('is_approved', '=', 1)
            ->simplePaginate($limit);
    }

    /**
     * @param Builder      $query
     * @param ContractUser $owner
     * @param string       $view
     *
     * @return Builder
     * @throws AuthenticationException
     */
    private function applyDisplayVideoSetting(Builder $query, ContractUser $owner, string $view): Builder
    {
        if (in_array($view, [Browse::VIEW_MY, Browse::VIEW_MY_PENDING])) {
            return $query;
        }

        if (!$owner instanceof HasPrivacyMember) {
            $query->where('videos.owner_type', $owner->entityType());
        }

        return $query;
    }

    /**
     * @param string $assetId
     *
     * @return bool
     */
    public function deleteVideoByAssetId(string $assetId): bool
    {
        $video = $this->getModel()->newQuery()
            ->where('asset_id', $assetId)
            ->first();

        if (!$video instanceof Model) {
            return true;
        }

        return (bool) $this->delete($video->entityId());
    }

    /**
     * @param int                  $videoId
     * @param array<string, mixed> $attributes
     *
     * @return bool
     * @throws Exception
     */
    public function doneProcessVideo(int $videoId, array $attributes): bool
    {
        $video = $this->withUserMorphTypeActiveScope()->with(['user', 'owner'])->find($videoId);

        if (!$video instanceof Model) {
            return false;
        }

        $videoCreator = $video->user;

        try {
            $contextUserId = is_array($video->raw_processing_data) ? Arr::get($video->raw_processing_data, 'context_user_id') : 0;
            $videoCreator  = UserEntity::getById($contextUserId ?: 0)->detail;
        } catch (\Exception) {
            // Just silent it
        }

        if (is_array($video->raw_processing_data)) {
            $attributes = array_merge($attributes, $video->raw_processing_data);
        }

        // If video is done processing, no longer need this action
        if ($video->is_success) {
            return true;
        }

        $video->fill($attributes);

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $video->setPrivacyListAttribute($attributes['privacy_list']);
        }

        $video->save();

        $taggedFriends = Arr::get($attributes, 'tagged_friends', []);
        $isExistsGroup = $video->group_id > 0;

        if (!$isExistsGroup) {
            app('events')->dispatch('activity.feed.create_from_resource', [$video], true);
        }

        if (!empty($taggedFriends)) {
            $modelTag = $isExistsGroup ? $video->group : $video;

            app('events')->dispatch('activity.feed.create_tagged_friends', [$modelTag, $taggedFriends], true);
        }

        if (Arr::has($attributes, 'searchable_text')) {
            $this->updateGlobalSearch($video, Arr::get($attributes, 'searchable_text'));
        }

        //@TODO: Refactor this! Not a optimal solution
        if (!$videoCreator instanceof IsNotifiable) {
            return true;
        }

        if ($isExistsGroup) {
            $feed = $video?->group?->activity_feed;
            if ($feed) {
                app('events')->dispatch('feed.composer.notification', [$feed->userEntity, $feed->ownerEntity, $feed], true);
            }

            return $this->doneProcessingVideosInGroup($video->group_id);
        }

        if ($video->isApproved()) {
            app('events')->dispatch('notification.new_post_to_follower', [$video->user, $video->refresh()]);
        }

        // Notify creator that their video is ready
        Notification::send($videoCreator, new VideoDoneProcessingNotification($video));

        return true;
    }

    public function failedProcessVideo(array $params): bool
    {
        if (empty($params)) {
            return false;
        }

        $videoId = Arr::get($params, 'video_id', 0);
        $assetID = Arr::get($params, 'asset_id');

        if (!$videoId && !$assetID) {
            return false;
        }

        $video = $this->getModel()->newModelQuery()->find($videoId);
        if (!$video) {
            $video = $this->getVideoByAssetId($assetID ?: '');
        }

        if (!$video instanceof Model) {
            return false;
        }

        $video->updateQuietly(['in_process' => Model::STATUS_FAILED]);
        $this->handleDeletedNotify($video);

        $videoCreator = $video->user;
        Notification::send($videoCreator, new VideoProcessedFailedNotification($video));

        return true;
    }

    protected function handleDeletedNotify(Model $video): void
    {
        $model = match ($video->group_id > 0) {
            true  => $video->group,
            false => $video
        };

        app('events')->dispatch('notification.delete_notification_to_follower', [$model], true);
    }

    /**
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param TempFileModel        $tempFile
     * @param array<string, mixed> $params
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function tempFileToVideo(
        ContractUser  $context,
        ContractUser  $owner,
        TempFileModel $tempFile,
        array         $params = []
    ): Model
    {
        policy_authorize(VideoPolicy::class, 'create', $context, $owner);

        policy_authorize(VideoPolicy::class, 'uploadVideoFile', $context, $owner);

        $content = null;

        $extra = [
            'context_user_id' => $context->entityId(),
        ];

        $text = Arr::get($params, 'text', '');

        if (!empty($text)) {
            $content = $text;
        }

        unset($params['text']);

        $params = array_merge($params, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'owner_id'    => $owner->entityId(),
            'owner_type'  => $owner->entityType(),
            'title'       => parse_input()->clean(Arr::get($params, 'title', '')),
            'text'        => $text,
            'is_approved' => (int) policy_check(VideoPolicy::class, 'autoApprove', $context, $owner),
            'in_process'  => Model::STATUS_PROCESS,
            'image_path'  => null,
            'content'     => $content,
        ]);

        $thumbTempFile = (int) Arr::get($params, 'thumb_temp_file', 0);

        if ($thumbTempFile > 0) {
            $thumbnailTemp = upload()->getFile($thumbTempFile);

            $params['thumbnail_file_id'] = $thumbnailTemp->entityId();

            // Delete temp file after done
            upload()->rollUp($thumbTempFile);
        }

        /** @var Model $model */
        $model = $this->getModel()->newModelInstance();

        $model->fill($params);

        if (MetaFoxPrivacy::CUSTOM == $params['privacy']) {
            $model->setPrivacyListAttribute($params['list']);
        }

        if (Arr::has($params, 'searchable_text')) {
            Arr::set($extra, 'searchable_text', Arr::get($params, 'searchable_text'));
        }

        if (Arr::has($params, 'feed_tagged_friends')) {
            Arr::set($extra, 'tagged_friends', Arr::get($params, 'feed_tagged_friends'));
        }

        $model->raw_processing_data = $extra;
        $model->save();

        $this->updateProcessingData($model, $extra);

        $model->refresh();

        VideoProcessingJob::dispatch($tempFile, $model->entityId());

        return $model;
    }

    /**
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param Model                $video
     * @param TempFileModel        $tempFile
     * @param array<string, mixed> $params
     *
     * @return Model
     * @throws AuthorizationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function tempFileToExistVideo(
        ContractUser  $context,
        ContractUser  $owner,
        Model         $video,
        TempFileModel $tempFile,
        array         $params = []
    ): Model
    {
        policy_authorize(PhotoPolicy::class, 'update', $context, $video);
        $jobExtra = [];
        $params   = array_merge($params, [
            'is_approved' => (int) policy_check(VideoPolicy::class, 'autoApprove', $context, $owner),
            'image_path'  => Settings::get('video.video_in_processing_image'),
            'in_process'  => Model::STATUS_PROCESS,
            'group_id'    => 0,
        ]);

        if ($owner->hasPendingMode()) {
            $params['is_approved'] = 1;
        }

        $video->fill($params);

        if ($params['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $video->setPrivacyListAttribute($params['list']);
        }

        $video->raw_processing_data = $jobExtra;
        $video->save();

        $this->updateProcessingData($video, $jobExtra);

        $video->refresh();

        VideoProcessingJob::dispatch($tempFile, $video->entityId());

        return $video;
    }

    public function getVideosByGroupId(int $groupId): ?Collection
    {
        return $this->getModel()->newModelInstance()
            ->where([
                'group_id' => $groupId,
            ])
            ->get();
    }

    /**
     * @param int $groupId
     *
     * @return bool
     */
    public function doneProcessingVideosInGroup(int $groupId): bool
    {
        $inProcessVideos = $this->getModel()
            ->newModelQuery()
            ->where('group_id', '=', $groupId)
            ->where('in_process', '=', Model::STATUS_PROCESS)
            ->count();

        if ($inProcessVideos > 0) {
            return false;
        }

        app('events')->dispatch('photo.done_processing_photo_group_items', [$groupId]);

        return true;
    }

    protected function updateGlobalSearch(Model $video, ?string $text): ?bool
    {
        if (!$video instanceof HasGlobalSearch) {
            return false;
        }

        $searchable = $video->toSearchable();

        if (null === $searchable) {
            return false;
        }

        $searchable = array_merge($searchable, [
            'text' => $text,
        ]);

        return app('events')->dispatch(
            'search.update_search_text',
            [$video->entityType(), $video->entityId(), $searchable],
            true
        );
    }

    public function updatePatchVideo(int $id, array $attributes): bool
    {
        $video = $this
            ->withUserMorphTypeActiveScope()
            ->with(['group'])
            ->find($id);

        $attributes = $this->handleContent($attributes, 'text');

        if (Arr::has($attributes, 'text')) {
            Arr::set($attributes, 'content', Arr::get($attributes, 'text'));
            unset($attributes['text']);
        }

        if (null !== $video->group) {
            $this->prepareDataForGroupUpdate($video->group, $video, $attributes);
        }

        $thumbTempFile = Arr::get($attributes, 'thumb_temp_file', 0);

        if (Arr::get($attributes, 'remove_thumbnail', 0) && is_numeric($video->thumbnail_file_id)) {
            app('storage')->deleteFile($video->thumbnail_file_id, null);

            $attributes['thumbnail_file_id'] = null;
        }

        if ($thumbTempFile > 0) {
            $tempFile = upload()->getFile($thumbTempFile);

            $attributes['thumbnail_file_id'] = $tempFile->entityId();

            // Delete temp file after done
            upload()->rollUp($thumbTempFile);
        }

        $this->setContent($attributes);
        $video->fill($attributes);

        $video->save();

        $this->updateExtra($video, $attributes);

        return true;
    }

    protected function handleContent(array $attributes, string $field = 'content'): array
    {
        if (Arr::has($attributes, $field)) {
            $content = Arr::get($attributes, $field);

            if (null === $content) {
                Arr::set($attributes, $field, MetaFoxConstant::EMPTY_STRING);
            }
        }

        return $attributes;
    }

    protected function prepareDataForGroupUpdate(
        Content $group,
        Model   $video,
        array   &$attributes,
        ?array  &$groupAttributes = null
    ): void
    {
        if ($group->total_item != 1) {
            return;
        }

        if (null !== $video->content) {
            $this->setContent($attributes);

            if (is_array($groupAttributes)) {
                $this->unsetContent($groupAttributes);
            }

            return;
        }

        if (Arr::has($attributes, 'searchable_text')) {
            return;
        }

        //Update from Feed and we need to re-index searchable text for video to be sync with feed when searching global
        if (null === $groupAttributes) {
            Arr::set($attributes, 'searchable_text', $group->content);

            return;
        }

        $text = Arr::get($attributes, 'text');

        unset($attributes['text']);

        Arr::set($groupAttributes, 'content', $text);

        Arr::set($attributes, 'searchable_text', $text);
    }

    protected function unsetContent(array &$attributes)
    {
        if (!Arr::has($attributes, 'content')) {
            return;
        }

        unset($attributes['content']);
    }

    protected function setContent(array &$attributes): void
    {
        if (Arr::has($attributes, 'content')) {
            return;
        }

        if (Arr::has($attributes, 'text')) {
            Arr::set($attributes, 'content', Arr::get($attributes, 'text'));
        }
    }

    protected function updateExtra(Model $video, array $attributes)
    {
        // In case must not update content/text of photo but need to searching this photo like feed
        if (Arr::has($attributes, 'searchable_text')) {
            if (null === $video->getFeedContent()) {
                $this->updateGlobalSearch($video, Arr::get($attributes, 'searchable_text'));
            }
        }
    }

    protected function createThumbnailFromLink(ContractUser $user, ?string $url): ?StorageFile
    {
        $response = Http::get($url);
        if (!$response->ok()) {
            return null;
        }
        $tempFile = sprintf('%s.%s', tempnam(sys_get_temp_dir(), 'metafox'), File::extension($url) ?? 'jpg');
        file_put_contents($tempFile, $response->body());

        $uploadedFile = UploadFile::pathToUploadedFile($tempFile);

        if (!$uploadedFile instanceof UploadedFile) {
            return null;
        }

        return upload()
            ->setStorage('photo')
            ->setPath('video')
            ->setThumbSizes(['500'])
            ->setItemType('photo')
            ->setUser($user)
            ->storeFile($uploadedFile);
    }

    /**
     * @inheritDoc
     */
    public function getVideoByAssetId(string $assetId): ?Model
    {
        $video = $this->getModel()
            ->newModelQuery()
            ->where('asset_id', '=', $assetId)
            ->first();

        return $video instanceof Model ? $video : null;
    }

    public function increaseView(int $id): ?Model
    {
        $video = $this->getModel()->newModelQuery()->find($id);

        if (!$video instanceof Model) {
            return null;
        }

        $video->incrementTotalView();

        $video->refresh();

        return $video;
    }

    public function markVideoProcessing(int $id): bool
    {
        $video = $this->getModel()
            ->newModelQuery()
            ->where([
                'id'         => $id,
                'in_process' => Model::STATUS_PROCESS,
            ])
            ->first();

        if (!$video instanceof Model) {
            return false;
        }
        $rawProcessingData = $video->raw_processing_data;

        if ($rawProcessingData && Arr::get($rawProcessingData, 'is_processing_job')) {
            return false;
        }

        $video->updateQuietly([
            'raw_processing_data' => array_merge($rawProcessingData, [
                'is_processing_job' => 1,
            ]),
        ]);

        $video->refresh();

        return true;
    }

    /**
     * @return AlbumRepositoryInterface
     */
    private function getAlbumRepository(): AlbumRepositoryInterface
    {
        return resolve(AlbumRepositoryInterface::class);
    }

    protected function getPrivacyForRemovingAlbum(?Model $video, array $attributes): array
    {
        if (null === $video) {
            return $attributes;
        }

        // In case photo does not belong to any album before
        if ($video->album_id == 0) {
            return $attributes;
        }

        //In case remove album from photo which belongs to an album before, then this photo will be forced to belong to timeline album
        return array_merge($attributes, [
            'album_id'   => 0,
            'album_type' => Album::NORMAL_ALBUM,
            'privacy'    => $this->determinePrivacyForRemovingAlbum($video, $attributes),
        ]);
    }

    private function determinePrivacyForRemovingAlbum(Model $video, array $attributes): int
    {
        $privacy = Arr::get($attributes, 'privacy');

        if (null !== $privacy) {
            return $privacy;
        }

        if ($video->owner instanceof PostBy) {
            return $video->owner->getPrivacyPostBy();
        }

        // Reset to everyone privacy in case owner does not has own privacy
        return MetaFoxPrivacy::EVERYONE;
    }

    private function shouldResetPhotoGroupPrivacy(array $attributes, array|null $photoGroupAttributes): bool
    {
        if (!is_array($photoGroupAttributes)) {
            return false;
        }

        if (null === Arr::get($attributes, 'privacy')) {
            return false;
        }

        if (null === Arr::get($photoGroupAttributes, 'privacy')) {
            return false;
        }

        return Arr::get($attributes, 'privacy') != Arr::get($photoGroupAttributes, 'privacy');
    }

    protected function getPrivacyForNormalAlbum(Album $album, array $attributes): array
    {
        $attributes['privacy'] = $album->privacy;

        if ($album->privacy == MetaFoxPrivacy::CUSTOM) {
            $attributes['privacy'] = $album->privacy;

            $lists = PrivacyPolicy::getPrivacyItem($album);

            $listIds = [];

            if (is_array($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $attributes['list'] = $listIds;
        }

        return $attributes;
    }

    protected function updateProcessingData(Model $video, array $jobExtra): void
    {
        $video->updateQuietly([
            'raw_processing_data' => array_merge($jobExtra, [
                'privacy'      => $video->privacy,
                'privacy_list' => $video->getPrivacyListAttribute(),
            ]),
        ]);
    }
}
