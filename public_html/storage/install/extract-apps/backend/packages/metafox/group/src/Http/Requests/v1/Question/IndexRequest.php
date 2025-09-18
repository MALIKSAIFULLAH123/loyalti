<?php

namespace MetaFox\Group\Http\Requests\v1\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Group\Models\Question;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\PaginationLimitRule;

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
            'group_id' => ['required', 'numeric', 'exists:groups,id'],
            'page'     => ['sometimes', 'numeric', 'min:1'],
            'limit'    => ['sometimes', 'numeric', new PaginationLimitRule(1, Settings::get('group.maximum_membership_question', Question::MAX_QUESTION))],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (Arr::has($data, 'limit')) {
            $limit = Arr::get($data, 'limit');

            if (!$limit) {
                unset($data['limit']);
            }
        }

        return $data;
    }
}
