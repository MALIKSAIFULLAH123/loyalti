<?php

namespace MetaFox\EMoney\Http\Requests\v1\UserBalance\Admin;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\EMoney\Http\Controllers\Api\v1\UserBalanceAdminController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest
 */
class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort'      => ['sometimes', new AllowInRule($this->getSortFields())],
            'sort_type' => ['sometimes', new AllowInRule([Browse::SORT_TYPE_ASC, Browse::SORT_TYPE_DESC])],
        ];
    }

    protected function getSortFields(): array
    {
        $fields = ['users.full_name'];

        foreach (array_keys(app('currency')->getCurrencies()) as $currency) {
            $fields[] = sprintf('emoney_statistics.%s', $currency);
        }

        return $fields;
    }
}
