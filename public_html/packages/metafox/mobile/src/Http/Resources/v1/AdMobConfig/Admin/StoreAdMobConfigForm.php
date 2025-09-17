<?php

namespace MetaFox\Mobile\Http\Resources\v1\AdMobConfig\Admin;

use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Mobile\Models\AdMobConfig as Model;
use MetaFox\Mobile\Repositories\AdMobPageAdminRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreAdMobConfigForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class StoreAdMobConfigForm extends AbstractForm
{
    protected function prepare(): void
    {
        $roles = resolve(RoleRepositoryInterface::class)->getUsableRoles()->pluck('id')->toArray();

        $this->title(__p('mobile::phrase.add_ad_config'))
            ->action(apiUrl('admin.mobile.admob.store'))
            ->asPost()
            ->setValue([
                'type'                    => Model::AD_MOB_TYPE_BANNER,
                'roles'                   => $roles,
                'frequency_capping'       => Model::AD_MOB_FREQUENCY_NONE,
                'time_capping_impression' => 0,
                'time_capping_frequency'  => Model::AD_MOB_FREQUENCY_PER_MINUTE,
                'view_capping'            => 0,
                'is_active'               => 1,
                'is_sticky'               => 0,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('name')
                    ->required()
                    ->label(__p('core::phrase.name'))
                    ->description(__p('mobile::phrase.ad_config_name_desc'))
                    ->yup(Yup::string()->required()),
                Builder::radioGroup('type')
                    ->label(__p('mobile::phrase.ad_mob_select_type'))
                    ->multiple(false)
                    ->inline(true)
                    ->description(__p('mobile::phrase.ad_mob_select_type_desc'))
                    ->options($this->getTypeOptions()),
                Builder::choice('pages')
                    ->label(__p('mobile::phrase.ad_mob_select_page'))
                    ->description(__p('mobile::phrase.ad_mob_select_page_desc'))
                    ->options($this->getPagesOptions())
                    ->required()
                    ->multiple()
                    ->yup(Yup::array()->required()),
                Builder::dropdown('frequency_capping')
                    ->label(__p('mobile::phrase.frequency_capping'))
                    ->required()
                    ->description(__p('mobile::phrase.select_frequency_capping_desc'))
                    ->options($this->getCappingOptions())
                    ->showWhen([
                        'and',
                        ['eq', 'type', 'banner'],
                    ]),
                Builder::section('time_capping_group')
                    ->asHorizontal()
                    ->marginDense()
                    ->sxContainer([
                        'alignItems' => 'flex-start',
                    ])
                    ->showWhen([
                        'and',
                        ['eq', 'type', 'banner'],
                        ['eq', 'frequency_capping', 'times'],
                    ])
                    ->addFields(
                        Builder::text('time_capping_impression')
                            ->asNumber()
                            ->preventScrolling()
                            ->label(__p('mobile::phrase.select_time_capping_impression'))
                            ->fullWidth(false)
                            ->width(300)
                            ->marginDense()
                            ->yup(Yup::number()->int()->min(0)),
                        Builder::choice('time_capping_frequency')
                            ->label(__p('mobile::phrase.select_time_frequency'))
                            ->disableClearable()
                            ->fullWidth(false)
                            ->width(300)
                            ->marginDense()
                            ->options($this->getTimeFrequencyOptions()),
                    ),
                Builder::typography('time_capping_description')
                    ->plainText(__p('mobile::phrase.frequency_capping_time_desc'))
                    ->color('text.hint')
                    ->tagName('span')
                    ->showWhen([
                        'and',
                        ['eq', 'type', 'banner'],
                        ['eq', 'frequency_capping', 'times'],
                    ]),
                Builder::text('view_capping')
                    ->asNumber()
                    ->preventScrolling()
                    ->label(__p('mobile::phrase.select_view_capping_impression'))
                    ->fullWidth(false)
                    ->minWidth(350)
                    ->description(__p('mobile::phrase.select_view_capping_description'))
                    ->showWhen([
                        'and',
                        ['eq', 'type', 'banner'],
                        ['eq', 'frequency_capping', 'views'],
                    ])
                    ->yup(Yup::number()->int()->min(0)),
                Builder::section('location_priority')
                    ->asHorizontal()
                    ->addFields(
                        Builder::choice('location')
                            ->required()
                            ->minWidth(250)
                            ->fullWidth(false)
                            ->label(__p('mobile::phrase.select_ad_location'))
                            ->description(__p('mobile::phrase.select_ad_location_description'))
                            ->options($this->getLocationOptions())
                            ->yup(Yup::when('type')->is('banner')->then(Yup::string()->required()))
                            ->showWhen([
                                'and',
                                ['eq', 'type', 'banner'],
                            ]),
                    ),
                Builder::checkboxGroup('roles')
                    ->label(__p('mobile::phrase.ad_mob_allow_access_from'))
                    ->marginNormal()
                    ->options($this->getRolesOptions())
                    ->enableCheckAll(),
                Builder::checkbox('is_active')
                    ->label(__p('core::phrase.is_active')),
            );

        $this->addDefaultFooter();
    }

    /**
     * @return array<int, mixed>
     */
    protected function getTypeOptions(): array
    {
        return $this->transformOptionFromArray(Model::AD_MOB_TYPE);
    }

    /**
     * @return array<int, mixed>
     */
    protected function getPagesOptions(): array
    {
        return resolve(AdMobPageAdminRepositoryInterface::class)->getPageOptions();
    }

    protected function getRolesOptions(): array
    {
        return resolve(RoleRepositoryInterface::class)->getRoleOptions();
    }

    /**
     * @return array<int, mixed>
     */
    protected function getCappingOptions(): array
    {
        return $this->transformOptionFromArray(Model::AD_MOB_FREQUENCY);
    }

    /**
     * @return array<int, mixed>
     */
    protected function getLocationOptions(): array
    {
        return $this->transformOptionFromArray(Model::AD_MOB_LOCATIONS);
    }

    /**
     * @return array<int, mixed>
     */
    protected function getTimeFrequencyOptions(): array
    {
        return $this->transformOptionFromArray(Model::AD_MOB_TIME_FREQUENCY);
    }

    /**
     * @return array<int, mixed>
     */
    protected function getPriorityOptions(int $start = 0, int $end = 10): array
    {
        $data = [];
        while ($start <= $end) {
            $data[] = [
                'label' => $start,
                'value' => $start,
            ];
            $start++;
        }

        return $data;
    }

    /**
     * return array<int, mixed>.
     */
    protected function transformOptionFromArray(array $data): array
    {
        return collect($data)
            ->map(function ($key, $value) {
                return [
                    'label' => __p($key),
                    'value' => $value,
                ];
            })
            ->values()
            ->toArray();
    }
}
