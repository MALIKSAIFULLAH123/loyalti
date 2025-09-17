<?php

namespace MetaFox\User\Http\Requests\v1\UserPassword;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Ban\Rules\BanEmailRule;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Rules\ExistsInEmailOrPhoneNumberRule;
use MetaFox\User\Rules\EmailOrPhoneNumberRule;

class RequestMethodRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge([
            ...$this->getEmailOrPhoneNumberRule(),
        ], $this->getCaptchaRule());
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data['user'] = resolve(UserRepositoryInterface::class)->findUserByEmailOrPhoneNumber(Arr::get($data, 'email'));

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'email.exists' => __p('user::validation.cannot_find_this_user'),
        ];
    }

    protected function getEmailOrPhoneNumberRule(): array
    {
        $banEmailRule = new BanEmailRule();
        $banEmailRule->setFailedMessage(__p('user::phrase.global_ban_message'));

        return [
            'email' => [
                'required',
                'string',
                new EmailOrPhoneNumberRule(),
                new ExistsInEmailOrPhoneNumberRule(),
                $banEmailRule,
            ],
        ];
    }

    protected function getCaptchaRuleName(): string
    {
        return 'user.forgot_password';
    }

    protected function getCaptchaRule(): array
    {
        return ['captcha' => Captcha::ruleOf($this->getCaptchaRuleName())];
    }
}
