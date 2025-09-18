<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use MetaFox\Poll\Models\Poll as Model;
use MetaFox\Poll\Policies\PollPolicy;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IntegrationCreatePollForm extends StatusCreatePollForm
{
    /**
     * Temporarily define parent item type.
     */
    public const PARENT_ITEM_TYPE = 'forum_thread';

    public function boot()
    {
        $context = user();

        $owner = $context;

        $ownerId = request()->get('owner_id');

        if (is_numeric($ownerId) && $ownerId > 0) {
            $owner = UserEntity::getById($ownerId)->detail;
        }

        policy_authorize(PollPolicy::class, 'create', $context, $owner);

        if (!$this->resource instanceof Model) {
            app('events')->dispatch('poll.integration.check_permission', [$context, $owner, self::PARENT_ITEM_TYPE], true);
        }
    }
}
