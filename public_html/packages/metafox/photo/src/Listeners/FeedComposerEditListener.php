<?php

namespace MetaFox\Photo\Listeners;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;

class FeedComposerEditListener
{
    /**
     * @param PhotoGroupRepositoryInterface $groupRepository
     */
    public function __construct(protected PhotoGroupRepositoryInterface $groupRepository)
    {
    }

    /**
     * Photo set feed content won't apply to every single photo. DO NOT assign content to per photo.
     *
     * @param  User                 $user
     * @param  User                 $owner
     * @param  mixed                $resource
     * @param  array<string, mixed> $params
     * @return bool|array
     * @throws Exception
     */
    public function handle(User $user, User $owner, mixed $resource, array $params): ?array
    {
        if ($resource?->entityType() != PhotoGroup::ENTITY_TYPE) {
            return null;
        }

        if (!$resource instanceof PhotoGroup) {
            throw new ModelNotFoundException();
        }

        $params = $this->groupRepository->handleContent($params);

        $oldItems = $resource->items;

        $current = $oldItems->count();

        $oldExtra = $oldItems
            ->pluck('item_type', 'id')
            ->toArray();

        $newFiles = Arr::get($params, 'photo_files.new', []);

        $removeFiles = Arr::get($params, 'photo_files.remove', []);

        $editFiles = Arr::get($params, 'photo_files.edit', []);

        if (is_array($newFiles)) {
            $current += count($newFiles);
        }

        if (is_array($removeFiles)) {
            $current -= count($removeFiles);
        }

        if (0 >= $current) {
            return [
                'error_message' => __('validation.invalid'),
            ];
        }

        if ($current == 1) {
            if (is_array($newFiles)) {
                $newFiles = $this->groupRepository->forceContentForGlobalSearch($newFiles, $resource->content);
            }

            if (is_array($editFiles)) {
                $editFiles = $this->groupRepository->forceContentForGlobalSearch($editFiles, $resource->content);
            }
        }

        $this->groupRepository->updateGlobalSearchForSingleMedia(
            $resource,
            Arr::get($params, 'content'),
            $current,
            array_merge($editFiles, $removeFiles)
        );

        unset($params['photo_files']);

        $content = Arr::get($params, 'content');

        unset($params['content']);

        $success = $this->handlePhotoGroup(
            $user,
            $owner,
            $resource,
            $params,
            $content,
            $newFiles,
            $removeFiles,
            $editFiles
        );

        $resource->refresh();

        $phrase = null;

        if (count($newFiles)) {
            $phrase = 'add_new_item';
        }

        if (count($editFiles)) {
            $phrase = 'updated_item';
        }

        if (count($removeFiles)) {
            $phrase = 'removed_item';
        }

        $newExtra = $resource->items->pluck('item_type', 'id')->toArray();

        $oldPhrase = null;

        if (Arr::get($params, 'is_first_history')) {
            $oldPhrase = 'add_new_item';
        }

        return [
            'success' => $success,
            'phrase'  => [
                'old' => $oldPhrase,
                'new' => $phrase,
            ],
            'extra' => [
                'old' => $oldExtra,
                'new' => $newExtra,
            ],
        ];
    }

    /**
     * @param  User                 $user
     * @param  User                 $owner
     * @param  PhotoGroup           $group
     * @param  array<string, mixed> $params
     * @param  string|null          $content
     * @param  int[]                $newFiles
     * @param  int[]                $removeFiles
     * @param  int[]                $editFiles
     * @return bool
     * @throws Exception
     */
    protected function handlePhotoGroup(
        User $user,
        User $owner,
        PhotoGroup $group,
        array $params,
        ?string $content,
        array $newFiles = [],
        array $removeFiles = [],
        array $editFiles = []
    ): bool {
        $groupParams = array_merge($params, [
            'content' => $content,
        ]);

        $group->fill($groupParams);

        if ($group->privacy == MetaFoxPrivacy::CUSTOM) {
            $group->loadPrivacyListAttribute();

            $group->setPrivacyListAttribute(Arr::get($params, 'list', $group->getPrivacyListAttribute()));
        }

        $group->save();

        $params['album_id'] = $group->album_id;

        $params['group_id'] = $group->entityId();

        if (count($newFiles)) {
            $this->uploadMedias($user, $owner, $newFiles, $params);
        }

        if (count($removeFiles)) {
            $this->removeMedias($removeFiles);
        }

        if (count($editFiles)) {
            $this->updateMedias($user, $editFiles);
        }

        return true;
    }

    protected function updateMedias(User $user, array $files): void
    {
        foreach ($files as $file) {
            $update = [];

            if (Arr::has($file, 'text')) {
                $text = Arr::get($file, 'text');

                if (null === $text) {
                    $text = MetaFoxConstant::EMPTY_STRING;
                }

                Arr::set($update, 'text', $text);

                Arr::set($update, 'content', $text);
            }

            if (Arr::has($file, 'searchable_text')) {
                Arr::set($update, 'searchable_text', Arr::get($file, 'searchable_text'));
            }

            if (Arr::has($file, 'base64')) {
                Arr::set($update, 'base64', Arr::get($file, 'base64'));
            }

            if (Arr::has($file, 'tagged_friends')) {
                Arr::set($update, 'tagged_friends', Arr::get($file, 'tagged_friends'));
            }

            if (is_array($thumbnail = Arr::get($file, 'thumbnail'))) {
                Arr::set($update, 'thumbnail', $thumbnail);
            }

            app('events')->dispatch('photo.media_patch_update', [$user, $file['type'], $file['id'], $update], true);
        }
    }

    /**
     * @param User                 $user
     * @param User                 $owner
     * @param array<int, mixed>    $files
     * @param array<string, mixed> $params
     *
     * @return void
     * @throws Exception
     */
    protected function uploadMedias(User $user, User $owner, array $files, array $params): void
    {
        foreach ($files as $file) {
            $tempFile = upload()->getFile($file['id']);

            $update = $params;

            if (Arr::has($file, 'text')) {
                $text = Arr::get($file, 'text', '');
                Arr::set($update, 'text', $text);
                Arr::set($update, 'content', $text);
            }

            if (Arr::has($file, 'searchable_text')) {
                Arr::set($update, 'searchable_text', Arr::get($file, 'searchable_text'));
            }

            $photoTaggedFriends = Arr::get($file, 'tagged_friends') ?? [];

            Arr::set($update, 'tagged_friends', $photoTaggedFriends);

            if (is_array($thumbnail = Arr::get($file, 'thumbnail'))) {
                Arr::set($update, 'thumbnail', $thumbnail);
            }

            app('events')->dispatch(
                'photo.media_upload',
                [$user, $owner, $tempFile->item_type, $tempFile, $update],
                true
            );
        }
    }

    /**
     * @param  array<int , mixed> $removeMedias
     * @return void
     */
    protected function removeMedias(array $removeMedias)
    {
        foreach ($removeMedias as $media) {
            app('events')->dispatch('photo.media_remove', [$media['id'], $media['type']], true);
        }
    }
}
