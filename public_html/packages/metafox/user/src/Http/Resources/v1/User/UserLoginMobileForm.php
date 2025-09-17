<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Support\Arr;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\User\Traits\UserLoginFormTrait;
use MetaFox\Yup\Yup;

/**
 * Class UserLoginMobileForm.
 * @driverName user.login
 * @driverType form-mobile
 * @preload    1
 */
class UserLoginMobileForm extends AbstractForm
{
    use UserLoginFormTrait;

    protected function prepare(): void
    {
        $this->title('')
            ->noBreadcrumb(true)
            ->submitAction('@login')
            ->action('user/login')
            ->asPost()
            ->acceptPageParams(['returnUrl'])
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('email')
                ->label($this->getLoginFieldLabel())
                ->marginDense()
                ->required()
                ->placeholder($this->getLoginFieldPlaceholder())
                ->autoFocus(true)
                ->fullWidth()
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                ),
            Builder::password('password')
                ->label(__p('user::phrase.password'))
                ->marginDense()
                ->required()
                ->fullWidth()
                ->placeholder(__p('user::phrase.enter_your_password'))
                ->yup(
                    Yup::string()
                        ->required(__p('validation.field_is_a_required_field', ['field' => __p('user::phrase.password')]))
                ),
            Captcha::getFormField('user.user_login', 'mobile', true),
        );

        $subActions = Builder::row('sub_actions');

        $subActions->setForm($this);

        $subActions->addFields(
            Builder::linkButton('changeAddress')
                ->margin('none')
                ->actionName('navigate')
                ->link('/site_address')
                ->fullWidth()
                ->label(__p('core::phrase.change_address')),
            Builder::linkButton('forgotPassword')
                ->link('/forgot_password')
                ->actionName('navigate')
                ->margin('none')
                ->fullWidth()
                ->label(__p('user::phrase.forgot_password?')),
        );

        $basic->addFields(
            Builder::submit('login')
                ->marginNormal()
                ->sizeNormal()
                ->setAttribute('paddingBottom', 'none')
                ->label(__p('user::phrase.sign_in'))
                ->color('primary')
                ->fullWidth(),
            $subActions,
            Builder::hidden('returnUrl'),
        );

        $this->handleSocialLoginFields($basic);
    }


    protected function initializeFlatten(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('email')
                ->label($this->getLoginFieldLabel())
                ->marginDense()
                ->variant('standard-outlined')
                ->required()
                ->placeholder($this->getLoginFieldPlaceholder())
                ->autoFocus(true)
                ->fullWidth()
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                ),
            Builder::password('password')
                ->label(__p('user::phrase.password'))
                ->marginDense()
                ->variant('standard-outlined')
                ->required()
                ->fullWidth()
                ->placeholder(__p('user::phrase.enter_your_password'))
                ->yup(
                    Yup::string()
                        ->required(__p('validation.field_is_a_required_field', ['field' => __p('user::phrase.password')]))
                ),
            Captcha::getFormField('user.user_login', 'mobile', true),
        );

        $subActions = Builder::row('sub_actions');

        $subActions->setForm($this);

        $subActions->addFields(
            Builder::linkButton('changeAddress')
                ->margin('none')
                ->actionName('navigate')
                ->variant('standard-outlined')
                ->link('/site_address')
                ->fullWidth()
                ->label(__p('core::phrase.change_address')),
            Builder::linkButton('forgotPassword')
                ->link('/forgot_password')
                ->actionName('navigate')
                ->margin('none')
                ->variant('standard-outlined')
                ->fullWidth()
                ->label(__p('user::phrase.forgot_password?')),
        );

        $basic->addFields(
            Builder::submit('login')
                ->marginNormal()
                ->sizeNormal()
                ->setAttribute('paddingBottom', 'none')
                ->label(__p('user::phrase.sign_in'))
                ->color('primary')
                ->fullWidth(),
            $subActions,
            Builder::hidden('returnUrl'),
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
        $fieldResponses = array_filter(app('events')->dispatch('socialite.login_fields', ['mobile']) ?? []);
        if (empty($fieldResponses)) {
            return;
        }

        $socialLogin = Builder::section()
            ->component('SocialButtons');

        foreach ($fieldResponses as $response) {
            $socialLogin->setAttribute('justifyContent', 'space-around');

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
