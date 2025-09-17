<?php

namespace MetaFox\Sms\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Sms\Contracts\ServiceInterface;
use MetaFox\Sms\Rules\PhoneNumberRule;
use MetaFox\Sms\Support\Traits\PhoneRegexTrait;

abstract class AbstractService implements ServiceInterface
{
    use PhoneRegexTrait;

    /**
     * @var array<mixed>
     */
    private array $config = [];

    public function getConfig(?string $key)
    {
        if (!empty($key)) {
            return Arr::get($this->config, $key);
        }

        return $this->config;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }
}
