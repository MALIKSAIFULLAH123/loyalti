<?php

namespace MetaFox\Invite\Listeners;

use MetaFox\Platform\Facades\Settings;

class InviteRegistrationFieldRuleMessagesListener
{
    public function handle(): array
    {
        if (!Settings::get('invite.invite_only', false)) {
            return [];
        }

        return $this->messagesForRequired();
    }

    protected function messagesForRequired(): array
    {
        return [
            'invite_code.required' => __p('invite::validation.the_invite_code_is_required'),
        ];
    }
}
