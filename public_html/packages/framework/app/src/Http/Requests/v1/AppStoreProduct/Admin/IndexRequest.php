<?php

namespace MetaFox\App\Http\Requests\v1\AppStoreProduct\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\App\Http\Resources\v1\AppStoreProduct\Admin\SearchForm;
use MetaFox\App\Support\MetaFoxStore;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Core\Http\Controllers\Api\v1\StoreAdminController::index;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
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
            'q'            => ['sometimes', 'nullable', 'string'],
            'type'         => ['sometimes', 'string', new AllowInRule(SearchForm::getAllowedOptions('type'))],
            'category'     => ['sometimes', 'string', new AllowInRule($this->getAllowedCategories())],
            'price_filter' => ['sometimes', 'string', new AllowInRule(SearchForm::getAllowedOptions('price_filter'))],
            'sort'         => ['sometimes', 'string', new AllowInRule(SearchForm::getAllowedOptions('sort'))],
            'featured'     => ['sometimes', 'string', new AllowInRule(SearchForm::getAllowedOptions('featured'))],
            'page'         => ['sometimes', 'numeric', 'min:1'],
            'limit'        => ['sometimes', 'numeric'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = Arr::add($data, 'sort', 'latest');
        $data = Arr::add($data, 'type', 'app');

        $filters = ['type', 'category', 'price_filter', 'featured'];

        foreach ($filters as $filter) {
            if (Arr::get($data, $filter) === 'all') {
                unset($data[$filter]);
            }
        }

        return $data;
    }

    /**
     * @return array<string>
     */
    public function getAllowedCategories(): array
    {
        return resolve(MetaFoxStore::class)->getAllowedCategories();
    }
}
