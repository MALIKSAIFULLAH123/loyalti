<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserPassword;

use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * @preload 0
 */
class PasswordRequestForm extends AbstractForm
{
    public function boot(): void
    {
        if (Settings::get('user.shorter_reset_password_routine')) {
            $this->submitAction('user/forgotPassword');
        }
    }

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.forgot_password'))
            ->description(__p('user::phrase.enter_email_search_account'))
            ->action(apiUrl('user.password.request.method', ['resolution' => 'web']))
            ->asPost();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('email')
                ->label(__p('user::web.email_or_phone'))
                ->placeholder(__p('user::web.email_or_phone'))
                ->required()
                ->autoFocus()
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                ),
            Captcha::getFormField('user.forgot_password')
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->sizeMedium()
                    ->label(__p('user::phrase.request_new_password')),
            );
    }
}
