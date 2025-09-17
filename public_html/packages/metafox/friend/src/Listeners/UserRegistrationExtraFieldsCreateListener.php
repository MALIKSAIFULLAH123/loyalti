<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;
use MetaFox\Friend\Repositories\FriendRequestRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use Metafox\User\Models\User;
use Prettus\Validator\Exceptions\ValidatorException;
use MetaFox\User\Models\UserEntity as UserEntityModel;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class UserRegistrationExtraFieldsCreateListener
{
    public function __construct(protected FriendRequestRepositoryInterface $requestRepository, protected FriendRepositoryInterface $friendRepository)
    {
    }

    /**
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function handle($model): void
    {
        if (!$model instanceof User) {
            return;
        }

        if (!$model->isApproved() || !$model->hasVerified()) {
            return;
        }

        $ownerIds = Settings::get('user.on_signup_new_friend');

        if (!is_array($ownerIds) || !count($ownerIds)) {
            return;
        }

        $owners = UserEntityModel::query()
            ->with(['detail'])
            ->whereIn('id', $ownerIds)
            ->get();

        if (!$owners->count()) {
            return;
        }

        $owners->each(function (UserEntityModel $owner) use ($model) {
            $detail = $owner->detail;

            if (null === $detail) {
                return;
            }

            if (!$detail->hasAdminRole() && !$detail->hasStaffRole()) {
                return;
            }

            if ($this->friendRepository->isFriend($detail->entityId(), $model->entityId())) {
                return;
            }

            $this->requestRepository->sendRequest($detail, $model);

            $this->friendRepository->addFriend($detail, $model, true);
        });
    }
}
