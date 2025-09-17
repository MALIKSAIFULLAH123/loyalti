<?php

namespace MetaFox\Sms\Support\Traits;

use MetaFox\Platform\MetaFoxConstant;

/**
 * @deprecated Remove in 5.2.0
 */
trait PhoneRegexTrait
{
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        return preg_match('/' . MetaFoxConstant::PHONE_NUMBER_REGEX . '/', $phoneNumber);
    }
}
