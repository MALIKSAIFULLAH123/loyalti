<?php

namespace MetaFox\Advertise\Http\Resources\v1\SponsorSetting\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Models\Sponsor as Model;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\UserRole;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditSponsorForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditSponsorSettingForm extends AbstractForm
{
    /**
     * @var int
     */
    private int $roleId = UserRole::NORMAL_USER_ID;

    /**
     * @var array
     */
    private array $settings = [];

    /**
     * @var array
     */
    private array $currencies = [];

    public function boot(Request $request)
    {
        $this->roleId = $request->get('role_id', UserRole::NORMAL_USER_ID);

        $this->settings = resolve(SponsorSettingServiceInterface::class)->getPackageSettings($this->roleId);

        $this->currencies = app('currency')->getActiveOptions();
    }

    protected function prepare(): void
    {
        $this->title(__p('advertise::phrase.edit_sponsor_settings'))
            ->action('admincp/advertise/sponsor-setting/' . $this->roleId)
            ->asPut()
            ->setAttribute('fixedFooter', true)
            ->setValue($this->buildValues());
    }

    protected function buildValues(): array
    {
        if (!count($this->settings)) {
            return [];
        }

        $values = [];

        foreach ($this->settings as $entityType => $setting) {
            $values = $this->buildValueForPermission($values, $setting, $entityType);
            $values = $this->buildValuesForSetting($values, $setting, $entityType);
        }

        return $values;
    }

    protected function buildValueForPermission(array $values, array $setting, string $entityType): array
    {
        if (!Arr::has($setting, 'permissions.sponsor_free')) {
            return $values;
        }

        foreach ($setting['permissions'] as $name => $permission) {
            Arr::set($values, sprintf('permissions.%s.%s', $entityType, $name), Arr::get($permission, 'value'));
        }

        return $values;
    }

    protected function buildValueForSetting(array $values, string $name, array $settingItem, string $entityType): array
    {
        $value = Arr::get($settingItem, 'value');

        if (null === $value) {
            return $values;
        }

        foreach ($this->currencies as $currency) {
            if (!Arr::has($value, $currency['value'])) {
                continue;
            }

            Arr::set($values, sprintf('settings_%s_%s', $name, $currency['value']), round(Arr::get($value, $currency['value']), 2));
        }

        return $values;
    }

    protected function buildValuesForSetting(array $values, array $setting, string $entityType): array
    {
        if (!Arr::has($setting, 'settings')) {
            return $values;
        }

        foreach ($setting['settings'] as $name => $settingItem) {
            $values = $this->buildValueForSetting($values, $name, $settingItem, $entityType);
        }

        return $values;
    }

    protected function initialize(): void
    {
        if (!count($this->settings)) {
            $this->addBasic()
                ->addFields(
                    Builder::description()
                        ->label(__p('advertise::phrase.no_settings_available'))
                );

            return;
        }

        foreach ($this->settings as $alias => $setting) {
            $this->addModuleSection($alias, $setting);
        }

        $this->addDefaultFooter(true);
    }

    protected function addModuleSection(string $entityType, array $moduleParams): void
    {
        if (Arr::has($moduleParams, 'permissions') && !Arr::has($moduleParams, 'permissions.sponsor_free')) {
            return;
        }

        $section = $this->addSection('entity_' . $entityType)
            ->label(Arr::get($moduleParams, 'title'))
            ->collapsible()
            ->collapsed();

        if (Arr::has($moduleParams, 'permissions')) {
            $sorted = [];

            $permissions = Arr::get($moduleParams, 'permissions');

            $orders = [
                'sponsor',
                'sponsor_free',
                'sponsor_in_feed',
                'auto_publish_sponsored_item',
            ];

            foreach ($orders as $order) {
                if (!Arr::has($permissions, $order)) {
                    continue;
                }

                $sorted[$order] = Arr::get($permissions, $order);
            }

            foreach ($sorted as $name => $permission) {
                $var = sprintf('permissions.%s.%s', $entityType, $name);

                $section->addField(
                    Builder::switch($var)
                        ->label(Arr::get($permission, 'title'))
                        ->description(Arr::get($permission, 'description'))
                        ->marginDense()
                );
            }
        }

        if (Arr::has($moduleParams, 'settings')) {
            foreach (Arr::get($moduleParams, 'settings') as $name => $setting) {
                $this->addCurrencyField($section, $entityType, $name, Arr::get($setting, 'title'), Arr::get($setting, 'description'));
            }
        }
    }

    protected function addCurrencyField(Section $section, string $entityType, string $name, ?string $label, ?string $description): void
    {
        $section->addField(
            Builder::description(sprintf('%s_price_description', $entityType))
                ->label($label ?? MetaFoxConstant::EMPTY_STRING)
                ->setAttribute('labelProps', ['color' => 'textPrimary'])
                ->description($description ?? MetaFoxConstant::EMPTY_STRING)
                ->marginDense()
        );

        $maxPriceValue = (int) str_repeat(9, 12);

        foreach ($this->currencies as $currency) {
            $section->addField(
                Builder::text(sprintf('settings_%s_%s', $name, $currency['value']))
                    ->label($currency['label'])
                    ->description(__p('advertise::phrase.specify_amount_you_want_to_charge_people'))
                    ->sizeSmall()
                    ->marginDense()
                    ->yup(
                        Yup::number()
                            ->nullable()
                            ->min(0, __p('advertise::phrase.price_must_be_greater_than_or_equal_to_number', ['number' => 0]))
                            ->max($maxPriceValue, __p('core::validation.currency_must_be_less_than_or_equal_to_number', [
                                'currency_code' => $currency['label'],
                                'number'        => number_format($maxPriceValue),
                            ]))
                            ->setError('typeError', __p('advertise::validation.price_must_be_number'))
                    )
            );
        }
    }
}
