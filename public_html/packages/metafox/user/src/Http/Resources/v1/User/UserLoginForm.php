<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Support\Arr;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Html\Hidden;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Traits\UserLoginFormTrait;
use MetaFox\Yup\Yup;

/**
 * Class UserLoginForm.
 * @driverName user.login
 * @preload    1
 */
class UserLoginForm extends AbstractForm
{
    use UserLoginFormTrait;

    protected function prepare(): void
    {
        $this
            ->title('')
            ->noBreadcrumb(true)
            ->submitAction('@login')
            ->action(url_utility()->makeApiUrl('user/login'))
            ->asPost()
            ->acceptPageParams(['returnUrl'])
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('email')
                ->component(MetaFoxForm::TEXT)
                ->variant('outlined')
                ->label($this->getLoginFieldLabel())
                ->required()
                ->fullWidth(true)
                ->shrink()
                ->placeholder($this->getLoginFieldPlaceholder())
                ->marginNormal()
                ->autoComplete('email')
                ->autoFocus(true)
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::password('password')
                ->label(__p('user::phrase.password'))
                ->variant('outlined')
                ->required()
                ->fullWidth(true)
                ->marginNormal()
                ->shrink()
                ->autoComplete('password')
                ->placeholder(__p('user::phrase.enter_your_password'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::checkbox('remember')
                ->checkedValue(true)
                ->label(__p('user::web.remember_me')),
            Captcha::getFormField('user.user_login', 'web', true),
        );

        app('events')->dispatch('user.login.extra_fields.build', [$basic]);

        $basic->addFields(
            Builder::submit('login')
                ->marginNormal()
                ->type('submit')
                ->sizeLarge()
                ->label(__p('user::phrase.sign_in'))
                ->color('primary')
                ->variant('contained')
                ->fullWidth(true),
            Builder::linkButton('forgotPassword')
                ->link('/user/password/request')
                ->variant('link')
                ->sizeMedium()
                ->color('primary')
                ->marginNone()
                ->fullWidth(true)
                ->label(__p('user::phrase.forgot_password?')),
        );

        if (Settings::get('user.allow_user_registration')) {
            $basic->addField(
                Builder::linkButton('register')
                    ->link('/register')
                    ->sizeLarge()
                    ->variant('outlined')
                    ->marginDense()
                    ->color('primary')
                    ->fullWidth(true)
                    ->label(__p('user::phrase.don_t_have_an_account'))
                    ->sx(['pt' => 3]),
            );
        }

        $basic->addField(
            new Hidden(['name' => 'returnUrl'])
        );

        $this->handleSocialLoginFields($basic);
    }

    /**
     * @param Section $section
     *
     * @return void
     */
    protected function handleSocialLoginFields(Section $section): void
    {
        $fieldResponses = array_filter(app('events')->dispatch('socialite.login_fields', ['web']) ?? []);
        if (empty($fieldResponses)) {
            return;
        }

        $socialLogin = Builder::section()
            ->component('SocialButtons');

        $section->addField(Builder::typography('social_login_typo')
            ->setAttribute('class', 'typoSigInSocialite')
            ->plainText(__p('user::phrase.or_sign_in_using')));

        foreach ($fieldResponses as $response) {
            $socialLogin->setForm($this);
            $socialLogin->name('social_login');

            $socialLogin->addFields(...$response);
        }

        // recalculate the total buttons
        $elements = Arr::get($socialLogin->toArray(), 'elements', []);
        $socialLogin->setAttribute('totalButton', count($elements));

        $section->addFields($socialLogin);
    }
}
