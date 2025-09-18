<?php

namespace MetaFox\Newsletter\Http\Requests\v1\Newsletter\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\ResourceTextRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Newsletter\Http\Controllers\Api\v1\NewsletterAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'archive'          => ['sometimes', 'boolean'],
            'override_privacy' => ['sometimes', 'boolean'],
            'channel_mail'     => ['required_without:channel_sms', 'numeric'],
            'channel_sms'      => ['required_without:channel_mail', 'numeric'],
            'roles'            => ['nullable', 'array'],
            'roles.*'          => ['required_with:user_roles', 'numeric', 'exists:auth_roles,id'],
            'countries'        => ['sometimes', 'nullable', 'array'],
            'countries.*'      => ['string', 'exists:core_countries,country_iso'],
            'genders'          => ['sometimes', 'nullable', 'array'],
            'genders.*'        => ['numeric', new ExistIfGreaterThanZero('exists:user_gender,id')],
            'age_from'         => ['sometimes', 'nullable', 'numeric'],
            'age_to'           => ['sometimes', 'nullable', 'numeric'],
            'round'            => ['required', 'numeric', 'max:1000'],
            'subject'          => ['required', 'array', new TranslatableTextRule(false)],
            'text'             => ['sometimes', 'nullable', 'array', new TranslatableTextRule(true)],
            'text_html'        => ['sometimes', 'nullable', 'array', new TranslatableTextRule(true)],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $channelMail = Arr::pull($data, 'channel_mail');
        if ($channelMail) {
            Arr::set($data, 'channels.mail', $channelMail);
        }

        $channelSms = Arr::pull($data, 'channel_sms');
        if ($channelSms) {
            Arr::set($data, 'channels.sms', $channelSms);
        }

        Arr::set($data, 'subject', Language::extractPhraseData('subject', $data));
        Arr::set($data, 'text', Language::extractPhraseData('text', $data));
        Arr::set($data, 'text_html', Language::extractPhraseData('text_html', $data));

        return array_merge($data, $this->validationTextFields($data));
    }

    public function messages()
    {
        return [];
    }

    protected function validationTextFields($data): array
    {
        $default = Language::getDefaultLocaleId();

        $validator = Validator::make([
            'channel_mail' => Arr::get($data, 'channels.mail'),
            'channel_sms'  => Arr::get($data, 'channels.sms'),
            'text_html'    => Arr::get($data, 'text_html'),
            'text'         => Arr::get($data, 'text'),
        ], [
            'channel_mail'       => ['required_without:channel_sms', 'nullable', 'numeric'],
            'channel_sms'        => ['required_without:channel_mail', 'nullable', 'numeric'],
            "text_html.$default" => ['required_if:channel_mail,1', 'string'],
            "text.$default"      => ['required_if:channel_sms,1', 'string'],
            "text_html.*"        => [new ResourceTextRule(true)],
            "text.*"             => [new ResourceTextRule(false)],
        ], [
            "text_html.$default.required_if" => __p('newsletter::validation.required_if', [
                'attribute' => __p('newsletter::phrase.html_content'),
                'other'     => __p('mail::phrase.mail'),
            ]),
            "text.$default.required_if"      => __p('newsletter::validation.required_if', [
                'attribute' => __p('newsletter::phrase.text_content'),
                'other'     => __p('sms::phrase.sms'),
            ]),
        ]);

        return $validator->validated();
    }
}
