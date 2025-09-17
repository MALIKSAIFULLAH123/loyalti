<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Support\Facades\Auth;
use MetaFox\Core\Support\Facades\Country as CountryFacade;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;
use MetaFox\User\Support\Facades\User;

class SearchUserMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/user')
            ->title(__p('core::phrase.search'))
            ->acceptPageParams(['q', 'country', 'city_code', 'gender', 'sort', 'country_state_id', 'group', 'is_featured']);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])->showWhen(['falsy', 'filters']);
        $basic->addFields(
            $this->getSearchFields()
                ->label(__p('core::phrase.keywords'))
                ->placeholder(__p('user::phrase.search_users')),
            Builder::button('filters')
                ->forBottomSheetForm(),
        );
        $this->getBasicFields($basic);

        $bottomSheet = $this->addSection(['name' => 'bottomSheet']);
        $this->getBottomSheetFields($bottomSheet);
    }

    protected function initializeFlatten(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView']);

        $basic->addFields(
            $this->getClearSearchFieldsFlatten()
                ->targets(['country', 'gender', 'sort', 'city_code', 'is_featured', 'group']),
            $this->getSearchFieldsFlatten()
                ->label(__p('core::phrase.keywords'))
                ->placeholder(__p('user::phrase.search_users'))
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        if (Auth::user()?->hasPermissionTo('user_role.filter')) {
            $section->addField(
                Builder::choice('group')
                    ->forBottomSheetForm()
                    ->autoSubmit()
                    ->label(__p('authorization::phrase.role'))
                    ->options(User::getRoleOptionsForSearchMembers()),
            );
        }
        $this->addCountryStateFields($section);
        $section->addFields(
            Builder::choice('gender')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('user::phrase.genders'))
                ->options($this->initGenderOptions()),
            Builder::choice('sort')
                ->forBottomSheetForm()
                ->autoSubmit()
                ->label(__p('core::phrase.sort_label'))
                ->options($this->getSortOptions()),
        );

        $section->addField(
            Builder::switch('is_featured')
                ->forBottomSheetForm()
                ->margin('none')
                ->label(__p('core::phrase.featured')),
        );
    }

    protected function addCountryStateFields(Section $section): void
    {
        $activeCountries = CountryFacade::buildCountrySearchForm();
        $section->addFields(
            Builder::choice('country')
            ->enableSearch()
            ->forBottomSheetForm()
            ->autoSubmit()
            ->label(__p('localize::country.country'))
            ->setAttribute('resetValue', true)
            ->options($activeCountries),
        );
        if (version_compare(MetaFox::getApiVersion(), 'v1.18', '<')) {
            $section->addFields(
                Builder::autocomplete('city_code')
                    ->useOptionContext()
                    ->forBottomSheetForm()
                    ->variant('standard-inlined')
                    ->label(__p('localize::country.city'))
                    ->placeholder(__p('localize::country.filter_by_city'))
                    ->showWhen([
                        'and',
                        ['truthy', 'country'],
                    ])
                    ->searchEndpoint('/user/city')
                    ->searchParams(['country' => ':country'])
                    ->valueKey('value'),
            );

            return;
        }
        $section->addFields(
            Builder::countryStatePicker('country_state_id')
                ->useOptionContext()
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->label(__p('localize::country.state'))
                ->description(__p('localize::country.state_name'))
                ->showWhen([
                    'and',
                    ['truthy', 'country'],
                ])
                ->searchEndpoint('user/country/state')
                ->searchParams([
                    'country' => ':country',
                ])
                ->valueKey('value'),
            Builder::countryCity('city_code')
                ->useOptionContext()
                ->forBottomSheetForm()
                ->autoSubmit()
                ->variant('standard-inlined')
                ->label(__p('localize::country.city'))
                ->description(__p('localize::country.filter_by_city'))
                ->showWhen([
                    'and',
                    ['truthy', 'country'],
                ])
                ->searchEndpoint('user/city')
                ->searchParams([
                    'country'   => ':country',
                    'state'     => ':country_state_id',
                    'city_code' => ':city_code',
                ])
                ->valueKey('value')
        );
    }

    protected function getBottomSheetFields(Section $section): void
    {
        $section->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->showWhen(['truthy', 'filters'])
                ->targets(['country', 'gender', 'sort', 'city_code', 'is_featured', 'group', 'country_state_id']),
        );

        if (Auth::user()?->hasPermissionTo('user_role.filter')) {
            $section->addField(
                Builder::choice('group')
                    ->forBottomSheetForm()
                    ->variant('standard-inlined')
                    ->label(__p('authorization::phrase.role'))
                    ->options(User::getRoleOptionsForSearchMembers())
                    ->showWhen(['truthy', 'filters']),
            );
        }
        $this->addCountryStateBottomSheet($section);
        $section->addFields(
            Builder::choice('gender')
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->label(__p('user::phrase.genders'))
                ->options($this->initGenderOptions())
                ->showWhen(['truthy', 'filters']),
            Builder::choice('sort')
                ->forBottomSheetForm()
                ->label(__p('core::phrase.sort_label'))
                ->variant('standard-inlined')
                ->options($this->getSortOptions())
                ->showWhen(['truthy', 'filters']),
        );

        $section->addField(
            Builder::switch('is_featured')
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->label(__p('core::phrase.featured'))
                ->showWhen(['truthy', 'filters']),
        );

        $section->addField(
            Builder::submit()
                ->showWhen(['truthy', 'filters'])
                ->label(__p('core::phrase.show_results')),
        );
    }

    protected function addCountryStateBottomSheet(Section $section): void
    {
        $activeCountries = CountryFacade::buildCountrySearchForm();

        if (version_compare(MetaFox::getApiVersion(), 'v1.18', '<')) {
            $section->addFields(
                Builder::choice('country')
                    ->forBottomSheetForm()
                    ->autoSubmit()
                    ->variant('standard-inlined')
                    ->setAttribute('resetValue', true)
                    ->label(__p('localize::country.country'))
                    ->options($activeCountries)
                    ->enableSearch()
                    ->showWhen(['truthy', 'filters']),
                Builder::autocomplete('city_code')
                    ->useOptionContext()
                    ->forBottomSheetForm()
                    ->variant('standard-inlined')
                    ->label(__p('localize::country.city'))
                    ->placeholder(__p('localize::country.filter_by_city'))
                    ->showWhen([
                        'and',
                        ['truthy', 'filters'],
                        ['truthy', 'country'],
                    ])
                    ->searchEndpoint('/user/city')
                    ->searchParams(['country' => ':country'])
                    ->valueKey('value'),
            );

            return;
        }

        $section->addFields(
            Builder::choice('country')
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->setAttribute('resetValue', true)
                ->label(__p('localize::country.country'))
                ->options($activeCountries)
                ->enableSearch()
                ->showWhen(['truthy', 'filters']),
            Builder::countryStatePicker('country_state_id')
                ->useOptionContext()
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->label(__p('localize::country.state'))
                ->description(__p('localize::country.state_name'))
                ->showWhen([
                    'and',
                    ['truthy', 'filters'],
                    ['truthy', 'country'],
                ])
                ->searchEndpoint('user/country/state')
                ->searchParams([
                    'country' => ':country',
                ])
                ->valueKey('value'),
            Builder::countryCity('city_code')
                ->useOptionContext()
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->label(__p('localize::country.city'))
                ->description(__p('localize::country.filter_by_city'))
                ->showWhen([
                    'and',
                    ['truthy', 'filters'],
                    ['truthy', 'country'],
                ])
                ->searchEndpoint('user/city')
                ->searchParams([
                    'country'   => ':country',
                    'state'     => ':country_state_id',
                    'city_code' => ':city_code',
                ])
                ->valueKey('value'),
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
    protected function initGenderOptions(): array
    {
        $genders = resolve(UserGenderRepositoryInterface::class)->getForForms(user(), null);

        $default = [
            ['label' => __p('core::phrase.any'), 'value' => 0],
        ];

        return array_merge($default, $genders);
    }
}
