<?php

namespace MetaFox\Sms\Support\Traits;

use Propaganistas\LaravelPhone\PhoneNumber;

trait PhoneNumberTrait
{
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        $phone = new PhoneNumber($phoneNumber);

        return $phone->isValid() && preg_replace('/\s+/', '', ($phoneNumber)) === $phone->formatE164();
    }

    public function formatNumber(string $phoneNumber): string
    {
        if (!$this->validatePhoneNumber($phoneNumber)) {
            return '';
        }

        $phone = new PhoneNumber($phoneNumber);

        return $phone->formatE164();
    }
}
