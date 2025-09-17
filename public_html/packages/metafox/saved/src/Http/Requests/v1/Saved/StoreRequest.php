<?php

namespace MetaFox\Saved\Http\Requests\v1\Saved;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_id'          => ['required', 'numeric'],
            'item_type'        => ['required', 'string'],
            'in_feed'          => ['required', 'numeric', new AllowInRule([0, 1])],
            'saved_list_ids'   => ['sometimes', 'array'],
            'saved_list_ids.*' => ['numeric', new ExistIfGreaterThanZero('exists:saved_lists,id')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (isset($data['saved_list_ids'])) {
            $data['savedLists'] = $data['saved_list_ids'];
            unset($data['saved_list_ids']);
        }

        return $data;
    }

    public function messages()
    {
        return [
            'saved_list_ids.*.exists' => __p('saved::validation.collection_not_exists'),
        ];
    }
}
