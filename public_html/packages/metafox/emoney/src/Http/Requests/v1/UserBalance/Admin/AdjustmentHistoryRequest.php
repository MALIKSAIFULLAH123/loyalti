<?php
namespace MetaFox\EMoney\Http\Requests\v1\UserBalance\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\EMoney\Facades\UserBalance;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Rules\AllowInRule;

class AdjustmentHistoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_full_name'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'owner_full_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'currency'        => ['sometimes', 'nullable', 'string', 'exists:core_currencies,code'],
            'type'            => ['sometimes', 'nullable', new AllowInRule(array_column(UserBalance::getAdjustmentTypeOptions(), 'value'))],
            'from_amount'     => ['sometimes', 'nullable', 'numeric', 'min:' . Support::DEFAULT_MIN_ADJUST_BALANCE_VALUE, 'max:' . Support::DEFAULT_MAX_ADJUST_BALANCE_VALUE_IN_TOTAL],
            'to_amount'       => ['sometimes', 'nullable', 'numeric', 'min:' . Support::DEFAULT_MIN_ADJUST_BALANCE_VALUE, 'max:' . Support::DEFAULT_MAX_ADJUST_BALANCE_VALUE_IN_TOTAL, 'after:from_amount'],
            'from_date'       => ['sometimes', 'nullable', 'date'],
            'to_date'         => ['sometimes', 'nullable', 'date', 'after:from_date'],
            'limit'           => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
