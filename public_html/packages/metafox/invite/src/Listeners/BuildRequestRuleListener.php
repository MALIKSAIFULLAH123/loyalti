<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Invite\Listeners;

use ArrayObject;
use MetaFox\Invite\Rules\InviteOnlyRule;
use MetaFox\Platform\Facades\Settings;

/**
 * Class BuildRequestRuleListener.
 * @ignore
 * @codeCoverageIgnore
 */
class BuildRequestRuleListener
{
    public function handle(ArrayObject $rules): void
    {
        if (!Settings::get('invite.invite_only', false)) {
            return;
        }

        $rules['invite_code'] = ['required', 'string', new InviteOnlyRule()];
    }
}
