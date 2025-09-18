<?php

namespace MetaFox\Socialite\Http\Resources\v1\Invite;

use MetaFox\User\Models\SocialAccount as Model;

/**
 * --------------------------------------------------------------------------
 * Form VerifyInviteMobileForm
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class VerifyInviteMobileForm.
 * @property ?Model $resource
 */
class VerifyInviteMobileForm extends VerifyInviteForm
{
    protected function prepare(): void
    {
        $this->title(__p('socialite::phrase.verify_invite_code'))
            ->description(__p('socialite::phrase.verify_invite_code_desc'))
            ->action(apiUrl('socialite.verifyInvite'))
            ->submitAction('invite/verify')
            ->asPost()
            ->setValue([
                'hash' => $this->resource->hash,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        app('events')->dispatch('user.registration.invite_code_field.build', [$basic]);
    }
}
