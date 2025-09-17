<?php

namespace MetaFox\Friend\Http\Resources\v1\Friend;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\User\Support\Browse\Traits\User\ExtraTrait;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserBlocked;
use MetaFox\User\Traits\UserStatisticTrait;

/**
 * Class SuggestionSimple.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SuggestionSimple extends FriendSimple
{
    use ExtraTrait;
    use UserStatisticTrait;

    /**
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $data    = parent::toArray($request);
        $context = user();
        Arr::forget($data, 'verification_required');

        return array_merge($data, [
            'module_name'   => 'friend',
            'resource_name' => 'friend_suggestion',
            'is_blocked'    => UserBlocked::isBlocked($this->resource, $context),
            'friendship'    => $this->resource ? UserFacade::getFriendship($context, $this->resource) : null,
            'extra'         => $this->getExtra(),
            'joined'        => $this->resource?->created_at, // formatted to ISO-8601 as v4
            'statistic'     => $this->getStatistic(),
        ]);
    }
}
