<?php

namespace MetaFox\Contact\Http\Requests\v1\Contact;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Contact\Http\Controllers\Api\v1\ContactController::store
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
        $rules = [
            'category_id' => ['required', 'int', 'exists:contact_categories,id'],
            'full_name'   => ['required', 'string', 'between:1,255'],
            'subject'     => ['required', 'string', 'between:1,255'],
            'email'       => ['required', 'string', 'email', 'between:1,255'],
            'message'     => ['required', 'string'],
            'send_copy'   => ['sometimes', 'int', new AllowInRule([0, 1])],
        ];

        $captchaRules = Captcha::ruleOf('contact.contact');

        if (is_array($captchaRules)) {
            $rules['captcha'] = $captchaRules;
        }

        return $rules;
    }
}
