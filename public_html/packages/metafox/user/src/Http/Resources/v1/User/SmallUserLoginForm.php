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
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Traits\UserLoginFormTrait;

/**
 * @driverName user.small_login
 * @preload    1
 */
class SmallUserLoginForm extends AbstractForm
{
    use UserLoginFormTrait;

    protected function prepare(): void
    {
        $this
            ->title('')
            ->testId('login form')
            ->noBreadcrumb(true)
            ->submitAction('@login')
            ->alertPreSubmitErrors(__p('user::validation.invalid_email_and_password'))
            ->action(url_utility()->makeApiUrl('user/login'))
            ->asPost();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal()->justifyContent('end');

        $basic->addFields(
            Builder::text('email')
                ->component(MetaFoxForm::TEXT)
                ->variant('outlined')
                ->label($this->getLoginFieldLabel())
                ->fullWidth(false)
                ->placeholder($this->getLoginFieldPlaceholder())
                ->marginDense()
                ->shrink()
                ->autoComplete('email')
                ->sizeSmall()
                ->noFeedback(false)
                ->showErrorTooltip(true)
                ->autoFocus(true),
            Builder::password('password')
                ->label(__p('user::phrase.password'))
                ->variant('outlined')
                ->fullWidth(false)
                ->marginDense()
                ->sizeSmall()
                ->shrink()
                ->autoComplete('password')
                ->placeholder(__p('user::phrase.password'))
                ->noFeedback(false)
                ->showErrorTooltip(true),
            Builder::checkbox('remember')
                ->checkedValue(true)
                ->fullWidth(false)
                ->label(__p('user::web.remember_me')),
            Captcha::getFormField('user.user_login', 'web', true),
        );

        $basic->addFields(
            Builder::submit('login')
                ->marginDense()
                ->type('submit')
                ->sizeMedium()
                ->label(__p('user::phrase.sign_in'))
                ->color('primary')
                ->variant('contained')
                ->fullWidth(false),
        );

        if (Settings::get('user.allow_user_registration') && !Settings::get('invite.invite_only', false)) {
            $basic->addFields(
                Builder::linkButton('register')
                    ->link('/register')
                    ->variant('link')
                    ->sizeMedium()
                    ->marginDense()
                    ->color('primary')
                    ->fullWidth(false)
                    ->label(__p('user::phrase.don_t_have_an_account')),
            );
        }
        $this->handleSocialLoginFields($basic);

    }

    /**
     * @param Section $section
     * @return void
     * @deprecated This method is a hot fix when disable the setting 'Require Login for Homepage Access'
     * TODO: Refactor this method after implementing signup with social account buttons on the registration form.
     */
    protected function handleSocialLoginFields(Section $section): void
    {
        $fieldResponses = array_filter(app('events')->dispatch('socialite.login_fields', ['web']) ?? []);
        if (empty($fieldResponses)) {
            return;
        }

        $socialLogin = Builder::section()
            ->sx([
                'display' => 'none',
            ])
            ->component('SocialButtons');

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
