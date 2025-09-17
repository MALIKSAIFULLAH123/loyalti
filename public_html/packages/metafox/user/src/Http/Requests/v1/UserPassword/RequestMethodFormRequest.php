<?php

namespace MetaFox\User\Http\Requests\v1\UserPassword;

class RequestMethodFormRequest extends RequestMethodRequest
{
    protected function getCaptchaRule(): array
    {
        return [];
    }
}
