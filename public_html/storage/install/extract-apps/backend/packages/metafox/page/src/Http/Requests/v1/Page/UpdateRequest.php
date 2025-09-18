<?php

namespace MetaFox\Page\Http\Requests\v1\Page;

use ArrayObject;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\RegexRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Rules\UniqueSlug;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    public const MIN_TITLE_LENGTH = 3;
    public const MAX_TITLE_LENGTH = 64;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules(): array
    {
        $id = (int) $this->route('page');

        $rules = new ArrayObject([
            'name' => [
                'sometimes', 'required', 'string', new ResourceNameRule('page'), new RegexRule('display_name'),
            ],
            'category_id' => [
                'sometimes', 'numeric', new CategoryRule(resolve(PageCategoryRepositoryInterface::class), $id),
            ],
            'vanity_url' => [
                'sometimes',
                'string',
                'nullable',
                new UniqueSlug(Page::ENTITY_TYPE, $id),
            ],
            'text'          => ['sometimes', 'string', 'nullable'],
            'landing_page'  => ['sometimes', 'string', 'nullable'],
            'location'      => ['sometimes', 'nullable', 'array'],
            'phone'         => ['sometimes', 'string', 'nullable'],
            'external_link' => ['sometimes', 'url', 'nullable'],
        ]);

        $payload    = $this->request->all();
        $fieldsName = CustomFieldFacade::loadFieldName(user(), [
            'view'         => CustomField::VIEW_ALL,
            'section_type' => CustomField::SECTION_TYPE_PAGE,
        ]);

        if (Arr::has($payload, $fieldsName)) {
            CustomFieldFacade::loadFieldEditRules(user(), $rules, ['section_type' => CustomField::SECTION_TYPE_PAGE]);
        }

        return $rules->getArrayCopy();
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (Arr::has($data, 'vanity_url')) {
            $data['profile_name'] = str_replace(MetaFoxConstant::SLUGIFY_FILTERS, MetaFoxConstant::SLUGIFY_FILTERS_REPLACE, $data['vanity_url']);
            unset($data['vanity_url']);
        }

        if (Arr::has($data, 'location')) {
            $data['location_name']      = Arr::get($data, 'location.address');
            $data['location_latitude']  = Arr::get($data, 'location.lat');
            $data['location_longitude'] = Arr::get($data, 'location.lng');
            $data['location_address']   = Arr::get($data, 'location.full_address');
            unset($data['location']);
        }

        $dataCustomProfile = $this->handleCustomProfile($data);

        return array_merge($data, $dataCustomProfile);
    }

    protected function handleCustomProfile(array $data): array
    {
        $fieldsName = CustomFieldFacade::loadFieldName(user(), [
            'view'         => CustomField::VIEW_ALL,
            'section_type' => CustomField::SECTION_TYPE_PAGE,
        ]);

        if (Arr::has($data, $fieldsName)) {
            $data = CustomFieldFacade::handleCustomProfileFieldsForEdit(user(), $data, [
                'section_type' => CustomField::SECTION_TYPE_PAGE,
                'view'         => CustomField::VIEW_ALL,
            ]);
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
            'name.required'      => __p('core::validation.name.required'),
            'category_id.exists' => __p('core::validation.category_id.exists'),
            'external_link.url'  => __p('core::validation.external_link.url'),
        ];
    }
}
