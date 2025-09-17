<?php

namespace MetaFox\Localize\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Core\Support\Facades\CountryCity;
use MetaFox\Platform\Rules\AllowInRule;

class CountryCityExistRule implements RuleContract
{
    protected array $excepts;

    public function __construct(array $excepts = [])
    {
        $this->excepts = $excepts;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if (is_array($value)) {
            $value = Arr::get($value, 'value', 0);
        }

        if ($value === 0) {
            return true;
        }
        $data = ['city_code' => $value];

        $allowInRule = array_merge(CountryCity::getAllActiveCities('city_code'), $this->excepts);

        $rules = ['city_code' => [new AllowInRule($allowInRule)]];

        $validator = Validator::make($data, $rules);

        return $validator->passes();
    }

    public function message(): string
    {
        return __p('localize::phrase.country_city_is_not_available');
    }

    public function excepts(array $excepts = []): self
    {
        $this->excepts = $excepts;

        return $this;
    }
}
