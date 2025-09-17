<?php

namespace MetaFox\Profile\Http\Requests\v1\Field\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\Profile\Rules\MinOptionsRule;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Profile\Support\Validation\ValidationFieldRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Profile\Http\Controllers\Api\v1\FieldAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

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
    public function rules()
    {
        $rules = [
            'section_id'         => ['int', 'sometimes', 'nullable'],
            'field_name'         => [
                'string', 'required', 'regex:/' . MetaFoxConstant::RESOURCE_IDENTIFIER_REGEX . '/',
                new CaseInsensitiveUnique('user_custom_fields', 'field_name'),
                new CaseInsensitiveUnique('user_custom_sections', 'name'),
                'max:' . CustomFieldSupport::MAX_NAME_LENGTH,
            ],
            'type_id'            => ['string', 'required'],
            'edit_type'          => ['string', 'sometimes'],
            'options'            => ['array', 'sometimes', new MinOptionsRule()],
            'options.*.value'    => ['array', 'sometimes', new TranslatableTextRule(true)],
            'options.*.ordering' => ['sometimes'],
            'options.*.status'   => [
                'string', new AllowInRule([
                    MetaFoxConstant::FILE_NEW_STATUS,
                    MetaFoxConstant::FILE_REMOVE_STATUS,
                    MetaFoxConstant::FILE_UPDATE_STATUS,
                ]),
            ],
            'view_type'          => ['string', 'required'],
            'var_type'           => ['string', 'required'],
            'is_active'          => ['sometimes', new AllowInRule([0, 1])],
            'is_required'        => ['sometimes', new AllowInRule([0, 1])],
            'is_register'        => ['sometimes', new AllowInRule([0, 1])],
            'is_search'          => ['sometimes', new AllowInRule([0, 1])],
            'is_feed'            => ['sometimes', new AllowInRule([0, 1])],
            'label'              => ['array', 'required', new TranslatableTextRule()],
            'description'        => ['array', 'sometimes', new TranslatableTextRule(true)],
            'has_label'          => ['int', 'sometimes'],
            'has_description'    => ['int', 'sometimes'],
            'extra'              => ['array', 'sometimes', 'nullable'],
        ];

        [, $validatedFieldRules,] = $this->getValidationField([], $this->input('edit_type'));

        return array_merge($rules, $this->fieldRolesRule('roles'), $this->fieldRolesRule('visible_roles'), $validatedFieldRules);
    }

    public function validated($key = null, $default = null)
    {
        $data     = parent::validated($key, $default);
        $editType = Arr::get($data, 'edit_type');

        if (isset($data['edit_type'])) {
            Arr::set($data, 'var_type', CustomFieldFacade::transformVarType($data['edit_type']));
            if (!in_array($data['edit_type'], CustomFieldFacade::getEditTypeAllowOptions())) {
                Arr::forget($data, 'options');
            }
        }

        $id = (int) $this->route('field');

        if ($id > 0) {
            /**@var FieldRepositoryInterface $fieldRepository */
            $fieldRepository = resolve(FieldRepositoryInterface::class);
            $editType        = $fieldRepository->find($id)?->edit_type;
        }

        $emptyPhrasesData = Language::getEmptyPhraseData();
        $labelData        = Language::extractPhraseData('label', $data);
        $descriptionData  = Language::extractPhraseData('description', $data);
        $data             = $this->handleFieldRoles($data);
        $data             = $this->handleFieldVisibleRoles($data);
        $data             = $this->handleOptions($data);

        Arr::set($data, 'label', !empty($labelData) ? $labelData : $emptyPhrasesData);
        Arr::set($data, 'description', !empty($descriptionData) ? $descriptionData : $emptyPhrasesData);

        [$data, ,] = $this->getValidationField($data, $editType);

        return $data;
    }

    public function messages(): array
    {
        $messages = [];
        $id       = (int) $this->route('field');
        $editType = $this->input('edit_type');

        if ($id > 0) {
            /**@var FieldRepositoryInterface $fieldRepository */
            $fieldRepository = resolve(FieldRepositoryInterface::class);
            $editType        = $fieldRepository->find($id)?->edit_type;
        }

        [, , $validatedFieldMessages] = $this->getValidationField([], $editType);

        return array_merge($messages, $validatedFieldMessages);
    }

    protected function fieldRolesRule(string $key): array
    {
        return [
            "$key"   => ['sometimes', 'array', 'nullable'],
            "$key.*" => ['sometimes', 'integer', 'nullable', new AllowInRule(CustomFieldFacade::getAllowedRole())],
        ];
    }

    protected function handleFieldRoles(array $data): array
    {
        if (!Arr::has($data, 'roles')) {
            return $data;
        }

        $roles = Arr::get($data, 'roles', []);
        if (is_array($roles)) {
            return $data;
        }

        if ($roles) {
            Arr::set($data, 'roles', [$roles]);
            return $data;
        }

        Arr::set($data, 'roles', []);

        return $data;
    }

    protected function handleFieldVisibleRoles(array $data): array
    {
        if (!Arr::has($data, 'visible_roles')) {
            return $data;
        }

        $roles = Arr::get($data, 'visible_roles', []);

        Arr::set($data, 'visibleRoles', $roles);
        Arr::forget($data, 'visible_roles');

        return $data;
    }

    protected function handleOptions(array $data): array
    {
        $options    = Arr::get($data, 'options', []);
        $newOptions = [];
        if (empty($options)) {
            return $data;
        }
        $emptyPhrasesData = Language::getEmptyPhraseData();

        foreach ($options as $key => $option) {
            if (!Arr::has($option, 'ordering')) {
                Arr::set($option, 'ordering', $key);
            }

            $value = Language::extractPhraseData('value', $option);
            Arr::set($option, 'label', !empty($value) ? $value : $emptyPhrasesData);

            Arr::forget($option, 'value');
            $newOptions[] = $option;
        }

        Arr::set($data, 'options', $newOptions);
        return $data;
    }

    protected function getValidationField(array $validated, string $editType = CustomFieldSupport::TEXT): array
    {
        $collection    = CustomFieldFacade::getDriverValidationField();
        $dataValidated = $rules = $messages = [];

        if ($collection->isEmpty()) {
            return [$validated, $rules, $messages];
        }

        foreach ($collection as $item) {
            $driver      = $item->driver;
            $driverClass = new $driver();

            if (!$driverClass instanceof ValidationFieldRule) {
                continue;
            }

            if (!in_array($editType, $driverClass->appliesEditingComponent())) {
                continue;
            }

            if (!empty($validated)) {
                $dataValidated = array_merge($dataValidated, $driverClass->configValidated($validated));
                $validated     = array_merge($validated, $this->fieldsErrorMessageValidated($driverClass, $validated));

                Arr::forget($validated, array_values($driverClass->fieldsName()));
            }

            $rules    = array_merge($rules, $this->fieldsErrorRules(class : $driverClass));
            $messages = array_merge($messages, $driverClass->configMessagesRule());
        }

        Arr::set($validated, 'extra.validation', $dataValidated);

        return [$validated, $rules, $messages];
    }

    protected function fieldsErrorRules(ValidationFieldRule $class): array
    {
        $rules = $class->configRules();
        $value = ['array', 'sometimes', new TranslatableTextRule(true)];

        foreach ($class->fieldsName() as $item) {
            if (!Arr::has($class->fieldsErrorMessageLabel(), $item)) {
                continue;
            }

            Arr::set($rules, sprintf(CustomFieldSupport::FIELD_NAME_ERROR_MESSAGE, $item), $value);
        }

        return $rules;
    }

    protected function fieldsErrorMessageValidated(ValidationFieldRule $class, array $data): array
    {
        $result           = [];
        $emptyPhrasesData = Language::getEmptyPhraseData();

        foreach ($class->fieldsName() as $item) {
            if (!Arr::has($class->fieldsErrorMessageLabel(), $item)) {
                continue;
            }

            $fieldName = sprintf(CustomFieldSupport::FIELD_NAME_ERROR_MESSAGE, $item);
            $labelData = Language::extractPhraseData($fieldName, $data);

            Arr::set($result, CustomFieldSupport::VALIDATION_MESSAGE . '.' . $item, !empty($labelData) ? $labelData : $emptyPhrasesData);
        }

        return $result;
    }
}
