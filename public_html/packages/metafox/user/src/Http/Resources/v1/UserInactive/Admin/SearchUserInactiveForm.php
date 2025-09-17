<?php

namespace MetaFox\User\Http\Resources\v1\UserInactive\Admin;

use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Models\UserGender as Model;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchUserInactiveForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName user.user_gender.search
 */
class SearchUserInactiveForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action('/user/inactive/browse')
            ->acceptPageParams([
                'q', 'email', 'group', 'status', 'gender',
                'postal_code', 'country_state_id', 'country', 'day', 'city_code',
                'age_from', 'age_to', 'sort', 'ip_address', 'phone_number', 'view_more',
            ])
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->setValue([
                'group'  => null,
                'status' => MetaFoxConstant::STATUS_APPROVED,
                'sort'   => SortScope::SORT_DEFAULT,
                'gender' => 0,
                'day'    => 7,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::text('day')
                ->forAdminSearchForm()
                ->required()
                ->asNumber()
                ->label(__p('user::phrase.day'))
                ->setAttribute('type', 'number')
                ->description(__p('user::phrase.number_day'))
                ->maxWidth(380)
                ->yup(Yup::number()->required()->unint()),
            Builder::text('q')
                ->forAdminSearchForm()
                ->placeholder(__p('user::phrase.display_name_or_user_name_label'))
                ->label(__p('user::phrase.display_name_or_user_name_label')),
            Builder::text('email')
                ->forAdminSearchForm()
                ->label(__p('user::phrase.email')),
            Builder::text('phone_number')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.phone_number')),
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
                ->options($this->getStatusOptions()),
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
                ])
        );
        $basic->addFields(
            Builder::submit()
                ->forAdminSearchForm()
                ->label(__p('user::phrase.get_inactive_member')),
            Builder::submitAction('process_mailing_all')
                ->forAdminSearchForm()
                ->label(__p('user::phrase.mail_inactive_members'))
                ->variant('contained')
                ->sizeMedium()
                ->customAction([
                    'apiParams' => $this->getApiParams(),
                    'type'      => '@admin/mailInActiveNavigate',
                    'to'        => '/user/inactive-process/create',
                ]),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->marginDense()
                ->align('right')
                ->excludeFields(['view_more'])
                ->sxFieldWrapper([
                    'p'              => 1,
                    'justifyContent' => 'center',
                    'alignItems'     => 'center,',
                ]),
            Builder::viewMore('view_more')
                ->marginDense()
                ->sxFieldWrapper([
                    'p'              => 1,
                    'justifyContent' => 'center',
                    'alignItems'     => 'center,',
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

    protected function getStatusOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.approved'),
                'value' => MetaFoxConstant::STATUS_APPROVED,
            ],
            [
                'label' => __p('user::phrase.featured_members'),
                'value' => MetaFoxConstant::STATUS_FEATURED,
            ],
        ];
    }

    protected function getApiParams(): array
    {
        return [
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
        ];
    }
}
