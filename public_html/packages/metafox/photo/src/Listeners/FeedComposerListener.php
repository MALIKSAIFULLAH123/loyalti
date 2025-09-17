<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Photo\Policies\PhotoGroupPolicy;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasTimelineAlbum;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;

class FeedComposerListener
{
    private PhotoRepositoryInterface      $repository;
    private PhotoGroupRepositoryInterface $photoGroupRepository;

    /**
     * @param PhotoRepositoryInterface      $repository
     * @param PhotoGroupRepositoryInterface $photoGroupRepository
     */
    public function __construct(
        PhotoRepositoryInterface      $repository,
        PhotoGroupRepositoryInterface $photoGroupRepository
    )
    {
        $this->repository           = $repository;
        $this->photoGroupRepository = $photoGroupRepository;
    }

    /**
     * Photo set feed content won't apply to every single photo. DO NOT assign content to per photo.
     *
     * @param User                 $user
     * @param User                 $owner
     * @param string               $postType
     * @param array<string, mixed> $params
     * @return array|null
     */
    public function handle(User $user, User $owner, string $postType, array $params): ?array
    {
        if ($postType != PhotoGroup::FEED_POST_TYPE) {
            return null;
        }

        if (false === policy_check(PhotoGroupPolicy::class, 'hasCreateFeed', $owner, $postType)) {
            return [
                'error_message' => __('validation.no_permission'),
            ];
        }

        $files = Arr::get($params, 'photo_files.new');

        if (!is_array($files) || !count($files)) {
            return [
                'error_message' => __('validation.invalid'),
            ];
        }

        Arr::set($params, 'files', $files);

        unset($params['photo_files']);

        try {
            $feedId = $this->handleComposer($user, $owner, $params);
        } catch (\Throwable $exception) {
            $feedId = 0;
        }

        return match ($feedId) {
            0       => ['error_message' => __('validation.no_permission')],
            -1      => ['is_processing' => true, 'message' => __p('activity::phrase.post_in_process_message')],
            default => ['id' => $feedId]
        };
    }

    protected function handleComposer(User $user, User $owner, array $params): int
    {
        if ($owner instanceof HasTimelineAlbum) {
            $params['album_id'] = $this->repository->getAlbum($user, $owner, Album::TIMELINE_ALBUM)->entityId();
        }

        $groupParams = array_merge($params, [
            'content'     => Arr::get($params, 'content', ''),
            'user_id'     => $user->entityId(),
            'user_type'   => $user->entityType(),
            'owner_id'    => $owner->entityId(),
            'owner_type'  => $owner->entityType(),
            'is_approved' => 0,
        ]);

        $group = new PhotoGroup();

        $group->fill($groupParams);

        if ($group->privacy == MetaFoxPrivacy::CUSTOM) {
            $group->setPrivacyListAttribute($params['list']);
        }

        $group->save();

        $group->refresh();

        $params['group_id'] = $group->entityId();

        $group->loadMissing('activity_feed');

        $content = Arr::get($params, 'content');

        if (!$group->activity_feed) {
            Arr::set($params, 'feed_tagged_friends', Arr::get($params, 'tagged_friends', []));
        }

        $uploaded = $this->uploadMedias($user, $owner, $params, $content);

        if (!count($uploaded)) {
            return 0;
        }

        if ($group->privacy == MetaFoxPrivacy::CUSTOM) {
            $group->setPrivacyListAttribute($params['list']);
        }

        // Create feed after all items are created
        app('events')->dispatch('activity.feed.create_from_resource', [$group, 'feed'], true);

        // Update photo group status after all of its items are
        $this->photoGroupRepository->updateApprovedStatus($group);

        $group->refresh();

        LoadReduce::flush();

        if (!$group->activity_feed) {
            return -1;
        }

        app('events')->dispatch(
            'activity.notify.approved_new_post_in_owner',
            [$group->activity_feed, $group->activity_feed->owner],
            true
        );

        if ($group->total_item > 1) {
            $group->items()->each(function (PhotoGroupItem $item) use ($group) {
                $this->updateGlobalSearchForSingleMedia($item->detail);
            });
        }

        return $group->activity_feed->entityId();
    }

    protected function updateGlobalSearchForSingleMedia(Content $detail): void
    {
        if (!$detail instanceof HasGlobalSearch) {
            return;
        }

        $searchable = $detail->toSearchable();

        if (null === $searchable) {
            return;
        }

        app('events')->dispatch('search.update_search_text', [$detail->entityType(), $detail->entityId(), $searchable], true);
    }

    /**
     * @param User        $user
     * @param User        $owner
     * @param array       $params
     * @param string|null $groupContentgroup
     * @return array
     */
    protected function uploadMedias(User $user, User $owner, array $params, ?string $groupContent): array
    {
        $medias = [];

        $files = Arr::get($params, 'files', []);

        if (!$this->canUploadMedia($files)) {
            return $medias;
        }

        if (MetaFoxConstant::EMPTY_STRING == $groupContent) {
            $groupContent = null;
        }

        if (null !== $groupContent) {
            $files = $this->photoGroupRepository->forceContentForGlobalSearch($files, $groupContent);
        }

        Arr::set($params, 'has_multiple_item', count($files) > 1);

        foreach ($files as $file) {
            $tempFile = upload()->getFile($file['id']);

            $update = $params;

            if (Arr::has($file, 'text')) {
                $text = Arr::get($file, 'text', '');
                Arr::set($update, 'text', $text);
            }

            if (Arr::has($file, 'searchable_text')) {
                Arr::set($update, 'searchable_text', Arr::get($file, 'searchable_text'));
            }

            if (Arr::has($file, 'tagged_friends')) {
                Arr::set($update, 'tagged_friends', Arr::get($file, 'tagged_friends'));
            }

            if (is_array($thumbnail = Arr::get($file, 'thumbnail'))) {
                Arr::set($update, 'thumbnail', $thumbnail);
            }

            /** @var Media|null $content */
            $content = app('events')->dispatch(
                'photo.media_upload',
                [$user, $owner, $tempFile->item_type, $tempFile, $update],
                true
            );

            if (!$content instanceof Media) {
                return [];
            }

            $medias[] = $content;
        }

        if (count($medias) == 0) {
            return [];
        }

        return $medias;
    }

    protected function canUploadMedia(array $files): bool
    {
        return true;
    }
}
