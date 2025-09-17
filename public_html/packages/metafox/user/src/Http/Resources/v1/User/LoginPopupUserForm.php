<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Support\Arr;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Traits\UserLoginFormTrait;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class LoginPopupUserForm.
 *
 * @property ?Model $resource
 * @preload 1
 */
class LoginPopupUserForm extends AbstractForm
{
    use UserLoginFormTrait;

    protected function prepare(): void
    {
        $this->noBreadcrumb(true)
            ->submitAction('@login')
            ->action(url_utility()->makeApiUrl('user/login'))
            ->acceptPageParams(['returnUrl'])
            ->asPost();
    }

    protected function initialize(): void
    {
        $this->getSectionHeader();

        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('email')
                ->component(MetaFoxForm::TEXT)
                ->variant('outlined')
                ->label($this->getLoginFieldLabel())
                ->required()
                ->shrink()
                ->fullWidth(true)
                ->placeholder($this->getLoginFieldPlaceholder())
                ->marginNormal()
                ->autoComplete('email')
                ->autoFocus(true)
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                ),
            Builder::password('password')
                ->label(__p('user::phrase.password'))
                ->variant('outlined')
                ->required()
                ->fullWidth(true)
                ->shrink()
                ->marginNormal()
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
                ->margin('none')
                ->fullWidth(true)
                ->label(__p('user::phrase.forgot_password?')),
        );

        $this->getRegisterField($basic);
        $this->getReturnUrlField($basic);

        $bottom = $this->addSection(
            Builder::section('bottom')
                ->variant('horizontal')
        );

        $this->handleSocialLoginFields($bottom);
    }

    protected function getSectionHeader(): void
    {
        $header = $this->addSection(
            Builder::section('header')
        );

        $header->addFields(
            Builder::typography('form_header')
                ->plainText(__p('user::phrase.sign_in'))
                ->variant('h3')
                ->sx([
                    'justifyContent' => 'center',
                    'display'        => 'flex',
                ])
        );
    }

    protected function getRegisterField(Section $basic): void
    {
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
    }

    protected function getReturnUrlField(Section $basic): void
    {
        $basic->addField(
            Builder::hidden('returnUrl'),
        );
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

        $section->addField(Builder::typography('social_login')
            ->setAttribute('class', 'typoSigInSocialite')
            ->plainText(__p('user::phrase.or_sign_in_using')));

        $socialLogin = Builder::section()
            ->component('SocialButtons');

        foreach ($fieldResponses as $response) {
            $socialLogin->addFields(...$response);
        }

        // recalculate the total buttons
        $elements = Arr::get($socialLogin->toArray(), 'elements', []);
        $socialLogin->setAttribute('totalButton', count($elements));

        $section->addFields($socialLogin);
    }
}
