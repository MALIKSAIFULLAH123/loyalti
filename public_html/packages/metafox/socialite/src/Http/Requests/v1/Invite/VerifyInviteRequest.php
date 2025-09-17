<?php

namespace MetaFox\Socialite\Http\Requests\v1\Invite;

use ArrayObject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;

/**
 * Class VerifyInviteRequest.
 */
class VerifyInviteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = new ArrayObject([
            'hash' => ['required', 'string', 'exists:social_accounts,hash'],
        ]);

        app('events')->dispatch('invite.request_rule.build', [$rules]);

        $rules = $rules->getArrayCopy();

        $this->validateInviteAppVersion($rules);

        return $rules;
    }

    protected function validateInviteAppVersion(array $rules): void
    {
        if (!Settings::get('invite.invite_only', false)) {
            return;
        }

        if (Arr::has($rules, 'invite_code')) {
            return;
        }

        throw new \RuntimeException('Please ensure the Invite app is upgraded to version 5.1.12 to continue using it.');
    }
}
