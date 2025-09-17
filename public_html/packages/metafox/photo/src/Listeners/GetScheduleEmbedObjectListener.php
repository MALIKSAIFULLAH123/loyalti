<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Photo\Policies\PhotoTagFriendPolicy;
use MetaFox\Photo\Support\ResourcePermission;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\User\Models\UserEntity;

class GetScheduleEmbedObjectListener
{
    public function handle(Entity $schedule, ?bool $toForm = false)
    {
        if (!$schedule->data) {
            return null;
        }

        $data = $schedule->data;

        if (!in_array(Arr::get($data, 'post_type'), ['photo', 'photo_set'])) {
            return null;
        }

        $schedule->data = $this->transformToEmbed($data, $toForm);

        return true;
    }

    /**
     * @param  array $data
     * @param  bool  $toForm
     * @return array
     */
    private function transformToEmbed(array $data, bool $toForm): array
    {
        $photoFiles = Arr::get($data, 'photo_files.new');

        if ($photoFiles) {
            $data['photos']         = [];
            $data['remain_photo']   = count($photoFiles);
            $data['schedule_type']  = 'photo';
            $storage                = app('storage');
            /** @var PhotoPolicy $policy */
            $policy = PolicyGate::getPolicyFor(Photo::class);
            /* @var PhotoTagFriendPolicy $policyTag */
            foreach ($photoFiles as $file) {
                $storageFile = $storage->getFile($file['id'] ?? null);
                if (!$storageFile instanceof StorageFile) {
                    continue;
                }

                $attributes = [
                    'type'        => $file['type'],
                    'id'          => $file['id'],
                    'status'      => $file['status'],
                    'text'        => Arr::get($file, 'text'),
                    'width'       => $storageFile->width,
                    'height'      => $storageFile->height,
                    'image'       => $file['type'] == 'photo' ? $storageFile->images : null,
                    'destination' => $file['type'] == 'photo' ? null : $storageFile->image,
                    'extra'       => [
                        'can_tag_friend' => $policy->tagFriend(user()),
                    ],
                    'tagged_friends' => $this->getTaggedFriends($file),
                ];

                if (is_array($thumbnail = Arr::get($file, 'thumbnail'))) {
                    if (is_numeric($tempFile = Arr::get($thumbnail, 'id')) && $tempFile > 0) {
                        Arr::set($thumbnail, 'image', app('storage')->getUrls((int) $tempFile));
                    }

                    Arr::set($attributes, 'thumbnail', $thumbnail);
                }

                $data['photos'][] = $attributes;
            }

            unset($data['photo_files']);
        }

        return $data;
    }

    private function getTaggedFriends(array $item): array
    {
        $taggedFriends = Arr::get($item, 'tagged_friends', []);
        if ($taggedFriends) {
            $friendIds = array_map(function ($friend) {
                return Arr::get($friend, 'user_id', 0);
            }, $taggedFriends);

            $users   = UserEntity::query()->whereIn('id', $friendIds)->get();
            $friends = [];
            foreach ($users as $user) {
                $friends[$user->entityId()] = $user;
            }

            $taggedFriends = array_map(function ($friend) use ($friends) {
                $user = Arr::get($friends, $friend['user_id']);
                if ($user) {
                    $friend['user'] = ResourceGate::user($user);
                }
                $friend['extra'] = [
                    ResourcePermission::CAN_REMOVE_TAGGED_FRIEND => true,
                ];

                return $friend;
            }, $taggedFriends);
        }

        return $taggedFriends;
    }
}
