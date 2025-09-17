<?php

namespace MetaFox\User\Http\Requests\v1\UserPassword;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\User\Models\PasswordResetToken as Token;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\PasswordResetTokenRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\UserPasswordController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'token'   => ['required', 'string', sprintf('exists:%s,value', Token::class)],
            'user_id' => ['required', 'numeric', 'exists:user_entities,id'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $user  = resolve(UserRepositoryInterface::class)->find(Arr::get($data, 'user_id'));
        $token = Arr::get($data, 'token');

        if (!resolve(PasswordResetTokenRepositoryInterface::class)->verifyToken($user, $token)) {
            abort(401, __p('user::phrase.incorrect_verification_code'));
        }

        $data['user'] = $user;

        return $data;
    }

    public function messages(): array
    {
        return [
            'token.exists'   => __p('user::phrase.incorrect_verification_code'),
            'token.required' => __p('user::phrase.the_verify_code_is_a_required_field'),
        ];
    }
}
