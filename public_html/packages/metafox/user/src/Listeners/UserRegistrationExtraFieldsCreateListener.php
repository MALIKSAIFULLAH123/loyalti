<?php

namespace MetaFox\User\Listeners;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class UserRegistrationExtraFieldsCreateListener
{
    public function __construct(protected UserRepositoryInterface $userRepository) { }

    /**
     * @param User                 $user
     * @param array<string, mixed> $attributes
     * @return User|null
     */
    public function handle(User $user, array $attributes): ?User
    {
        if (!Settings::get('user.force_user_to_upload_on_sign_up', false)) {
            return null;
        }

        $imageCrop = Arr::get($attributes, 'user_profile.base64');
        $image     = $imageCrop ? upload()->convertBase64ToUploadedFile($imageCrop) : null;

        if (!$image instanceof UploadedFile) {
            return null;
        }

        return $this->uploadUserAvatar($user, $image, $imageCrop);
    }

    protected function uploadUserAvatar(User $user, UploadedFile $userProfile, string $imageCrop): User
    {
        $this->userRepository->createAvatarFromSignup($user, $userProfile, [
            'imageCrop' => $imageCrop,
        ]);

        return $user->refresh();
    }
}
