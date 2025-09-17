<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Rules\MetaFoxPasswordFormatRule;
use MetaFox\Yup\StringShape;
use MetaFox\Yup\Yup;

/**
 * Class EditPasswordMobileForm.
 *
 * @property User $resource
 * @driverName user.account.password
 * @driverType form-mobile
 */
class EditPasswordMobileForm extends AbstractForm
{
    /**
     * @throws AuthenticationException
     */
    public function boot(): void
    {
        $this->resource = user();
    }

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.change_password'))
            ->asPut()
            ->action(url_utility()->makeApiUrl('/account/setting'))
            ->submitAction('user/changePassword')
            ->setValue([]);

        if (request()->get('source') !== 'social_register') {
            $this->secondAction('changePassword/DONE');
        }

        if (version_compare(MetaFox::getApiVersion(), 'v1.12', '<')) {
            $this->confirm([
                'title'   => __p('user::phrase.account_setting_label.logout_other_devices'),
                'message' => __p('user::phrase.confirm_logout_other_device'),
            ]);
        }
    }

    protected function getPasswordValidate(string $field): StringShape
    {
        $passwordValidate = Yup::string()
            ->required(__p('user::phrase.field_password_is_a_required', [
                'field' => $field,
            ]))
            ->minLength(Settings::get('user.minimum_length_for_password', 8))
            ->maxLength(Settings::get('user.maximum_length_for_password', 30));

        $passwordRule = new MetaFoxPasswordFormatRule();

        foreach ($passwordRule->getFormRules() as $rule) {
            $passwordValidate->matches($rule, $passwordRule->message());
        }

        return $passwordValidate;
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $this->handleCurrentPasswordField($basic);
        $basic->addFields(
            Builder::password('new_password')
                ->label(__p('user::phrase.new'))
                ->variant('standard')
                ->marginNone()
                ->fullWidth(true)
                ->required()
                ->sizeSmall()
                ->autoComplete('password')
                ->placeholder(__p('user::phrase.new_password'))
                ->yup($this->getPasswordValidate(__p('user::phrase.new'))),
            Builder::password('new_password_confirmation')
                ->label(__p('core::phrase.confirm'))
                ->variant('standard')
                ->marginNone()
                ->fullWidth(true)
                ->required()
                ->sizeSmall()
                ->autoComplete('password')
                ->placeholder(__p('user::phrase.confirm_password'))
                ->yup($this->getPasswordValidate(__p('core::phrase.confirm'))),
        );
    }

    protected function handleCurrentPasswordField(Section $basic): void
    {
        if ($this->resource->getAuthPassword()) {
            $basic->addField(
                Builder::password('old_password')
                    ->autoComplete('off')
                    ->marginNormal()
                    ->label(__p('user::phrase.current'))
                    ->placeholder(__p('user::phrase.current_password'))
                    ->required()
                    ->yup(Yup::string()->required(__p('user::phrase.field_password_is_a_required', [
                        'field' => __p('user::phrase.current'),
                    ])))
            );
        }
    }
}
