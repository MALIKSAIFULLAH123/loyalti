<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Core\Support\Facades\Country as CountryFacade;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class CountryState.
 */
class CountryState extends AbstractField
{
    /**
     * @param  array<array<string,mixed>> $options
     * @return $this
     */
    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }

    /**
     * @param  array<array<string,mixed>> $subOptions
     * @return $this
     */
    public function subOptions(array $subOptions): self
    {
        return $this->setAttribute('suboptions', $subOptions);
    }

    public function initialize(): void
    {
        [$countries, $states] = $this->getCountryStates();

        $this->component(MetaFoxForm::COUNTRY_STATE)
            ->variant('outlined')
            ->fullWidth(true)
            ->id('country_state')
            ->label(__p('core::country.country'))
            ->options($countries)
            ->subOptions($states)
            ->valueType('array');
    }

    /**
     * @return array<int,mixed>
     */
    public function getCountryStates(): array
    {
        //Get countries for select
        $mainCountries = CountryFacade::buildCountrySearchForm();

        // Generate states with its related country
        $countries = CountryFacade::getAllActiveCountries();
        $states    = [];
        foreach ($countries as $key => $country) {
            $countryStates = CountryFacade::getCountryStates($country['country_iso']);

            $states[$key] = collect($countryStates)->values()->map(function (array $state) {
                return [
                    'value' => $state['state_iso'],
                    'label' => $state['name'],
                ];
            });
        }

        return [
            $mainCountries,
            $states,
        ];
    }
}
