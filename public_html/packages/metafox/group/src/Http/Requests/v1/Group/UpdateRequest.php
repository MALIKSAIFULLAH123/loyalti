<?php

namespace MetaFox\Group\Http\Requests\v1\Group;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\RegexRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Rules\UniqueSlug;

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
        $id = (int) $this->route('group');

        return [
            'name'        => ['required_if:exists,name', 'string', new ResourceNameRule('group'), new RegexRule('display_name')],
            'category_id' => ['sometimes', 'numeric', new CategoryRule(resolve(CategoryRepositoryInterface::class), $id)],
            'reg_method'  => ['sometimes', 'numeric', new AllowInRule(PrivacyTypeHandler::ALLOW_PRIVACY)],
            'vanity_url'  => [
                'sometimes',
                'string',
                'nullable',
                'max:100',
                new UniqueSlug(Group::ENTITY_TYPE, $id),
            ],
            'landing_page'     => ['sometimes', 'required', 'string'],
            'location'         => ['sometimes', 'nullable', 'array'],
            'text_description' => ['sometimes', 'string', 'nullable'],
            'text'             => ['sometimes', 'string', 'nullable'],
            'phone'            => ['sometimes', 'string', 'nullable'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (isset($data['reg_method'])) {
            $data['privacy_type'] = $data['reg_method'];
            unset($data['reg_method']);
        }

        if (isset($data['text_description'])) {
            $data['text'] = $data['text_description'];
            unset($data['text_description']);
        }

        if (Arr::has($data, 'vanity_url')) {
            $data['profile_name'] = str_replace(MetaFoxConstant::SLUGIFY_FILTERS, MetaFoxConstant::SLUGIFY_FILTERS_REPLACE, $data['vanity_url']);
            unset($data['vanity_url']);
        }

        if (Arr::has($data, 'text_description')) {
            $data['text'] = $data['text_description'];
            unset($data['text_description']);
        }

        if (Arr::has($data, 'location')) {
            if (is_array($location = Arr::get($data, 'location'))) {
                $data['location_address']      = Arr::get($location, 'full_address');
                $data['location_name']         = Arr::get($location, 'address');
                $data['location_latitude']     = Arr::get($location, 'lat');
                $data['location_longitude']    = Arr::get($location, 'lng');
            }

            unset($data['location']);
        }

        return $data;
    }

    /**
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'vanity_url.unique'  => __p('core::phrase.cannot_use_this_url_please_choose_another_one'),
            'name.required_if'   => __p('core::validation.name.required'),
            'type_id.required'   => __p('core::validation.type_id.required'),
            'type_id.exists'     => __p('core::validation.type_id.exists'),
            'category_id.exists' => __p('core::validation.category_id.exists'),
        ];
    }
}
