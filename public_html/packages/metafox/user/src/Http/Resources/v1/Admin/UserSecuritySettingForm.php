<?php

namespace MetaFox\User\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

class UserSecuritySettingForm extends AdminSettingForm
{
    protected function prepare(): void
    {
        $vars = [
            'user.minimum_length_for_password',
            'user.maximum_length_for_password',
            'user.required_strong_password',
            'user.force_frequent_password_change',
            'user.force_frequent_password_change_period',
            'user.force_password_history_check',
            'user.number_of_password_history',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this
            ->title(__p('user::phrase.security_settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/user'))
            ->asPost()
            ->setValue($value);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('user.minimum_length_for_password')
                ->asNumber()
                ->label(__p('user::admin.minimum_length_for_password_label'))
                ->description(__p('user::admin.minimum_length_for_password_desc'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->unint()
                        ->min(4)
                        ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))
                ),
            Builder::text('user.maximum_length_for_password')
                ->asNumber()
                ->label(__p('user::admin.maximum_length_for_password_label'))
                ->description(__p('user::admin.maximum_length_for_password_desc'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->unint()
                        ->max(255)
                        ->when(
                            Yup::when('minimum_length_for_password')
                                ->is('$exists')
                                ->then(
                                    Yup::number()
                                        ->min(['ref' => 'minimum_length_for_password'])
                                        ->setError('min', __p('validation.minimum_length_description_with_ref'))
                                )
                        )
                        ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}'])),
                ),
            Builder::switch('user.required_strong_password')
                ->label(__p('user::admin.required_strong_password_label'))
                ->description(__p('user::admin.required_strong_password_desc')),
            Builder::switch('user.force_frequent_password_change')
                ->label(__p('user::admin.force_frequent_password_change_label'))
                ->description(__p('user::admin.force_frequent_password_change_desc')),
            Builder::text('user.force_frequent_password_change_period')
                ->asNumber()
                ->required()
                ->label(__p('user::admin.force_frequent_password_change_period_label'))
                ->description(__p('user::admin.force_frequent_password_change_period_desc'))
                ->yup(
                    Yup::number()
                        ->unint()
                        ->required()
                        ->min(1)
                        ->setError('min', __p('validation.minimum_length_description_with_ref'))
                        ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))
                        ->setError('required', __p('validation.required', ['attribute' => '${path}']))
                ),
            Builder::switch('user.force_password_history_check')
                ->label(__p('user::admin.force_password_history_check_label'))
                ->description(__p('user::admin.force_password_history_check_desc')),
            Builder::text('user.number_of_password_history')
                ->asNumber()
                ->required()
                ->label(__p('user::admin.number_of_password_history_label'))
                ->description(__p('user::admin.number_of_password_history_desc'))
                ->yup(
                    Yup::number()
                        ->unint()
                        ->required()
                        ->min(1)
                        ->max(30)
                        ->setError('min', __p('validation.minimum_length_description_with_ref'))
                        ->setError('max', __p('validation.maximum_length_description_with_ref'))
                        ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))
                        ->setError('required', __p('validation.required', ['attribute' => '${path}']))
                ),
        );

        $this->addDefaultFooter(true);
    }
}
