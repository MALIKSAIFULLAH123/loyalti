<?php

namespace MetaFox\Photo\Listeners;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class FeedScheduleEditListener
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
     * @param  mixed                $resource
     * @param  array<string, mixed> $params
     * @return bool|array
     * @throws Exception
     */
    public function handle(User $user, mixed $resource, array $params): ?array
    {
        if ($resource?->post_type != PhotoGroup::ENTITY_TYPE) {
            return null;
        }

        $removeFiles = Arr::get($params, 'photo_files.remove', []);

        if (count($removeFiles)) {
            foreach ($removeFiles as $media) {
                app('storage')->rolLDown($media['id']);
            }
            unset($params['photo_files']['remove']);
        }
        $newFiles = Arr::get($params, 'photo_files.new', []);
        if (count($newFiles)) {
            foreach ($newFiles as &$media) {
                $base64 = Arr::get($media, 'base64');
                $id     = Arr::get($media, 'id');
                if ($base64) {
                    $media['id'] = $this->handleBase64($user, $id, $base64);
                    unset($media['base64']);
                }
            }
            $params['photo_files']['new'] = $newFiles;
        }

        return [
            'success'    => true,
            'new_params' => $params,
        ];
    }

    public function handleBase64(User $user, int $id, string $base64): ?int
    {
        $uploadFile = upload()->convertBase64ToUploadedFile($base64);

        if (!$uploadFile instanceof UploadedFile) {
            return null;
        }

        $file = upload()
            ->setStorage('photo')
            ->setPath('photo')
            ->setThumbSizes(ResizeImage::SIZE)
            ->setItemType('photo')
            ->setUser($user)
            ->storeFile($uploadFile);

        if (null === $file) {
            return null;
        }

        app('storage')->rolLDown($id);

        return $file->entityId();
    }
}
