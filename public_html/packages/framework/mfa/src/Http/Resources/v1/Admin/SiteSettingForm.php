<?php

namespace MetaFox\Mfa\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Form\Builder;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'mfa';
        $vars   = [
            'mfa.confirm_password',
            'mfa.resend_verification_delay_time',
            'mfa.verify_code_timeout',
            'mfa.brute_force_attempts_count',
            'mfa.brute_force_cool_down',
            'mfa.enforce_mfa',
            'mfa.enforce_mfa_targets',
            'mfa.enforce_mfa_timeout',
            'mfa.enforce_mfa_roles',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::switch('mfa.confirm_password')
                    ->label(__p('mfa::phrase.confirm_password_label'))
                    ->description(__p('mfa::phrase.confirm_password_desc')),
                Builder::text('mfa.resend_verification_delay_time')
                    ->label(__p('mfa::phrase.resend_verification_delay_time_label'))
                    ->description(__p('mfa::phrase.resend_verification_delay_time_desc'))
                    ->required()
                    ->yup(Yup::number()->required()->int()->min(1)),
                Builder::text('mfa.verify_code_timeout')
                    ->label(__p('mfa::phrase.verify_code_timeout_label'))
                    ->description(__p('mfa::phrase.verify_code_timeout_desc'))
                    ->required()
                    ->yup(Yup::number()->required()->int()->min(1)),
                Builder::text('mfa.brute_force_attempts_count')
                    ->label(__p('mfa::phrase.force_attempts_count_label'))
                    ->description(__p('mfa::phrase.force_attempts_count_desc'))
                    ->required()
                    ->yup(
                        Yup::number()
                            ->required()
                    ),
                Builder::text('mfa.brute_force_cool_down')
                    ->label(__p('mfa::phrase.force_cool_down_label'))
                    ->description(__p('mfa::phrase.force_cool_down_desc', ['name' => __p('mfa::phrase.force_attempts_count_label')]))
                    ->required()
                    ->yup(
                        Yup::number()
                            ->required()
                    ),
            );

        $this->addEnforceMFASection();

        $this->addDefaultFooter(true);
    }

    private function addEnforceMFASection()
    {
        $applicableRoles   = resolve(RoleRepositoryInterface::class)->getRoleOptions();
        $enforceMFASection = $this->addSection([
            'name'  => 'section_enforce_mfa',
            'label' => __p('mfa::admin.enforce_mfa'),
        ]);

        $enforceMFASection->addFields(
            Builder::switch('mfa.enforce_mfa')
                ->label(__p('mfa::admin.enforce_mfa'))
                ->description(__p('mfa::admin.enforce_mfa_description')),
            Builder::text('mfa.enforce_mfa_timeout')
                ->asNumber()
                ->label(__p('mfa::admin.enforce_mfa_timeout'))
                ->description(__p('mfa::admin.enforce_mfa_timeout_description'))
                ->showWhen(['truthy', 'mfa.enforce_mfa'])
                ->requiredWhen(['truthy', 'mfa.enforce_mfa'])
                ->yup(
                    Yup::number()
                        ->unint()
                        ->min(1)
                        ->when(
                            Yup::when('enforce_mfa')
                                ->is(1)
                                ->then(
                                    Yup::number()
                                        ->required()
                                        ->setError('typeError', __p('validation.numeric', ['attribute' => '${path}']))
                                )
                        )
                ),
            Builder::radioGroup('mfa.enforce_mfa_targets')
                ->label(__p('mfa::admin.enforce_mfa_targets'))
                ->description(__p('mfa::admin.enforce_mfa_targets_description'))
                ->options([
                    [
                        'label' => __p('core::web.all_users'),
                        'value' => 'all',
                    ],
                    [
                        'label' => __p('core::phrase.applicable_roles'),
                        'value' => 'roles',
                    ],
                ])
                ->showWhen(['truthy', 'mfa.enforce_mfa'])
                ->requiredWhen(['truthy', 'mfa.enforce_mfa'])
                ->yup(
                    Yup::string()
                        ->nullable()
                        ->oneOf(['all', 'roles'])
                        ->when(Yup::when('enforce_mfa')->is(1)->then(Yup::string()->required()))
                ),
            Builder::multiChoice('mfa.enforce_mfa_roles')
                ->label(__p('core::phrase.specific_roles'))
                ->description(__p('mfa::admin.enforce_mfa_roles_description'))
                ->disableClearable()
                ->showWhen([
                    'and',
                    ['eq', 'mfa.enforce_mfa_targets', 'roles'],
                    ['truthy', 'mfa.enforce_mfa'],
                ])
                ->options($applicableRoles)
        );
    }
}
