<?php

namespace MetaFox\Invite\Http\Resources\v1\Invite;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Invite\Models\Invite as Model;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreInviteMobileForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreInviteMobileForm extends StoreInviteForm
{
    protected function prepare(): void
    {
        $this->title(__p('invite::phrase.invite_your_friends'))
            ->action('/invite')
            ->updateResponseValue()
            ->asPost()
            ->secondAction('invite/redirectManage')
            ->setValue([
                'link_invite' => $this->linkInvite,
                'invite_code' => $this->code,
            ]);
    }

    protected function initialize(): void
    {
        $this->addHeader(['showRightHeader' => !$this->isDisable()])
            ->component('FormHeader');

        $minContact = 1;

        $this->addBasic()
            ->addFields(
                Builder::text('link_invite')
                    ->label(__p('invite::phrase.link_invite'))
                    ->disabled()
                    ->setAttribute('hasCopy', true),
                Builder::text('invite_code')
                    ->label(__p('user::phrase.invite_code'))
                    ->disabled()
                    ->setAttribute('hasCopy', true)
                    ->setAttribute('hasRefresh', true),
                $this->getInfoInviteField(),
                Builder::tags('recipients')
                    ->label(__p('invite::phrase.contacts'))
                    ->placeholder(__p('invite::phrase.add_emails_or_phone_numbers'))
                    ->description(__p('invite::phrase.separate_multiple_emails_phone_numbers_with_commas_or_enter'))
                    ->required()
                    ->yup(
                        Yup::array()
                            ->required()
                            ->min($minContact)
                            ->setError('min', __p(
                                'validation.min.array',
                                ['attribute' => __p('invite::phrase.contacts'), 'min' => $minContact]
                            ))
                    ),
                Builder::textArea('message')
                    ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                    ->label(__p('invite::phrase.add_personal_message'))
                    ->placeholder(__p('invite::phrase.write_message'))
                    ->yup(Yup::string()
                        ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)),
            );
    }

    /**
     * @return AbstractField
     * @throws AuthenticationException
     */
    protected function getInfoInviteField(): AbstractField
    {
        $makeFriend = (int) Settings::get('invite.make_invited_users_friends_with_their_host');
        $typography = __p('invite::phrase.invite_your_friends_to_site', [
            'value'      => config('app.name'),
            'makeFriend' => $makeFriend,
        ]);

        if ($this->isDisable()) {
            $typography = __p('invite::phrase.you_must_will_to_wait_minutes_before_can_send_another_invitation', [
                'value' => $this->timeUnlock,
            ]);
        }

        if (version_compare(MetaFox::getApiVersion(), 'v1.13', '<')) {
            return Builder::typography()
                ->plainText($typography);
        }

        $field = Builder::alert('alert_info_invite')->message($typography);

        return $this->isDisable() ? $field->asWarning() : $field->asInfo();
    }
}
