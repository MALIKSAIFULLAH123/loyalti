<?php

namespace MetaFox\User\Http\Resources\v1\User;

use MetaFox\Form\Mobile\Builder;
use MetaFox\User\Http\Resources\v1\User\Admin\DenyUserForm as DenyUserFormAdmin;
use MetaFox\User\Models\User as Model;
use MetaFox\Yup\Yup;

/**
 * Class DenyUserForm.
 *
 * @property Model $resource
 * @driverType form
 * @driverName user.deny_user
 */
class DenyUserMobileForm extends DenyUserFormAdmin
{
    protected function prepare(): void
    {
        $this->action("user/deny/{$this->userId}")
            ->asPatch()
            ->setValue([]);
    }

    public function initialize(): void
    {
        $this->title(__p('user::phrase.deny_user'));

        $basic = $this->addBasic();
        if (empty($this->user->getPhoneNumberForVerification()) && empty($this->user->getEmailForVerification())) {
            $basic->addFields(
                Builder::alert()
                    ->message(__p('user::phrase.you_are_about_to_deny_user'))
                    ->asInfo(),
            );
            return;
        }

        $basic->addFields(
            Builder::alert()
                ->message(__p('user::mail.send_email_description_when_deny_user', ['username' => $this->userName]))
                ->asInfo(),
        );

        $this->buildMailSection($basic);
        $this->buildSmsSection($basic);
    }

    protected function buildSmsSection($basic): void
    {
        if (empty($this->user->getPhoneNumberForVerification())) {
            return;
        }

        $basic->addFields(
            Builder::checkbox('has_send_sms')
                ->label(__p('user::phrase.send_via_sms')),
        );
        $smsSection = $this->addSection(['name' => 'sms_section'])
            ->label(__p('user::phrase.send_via_sms'))
            ->showWhen([
                'truthy', 'has_send_sms',
            ]);

        $smsSection->addFields(
            Builder::textArea('sms_message')
                ->required()
                ->label(__p('core::phrase.message'))
                ->placeholder(__p('core::phrase.message'))
                ->yup(
                    Yup::string()->nullable()
                        ->when(
                            Yup::when('has_send_sms')
                                ->is(1)
                                ->then(Yup::string()->required(__p('validation.required', ['attribute' => __p('core::phrase.message')])))
                        )
                ),
        );
    }

    protected function buildMailSection($basic): void
    {
        if (empty($this->user->getEmailForVerification())) {
            return;
        }

        $basic->addFields(
            Builder::checkbox('has_send_mail')
                ->label(__p('user::phrase.send_via_mail')),
        );

        $mailSection = $this->addSection(['name' => 'mail_section'])
            ->label(__p('user::phrase.send_via_mail'))
            ->showWhen([
                'truthy', 'has_send_mail',
            ]);

        $mailSection->addFields(
            Builder::text('subject')
                ->requiredWhen(['truthy', 'has_send_mail',])
                ->label(__p('user::mail.subject'))
                ->placeholder(__p('user::mail.subject'))
                ->yup(
                    Yup::string()->nullable()
                        ->when(
                            Yup::when('has_send_mail')
                                ->is(1)
                                ->then(Yup::string()->required(__p('validation.required', ['attribute' => __p('user::mail.subject')])))
                        )
                ),
            Builder::textArea('message')
                ->required()
                ->label(__p('core::phrase.message'))
                ->placeholder(__p('core::phrase.message'))
                ->yup(
                    Yup::string()->nullable()
                        ->when(
                            Yup::when('has_send_mail')
                                ->is(1)
                                ->then(Yup::string()->required(__p('validation.required', ['attribute' => __p('core::phrase.message')])))
                        )
                ),
        );
    }
}
