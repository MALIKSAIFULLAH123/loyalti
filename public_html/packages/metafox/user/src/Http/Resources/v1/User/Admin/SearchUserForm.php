<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use Illuminate\Support\Arr;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;
use MetaFox\User\Support\Browse\Scopes\User\StatusScope;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchUserForm.
 *
 * @property Model $resource
 */
class SearchUserForm extends AbstractForm
{
    protected function prepare(): void
    {
        $customFieldNames = CustomFieldFacade::loadFieldName(user(), [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'view'         => CustomField::VIEW_SEARCH,
        ]);

        $this->action('/user/user/browse')
            ->acceptPageParams(array_merge([
                'q', 'email', 'group', 'status', 'gender', 'currency_id',
                'postal_code', 'country_state_id', 'country', 'city_code',
                'age_from', 'age_to', 'sort', 'ip_address', 'phone_number', 'view_more',
            ], $customFieldNames))
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->title(__p('core::phrase.edit'))
            ->setValue([
                'group'  => null,
                'status' => StatusScope::STATUS_DEFAULT,
                'sort'   => SortScope::SORT_DEFAULT,
                'gender' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal();

        $basic->addFields(
            Builder::text('email')
                ->forAdminSearchForm()
                ->label(__p('user::phrase.email')),
            Builder::text('phone_number')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.phone_number')),
            Builder::text('q')
                ->forAdminSearchForm()
                ->placeholder(__p('user::phrase.display_name_or_user_name_label'))
                ->label(__p('user::phrase.display_name_or_user_name_label')),
            Builder::choice('group')
                ->forAdminSearchForm()
                ->label(__p('authorization::phrase.role'))
                ->options($this->getGroupOptions()),
            Builder::gender()
                ->label(__p('user::phrase.user_gender'))
                ->forAdminSearchForm(),
            Builder::text('postal_code')
                ->forAdminSearchForm()
                ->label(__p('user::phrase.zip_postal_code'))
                ->placeholder('- - - - - -'),
            Builder::choice('status')
                ->fullWidth(false)
                ->sizeSmall()
                ->marginDense()
                ->width(220)
                ->showWhen(['and', ['truthy', 'view_more']])
                ->label(__p('user::phrase.show_members'))
                ->options(StatusScope::getStatusOptions()),
            Builder::choice('age_from')
                ->forAdminSearchForm()
                ->showWhen(['and', ['truthy', 'view_more']])
                ->label(__p('user::phrase.age_group_from'))
                ->options($this->getAgeOptions()),
            Builder::choice('age_to')
                ->forAdminSearchForm()
                ->showWhen(['and', ['truthy', 'view_more']])
                ->label(__p('user::phrase.age_group_to'))
                ->options($this->getAgeOptions()),
            Builder::choice('sort')
                ->forAdminSearchForm()
                ->showWhen(['and', ['truthy', 'view_more']])
                ->label(__p('user::phrase.sort_results_by'))
                ->options(SortScope::getSortOptions()),
            Builder::choice('currency_id')
                ->forAdminSearchForm()
                ->showWhen(['and', ['truthy', 'view_more']])
                ->label(__p('localize::currency.label'))
                ->options($this->getCurrencyOptions()),
            Builder::text('ip_address')
                ->forAdminSearchForm()
                ->showWhen(['and', ['truthy', 'view_more']])
                ->label(__p('user::phrase.ip_address')),
            Builder::countryState('country')
                ->sizeSmall()
                ->maxWidth(220)
                ->forAdminSearchForm()
                ->valueType('array')
                ->showWhen(['and', ['truthy', 'view_more']])
                ->setAttribute('countryFieldName', 'country')
                ->setAttribute('stateFieldName', 'country_state_id')
                ->inline(),
            Builder::countryCity('city_code')
                ->forAdminSearchForm()
                ->sizeSmall()
                ->fullWidth(false)
                ->minWidth(220)
                ->label(__p('localize::country.city'))
                ->description(__p('localize::country.city_name'))
                ->showWhen([
                    'and',
                    ['truthy', 'view_more'],
                    ['truthy', 'country'],
                ])
                ->searchEndpoint('user/city')
                ->searchParams([
                    'country' => ':country',
                    'state'   => ':country_state_id',
                ]),
        );

        CustomFieldFacade::loadFieldSearch($basic, [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'resolution'   => MetaFoxConstant::RESOLUTION_ADMIN,
        ]);

        $basic->addFields(
            Builder::submit()
                ->forAdminSearchForm(),
            $this->buttonExportUsers(),
            Builder::submitAction('logout_all_users')
                ->forAdminSearchForm()
                ->label(__p('user::phrase.logout_all_users'))
                ->variant('contained')
                ->sizeMedium()
                ->customAction([
                    'module_name'   => 'user',
                    'resource_name' => 'user',
                    'action_name'   => 'logoutAllUsers',
                ]),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->marginDense()
                ->align('right')
                ->excludeFields(['view_more']),
            Builder::viewMore('view_more')
                ->marginDense()
                ->sxFieldWrapper([
                    'p' => 1,
                ]),
        );
    }

    private function getGroupOptions(): array
    {
        return array_merge(
            [
                [
                    'label' => __p('core::phrase.all'),
                    'value' => null,
                ],
            ],
            resolve(RoleRepositoryInterface::class)->getRoleOptions()
        );
    }

    public function getAgeOptions(): array
    {
        return array_map(function (int $value) {
            return [
                'label' => $value,
                'value' => $value,
            ];
        }, range(4, 121));
    }

    protected function getCurrencyOptions(): array
    {
        $currencies = app('currency')->getCurrencies();
        return array_values(array_map(function ($currency) {
            return [
                'value' => $currency->code,
                'label' => $currency->name,
            ];
        }, $currencies));
    }

    protected function getApiParams(): array
    {
        $customFieldNames = CustomFieldFacade::loadFieldName(user(), [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'view'         => CustomField::VIEW_SEARCH,
        ]);

        $customFieldNames = Arr::map($customFieldNames, function ($customFieldName) {
            return [$customFieldName => ":$customFieldName"];
        });

        return array_merge([
            'q'                => ':q',
            'email'            => ':email',
            'group'            => ':group',
            'status'           => ':status',
            'gender'           => ':gender',
            'postal_code'      => ':postal_code',
            'country_state_id' => ':country_state_id',
            'country'          => ':country',
            'day'              => ':day',
            'age_from'         => ':age_from',
            'age_to'           => ':age_to',
            'sort'             => ':sort',
            'ip_address'       => ':ip_address',
            'phone_number'     => ':phone_number',
            'currency_id'      => ':currency_id',
            'city_code'        => ':city_code',
            'view_more'        => ':view_more',
        ], $customFieldNames);
    }

    protected function buttonExportUsers(): ?AbstractField
    {
        if (!user()->hasPermissionTo('admincp.has_system_access')) {
            return null;
        }

        return Builder::submitAction('export_users')
            ->forAdminSearchForm()
            ->label(__p('user::phrase.export_users'))
            ->variant('contained')
            ->sizeMedium()
            ->customAction([
                'apiParams' => $this->getApiParams(),
                'type'      => '@admin/mailInActiveNavigate',
                'to'        => '/user/export-process/create',
            ]);
    }
}
