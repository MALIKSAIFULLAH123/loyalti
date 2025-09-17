<?php

namespace MetaFox\Profile\Support\Validation;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

abstract class ValidationFieldRule extends AbstractForm
{
    protected Section $section;
    protected string  $relationField = 'edit_type';

    /**
     * @return string
     */
    public function getRelationField(): string
    {
        return $this->relationField;
    }

    /**
     * @param string $relationField
     *
     * @return void
     */
    public function setRelationField(string $relationField): void
    {
        $this->relationField = $relationField;
    }

    /**
     * @return Section
     */
    public function getSection(): Section
    {
        return $this->section;
    }

    /**
     * @param Section $section
     *
     * @return void
     */
    public function setSection(Section $section): void
    {
        $this->section = $section;
    }

    /**
     * @return array
     */
    public function getShowWhen(): array
    {
        return ['includes', $this->getRelationField(), $this->appliesEditingComponent()];
    }

    /**
     * @return array
     */
    public function getShowWhenByParent(): array
    {
        return $this->getShowWhen();
    }

    /**
     * @return array
     */
    public function configRules(): array
    {
        return [];
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    public function inputRules(array $rules): array
    {
        $result = [];
        foreach ($rules as $key => $value) {
            $result = array_merge($result, ["$key:$value"]);
        }

        return $result;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function configValidated($data): array
    {
        $result = [];
        foreach ($this->fieldsName() as $item) {
            $value = Arr::get($data, $item);
            Arr::set($result, $item, $value ?? MetaFoxConstant::EMPTY_STRING);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function configMessagesRule(): array
    {
        return [];
    }

    /**
     * @param Field $field
     * @param array $data
     *
     * @return array
     */
    public function inputMessagesRule(Field $field, array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $errorMessage = CustomFieldFacade::getValidationErrorMessage($field, $key);

            $result = array_merge($result, [$field->key . '.' . $key => $errorMessage]);
        }

        return $result;
    }

    public function getValuesDefault(): array
    {
        return [];
    }
}
