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
class MobileIntegrationCreatePollForm extends StatusCreatePollMobileForm
{
    /**
     * Temporarily define parent item type.
     */
    public const PARENT_ITEM_TYPE = 'forum_thread';

    public function boot()
    {
        $context = user();

        $owner = $context;

        $ownerId      = request()->get('owner_id');
        $this->isEdit = (bool)request()->get('is_edit');

        if (is_numeric($ownerId) && $ownerId > 0) {
            $owner = UserEntity::getById($ownerId)->detail;
        }

        policy_authorize(PollPolicy::class, 'create', $context, $owner);

        if (!$this->resource instanceof Model) {
            app('events')->dispatch('poll.integration.check_permission', [$context, $owner, self::PARENT_ITEM_TYPE], true);
        }
    }

    protected function prepare(): void
    {
        $minAnswers     = 2; //@todo: implement with setting
        $answersDefault = [];
        for ($i = 1; $i <= $minAnswers; $i++) {
            $answersDefault[] = [
                'answer' => '',
                'order'  => $i,
            ];
        }

        $this->title(__p('poll::phrase.new_poll_title'))
            ->submitAction('@poll/statusAddPoll')
            ->action('/poll')
            ->asPost()
            ->setValue([
                'poll_multiple' => 0,
                'enable_close'  => 0,
                'poll_public'   => 1,
                'poll_answers'  => $answersDefault,
                'poll_question' => '',
            ]);

        if ($this->isEdit) {
            $this->title(__p('poll::phrase.edit_poll_title'));
        }
    }
}
