<?php

namespace MetaFox\Saved\Http\Requests\v1\Saved;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

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
        return [
            'is_unopened'      => ['sometimes', 'numeric', new AllowInRule([0, 1])],
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

        if (isset($data['is_unopened'])) {
            $data['is_opened'] = (int) !$data['is_unopened'];
            unset($data['is_unopened']);
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
