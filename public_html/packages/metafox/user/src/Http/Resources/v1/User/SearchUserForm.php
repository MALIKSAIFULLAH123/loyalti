<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\User\Support\Browse\Scopes\User\ViewScope;
use MetaFox\User\Support\Facades\User;

/**
 * @preload 1
 */
class SearchUserForm extends AbstractForm
{
    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $this->action('/user/search')
            ->acceptPageParams($this->handleAcceptParams())
            ->setValue([
                'group' => null,
                'view'  => Browse::VIEW_ALL,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('user::phrase.search_users'))
                ->className('mb2'),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['q']),
        );

        if (Auth::user()?->hasPermissionTo('user_role.filter')) {
            $basic->addField(
                Builder::choice('group')
                    ->marginNormal()
                    ->disableClearable()
                    ->label(__p('authorization::phrase.role'))
                    ->options(User::getRoleOptionsForSearchMembers()),
            );
        }

        $basic->addFields(
            Builder::choice('view')
                ->label(__p('user::phrase.filter_by'))
                ->disableClearable()
                ->marginNormal()
                ->options($this->getViewOptions()),
        );

        CustomFieldFacade::loadFieldSearch($basic, [
            'section_type'   => CustomField::SECTION_TYPE_USER,
            'resolution'     => MetaFoxConstant::RESOLUTION_WEB,
            'relation_field' => 'group',
        ]);

        $basic->addFields(
            Builder::countryState('country_iso')
                ->valueType('array')
                ->setAttribute('countryFieldName', 'country')
                ->setAttribute('stateFieldName', 'country_state_id')
                ->setAttribute('cityFieldName', 'city_code'),
            //City field
            Builder::searchCountryCity('city_code')
                ->label(__p('localize::country.city'))
                ->description(__p('localize::country.city_name'))
                ->searchEndpoint('user/city')
                ->showWhen([
                    'truthy',
                    'country',
                ])
                ->searchParams([
                    'country'   => ':country',
                    'state'     => ':country_state_id',
                    'city_code' => ':city_code',
                ]),
            Builder::gender()
                ->label(__p('user::phrase.genders'))
                ->marginNormal(),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->marginNormal()
                ->options($this->getSortOptions()),
        );
    }

    /**
     * @return array<int, mixed>
     */
    protected function getSortOptions(): array
    {
        return [
            ['label' => __p('core::phrase.name'), 'value' => 'full_name'],
            ['label' => __p('user::phrase.last_login'), 'value' => 'last_login'],
            ['label' => __p('user::phrase.last_activity'), 'value' => 'last_activity'],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function getViewOptions(): array
    {
        $options = [
            ['label' => __p('core::phrase.all'), 'value' => ViewScope::VIEW_DEFAULT],
            ['label' => __p('user::phrase.recent_active'), 'value' => ViewScope::VIEW_RECENT],
            ['label' => __p('user::phrase.members_you_may_know'), 'value' => ViewScope::VIEW_RECOMMEND],
            ['label' => __p('user::phrase.feature_user'), 'value' => ViewScope::VIEW_FEATURED],
        ];

        if (Auth::user()?->isGuest()) {
            $options = array_filter($options, function ($item) {
                return $item['value'] != ViewScope::VIEW_RECOMMEND;
            });
        }

        return array_values($options);
    }

    /**
     * @return string[]
     * @throws AuthenticationException
     */
    protected function handleAcceptParams(): array
    {
        $result = ['q', 'country', 'city_code', 'gender', 'sort', 'country_state_id', 'is_featured', 'group', 'view'];
        $fields = CustomFieldFacade::loadFieldName(Auth::user(), [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'view'         => CustomField::VIEW_SEARCH,
        ]);

        return array_merge($result, $fields);
    }
}
