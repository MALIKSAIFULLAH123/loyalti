<?php

namespace MetaFox\Localize\Http\Requests\v1\Currency\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Localize\Models\Currency;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;
use MetaFox\Platform\Rules\RegexRule;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = (int) $this->route('currency');

        $rules = [
            'currency_code' => [
                'required',
                'string',
                new CaseInsensitiveUnique('core_currencies', 'code', $id),
                new RegexRule('currency_id'),
            ],
            'symbol'     => ['required', 'string', 'between:1,15'],
            'name'       => ['required', 'string', 'between:1,255'],
            'format'     => ['sometimes', 'string'],
            'is_default' => ['sometimes', new AllowInRule([0, 1])],
        ];

        return  $this->handleActiveRule($rules);
    }

    private function handleActiveRule(array $rules): array
    {
        $id       = (int) $this->route('currency');
        $currency = Currency::query()->find($id);

        if ($currency && !$currency->is_using) {
            $rules['is_active']  = ['sometimes', new AllowInRule([0, 1])];
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $currencyCode = Arr::get($data, 'currency_code');

        if (!Arr::has($data, 'is_default')) {
            Arr::set($data, 'is_default', false);
        }

        if (!Arr::has($data, 'is_active')) {
            Arr::set($data, 'is_active', true);
        }

        if (Arr::get($data, 'is_default')) {
            Arr::set($data, 'is_active', true);
        }

        if ($currencyCode) {
            Arr::set($data, 'code', $currencyCode);
            Arr::forget($data, 'currency_code');
        }

        return $data;
    }
}
