<?php

namespace MetaFox\Mail\Support;

use MetaFox\Mail\Contracts\MailSupportContracts;
use MetaFox\Platform\Facades\Settings;

class MailSupport implements MailSupportContracts
{
    public function validateConfiguration(): bool
    {
        if (app()->isLocal()) {
            return true;
        }

        $smsService = Settings::get('mail.default');

        return !in_array($smsService, ['log', 'array']);
    }
}
