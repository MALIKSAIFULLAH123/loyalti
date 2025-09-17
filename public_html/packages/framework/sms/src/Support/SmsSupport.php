<?php

namespace MetaFox\Sms\Support;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Sms\Contracts\SmsSupportContracts;

class SmsSupport implements SmsSupportContracts
{
    public function validateConfiguration(): bool
    {
        if (app()->isLocal()) {
            return true;
        }

        $smsService = Settings::get('sms.default');
        return $smsService != 'log';
    }
}
