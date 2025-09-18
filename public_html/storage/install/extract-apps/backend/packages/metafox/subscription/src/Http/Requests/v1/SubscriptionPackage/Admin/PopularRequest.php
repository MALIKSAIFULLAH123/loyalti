<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;

class PopularRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_popular' => ['required', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $isPopular = Arr::get($data, 'is_popular');

        if (is_numeric($isPopular)) {
            $isPopular = (bool) $isPopular;
        }

        Arr::set($data, 'is_popular', $isPopular);

        return $data;
    }
}
