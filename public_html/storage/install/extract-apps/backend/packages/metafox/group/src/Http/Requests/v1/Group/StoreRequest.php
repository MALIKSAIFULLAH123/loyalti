<?php

namespace MetaFox\Group\Http\Requests\v1\Group;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\RegexRule;
use MetaFox\Platform\Rules\ResourceNameRule;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', new ResourceNameRule('group'), new RegexRule('display_name')],
            'category_id' => [
                'sometimes', 'numeric', new CategoryRule(resolve(CategoryRepositoryInterface::class)),
            ],
            'reg_method'  => ['required', 'numeric', new AllowInRule(PrivacyTypeHandler::ALLOW_PRIVACY)],
            'text'        => ['sometimes', 'string', 'nullable'],
            'users'       => ['sometimes', 'array'],
            'users.*'     => ['sometimes', 'array'],
            'users.*.id'  => ['numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['category_id'])) {
            $data['category_id'] = 0;
        }

        if (!isset($data['text'])) {
            $data['text'] = '';
        }

        $data['user_ids'] = [];

        if (array_key_exists('users', $data)) {
            $users            = Arr::get($data, 'users', []);
            $data['user_ids'] = collect($users)->pluck('id')->toArray();
        }

        if (empty($data['owner_id'])) {
            $data['owner_id'] = 0;
        }

        $data['privacy_type'] = $data['reg_method'];
        unset($data['reg_method']);
        unset($data['users']);

        return $data;
    }

    /**
     * @return array<mixed>
     */
    public function messages(): array
    {
        return [
            'name.required' => __p('core::validation.name.required'),
        ];
    }
}
