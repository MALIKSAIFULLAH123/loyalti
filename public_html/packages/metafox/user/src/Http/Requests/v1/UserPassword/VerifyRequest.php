<?php

namespace MetaFox\User\Http\Requests\v1\UserPassword;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use Illuminate\Support\Arr;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserPasswordController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 */
class VerifyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'request_method' => ['required', 'string', new AllowInRule(['mail', 'sms'])],
            'user_id'        => ['required', 'numeric', 'exists:user_entities,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data['user'] = resolve(UserRepositoryInterface::class)->find(Arr::get($data, 'user_id'));

        return $data;
    }
}
