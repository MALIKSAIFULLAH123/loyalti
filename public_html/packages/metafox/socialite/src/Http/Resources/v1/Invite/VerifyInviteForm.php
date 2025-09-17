<?php

namespace MetaFox\Socialite\Http\Resources\v1\Invite;

use Illuminate\Http\Request;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\User\Models\SocialAccount as Model;
use MetaFox\User\Repositories\SocialAccountRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form VerifyInviteForm
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class VerifyInviteForm.
 * @property Model $resource
 */
class VerifyInviteForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('socialite::phrase.verify_invite_code'))
            ->description(__p('socialite::phrase.verify_invite_code_desc'))
            ->action(apiUrl('socialite.verifyInvite'))
            ->successAction('@loginByTokenAndRedirect')
            ->asPost()
            ->setValue([
                'hash' => $this->resource->hash,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        app('events')->dispatch('user.registration.invite_code_field.build', [$basic]);

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('socialite::phrase.verify')),
                Builder::cancelButton(),
            );
    }

    public function boot(Request $request, SocialAccountRepositoryInterface $repository): void
    {
        $this->resource = $repository->findSocialAccountByHash($request->get('hash'));
    }
}
