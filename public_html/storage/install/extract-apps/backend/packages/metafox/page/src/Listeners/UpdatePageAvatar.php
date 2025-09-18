<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UpdatePageAvatar
{
    public function __construct(protected PageRepositoryInterface $repository) { }

    /**
     * @param User|null         $context
     * @param User              $owner
     * @param UploadedFile|null $image
     * @param string            $imageCrop
     * @return array<string,          mixed>
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(?User $context, User $owner, ?UploadedFile $image, string $imageCrop): array
    {
        if (!$owner instanceof Page) {
            return [];
        }

        policy_authorize(PagePolicy::class, 'uploadAvatar', $context, $owner);

        $params = [
            'image'      => $image,
            'image_crop' => $imageCrop,
        ];

        return $this->repository->updateAvatar($context, $owner->entityId(), Arr::except($params, ['photo_id']));
    }
}
