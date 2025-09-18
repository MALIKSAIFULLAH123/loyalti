<?php

namespace MetaFox\Invite\Listeners;

use MetaFox\Invite\Rules\InviteOnlyRule;
use MetaFox\Platform\Facades\Settings;

class InviteRegistrationFieldRulesListener
{
    public function handle(\ArrayObject $rules): void
    {
        $rules['code'] = ['sometimes', 'string', 'nullable'];

        if (!Settings::get('invite.invite_only', false)) {
            $rules['invite_code'] = ['sometimes', 'string', 'nullable', new InviteOnlyRule()];
            return;
        }

        $rules['invite_code'] = ['required', 'string', new InviteOnlyRule()];
    }
}
