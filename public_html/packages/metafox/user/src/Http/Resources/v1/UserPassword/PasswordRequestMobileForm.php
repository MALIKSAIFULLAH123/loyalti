<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserPassword;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * @driverType form-mobile
 * @driverName user.forgot_password
 * @preload    1
 */
class PasswordRequestMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('user::phrase.forgot_password'))
            ->description(__p('user::phrase.enter_email_search_account'))
            ->action(apiUrl('user.password.request.method', ['resolution' => 'mobile']))
            ->asPost();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('email')
                ->label(__p('user::web.email_or_phone'))
                ->placeholder(__p('user::web.email_or_phone'))
                ->description(__p('user::phrase.forgot_email_help'))
                ->required()
                ->validateAction('user.user.validateEmailOrPhoneNumber')
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field')),
                ),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('user::phrase.request_new_password'))->sizeMedium(),
            );
    }

    protected function initializeFlatten(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('email')
                ->label(__p('user::web.email_or_phone'))
                ->placeholder(__p('user::web.email_or_phone'))
                ->description(__p('user::phrase.forgot_email_help'))
                ->required()
                ->variant('standard-outlined')
                ->validateAction('user.user.validateEmailOrPhoneNumber')
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field')),
                ),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('user::phrase.request_new_password'))->sizeMedium(),
            );
    }
}
