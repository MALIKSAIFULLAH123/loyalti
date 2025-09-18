<?php

namespace MetaFox\Page\Http\Requests\v1\Page;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Rules\Base64FileTypeRule;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
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
            'name'          => ['required', 'string', new ResourceNameRule('page'), new RegexRule('display_name')],
            'category_id'   => [
                'sometimes', 'numeric', new CategoryRule(resolve(PageCategoryRepositoryInterface::class)),
            ],
            'text'          => ['sometimes', 'string', 'nullable'],
            'users'         => ['sometimes', 'array'],
            'users.*'       => ['sometimes', 'array'],
            'users.*.id'    => ['sometimes', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'external_link' => ['sometimes', 'url', 'nullable'],
            'image'         => ['sometimes', 'nullable', 'array'],
            'image.base64'  => ['sometimes', 'string', new Base64FileTypeRule('photo')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['text'])) {
            $data['text'] = '';
        }

        if (!isset($data['category_id'])) {
            $data['category_id'] = 0;
        }
        $data['user_ids'] = [];

        if (array_key_exists('users', $data)) {
            $users            = Arr::get($data, 'users', []);
            $data['user_ids'] = collect($users)->pluck('id')->toArray();
        }

        return $data;
    }

    /**
     * @return array<mixed>
     */
    public function messages(): array
    {
        return [
            'name.required'      => __p('core::validation.name.required'),
            'type_id.required'   => __p('core::validation.type_id.required'),
            'type_id.exists'     => __p('core::validation.type_id.exists'),
            'category_id.exists' => __p('core::validation.category_id.exists'),
            'external_link.url'  => __p('core::validation.external_link.url'),
        ];
    }
}
