<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Rules\PrivacyRule;

/**
 * Class UpdateRequest.
 */
class UpdatePrivacyRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'privacy' => ['required', new PrivacyRule()],
        ];

        return $rules;
    }

    /**
     * @throws AuthenticationException
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['privacy'])) {
            // @todo feed.default_privacy_setting.
            $data['privacy'] = MetaFoxPrivacy::EVERYONE;
        }

        $data['list'] = [];

        if (is_array($data['privacy'])) {
            $data['list'] = $data['privacy'];
            $data['privacy'] = MetaFoxPrivacy::CUSTOM;
        }

        return $data;
    }
}
