<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

/**
 * @preload 1
 */
class UserLoginForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title('')
            ->noBreadcrumb(true)
            ->submitAction('@login')
            ->action('admincp/login')
            ->testId('loginForm')
            ->asPost();
    }

    protected function initialize(): void
    {
        // overwrite header content
        $this->addSection([
            'name'      => 'formHeader',
            'component' => 'AdminAuthHeader',
        ]);

        $basic = $this->addBasic();

        $basic->addFields(
            Builder::email('email')
                ->label(__p('core::phrase.email_address'))
                ->autoComplete('email')
                ->autoFocus()
                ->fullWidth()
                ->shrink()
                ->marginNormal()
                ->sizeMedium()
                ->yup(
                    Yup::string()
                        ->email(__p('validation.invalid_email_address'))
                        ->required()
                ),
            Builder::password('password')
                ->label(__p('core::phrase.password'))
                ->autoComplete('password')
                ->sizeMedium()
                ->shrink()
                ->fullWidth()
                ->marginNormal()
                ->yup(
                    Yup::string()->required()
                ),
        );

        app('events')->dispatch('user.admin_login.extra_fields.build', [$basic]);

        $basic->addFields(
            Builder::checkbox('remember')
                ->checkedValue(true)
                ->label(__p('user::web.remember_me')),
            Builder::submit('login')
                ->type('submit')
                ->label(__p('user::phrase.login'))
                ->variant('contained')
                ->color('primary')
                ->fullWidth()
                ->className('mt1'),
            Builder::linkButton('forgot_password')
                ->sizeSmall()
                ->link('/user/password/request')
                ->variant('link')
                ->target('_blank')
                ->fullWidth(true)
                ->label(__p('user::phrase.forgot_password?'))
                ->className('mt1'),
        );
    }
}
