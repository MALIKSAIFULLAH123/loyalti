<?php

namespace MetaFox\User\Support\Facades;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Facade;
use MetaFox\User\Support\UserAuth as SupportUserAuth;

/**
 * Class UserAuth.
 *
 * @method static mixed login(array $params = [])
 * @method static mixed authorize(FormRequest $request)
 * @mixin \MetaFox\User\Support\UserAuth
 */
class UserAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SupportUserAuth::class;
    }
}
