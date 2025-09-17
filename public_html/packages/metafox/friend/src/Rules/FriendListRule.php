<?php

namespace MetaFox\Friend\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Friend\Repositories\Eloquent\FriendListRepository;
use MetaFox\Friend\Repositories\FriendListRepositoryInterface;

/**
 * Class FriendListRule.
 * @ignore
 * @codeCoverageIgnore
 */
class FriendListRule implements Rule
{
    private ?int $ownerId;

    public function __construct(int $ownerId = null)
    {
        $this->ownerId = $ownerId;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        /** @var FriendListRepository $friendListRepository */
        $friendListRepository = resolve(FriendListRepositoryInterface::class);

        $userId = $this->ownerId ?: Auth::id();

        foreach ($value as $listId) {
            /** @var FriendList $friendList */
            $friendList = $friendListRepository->getModel()->newQuery()->find($listId);

            if ($friendList == null) {
                return false;
            }

            if ($friendList->user_id != $userId) {
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return 'The :attribute is invalid.';
    }
}
