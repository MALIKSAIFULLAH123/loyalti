<?php

namespace MetaFox\Profile\Http\Resources\v1\Field\Validation;

use MetaFox\Form\Builder;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Profile\Support\Rules\RegexRule;
use MetaFox\Profile\Support\Validation\ValidationFieldRule;
use MetaFox\Profile\Support\Validation\ValidationFieldRuleInterface;
use MetaFox\Yup\MixedShape;

/**
 * @driverName validation.regex
 * @driverType custom-field-validator
 * @resolution admin
 */
class RegexField extends ValidationFieldRule implements ValidationFieldRuleInterface
{
    public function getFields(): void
    {
        $this->section->addFields(
            Builder::text('regex')
                ->label(__p('profile::phrase.regex'))
                ->showWhen($this->getShowWhen()),
        );
    }

    public function appliesEditingComponent(): array
    {
        return [CustomField::TEXT];
    }

    public function configRules(): array
    {
        return [
            'regex' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function fieldsName(): array
    {
        return ['regex'];
    }

    /**
     * @return array
     * Establish the relationship between the label field, message, and field names.
     */
    public function fieldsErrorMessageLabel(): array
    {
        return [
            'regex' => __p('profile::phrase.regex_message'),
        ];
    }

    public function inputRules(array $rules): array
    {
        $result = [];
        foreach ($rules as $key => $value) {
            if (!in_array($key, $this->fieldsName())) {
                continue;
            }

            $result = array_merge($result, [new RegexRule($key, $value)]);
        }

        return $result;
    }

    public function setYupFieldParent(MixedShape $yup, Field $field, array $rules): void
    {
        foreach ($rules as $key => $value) {
            if (!in_array($key, $this->fieldsName())) {
                continue;
            }

            $errorMessage = CustomFieldFacade::getValidationErrorMessage($field, $key);
            $yup->matches($value, $errorMessage);
        }
    }

    public function inputMessagesRule(Field $field, array $data): array
    {
        return [];
    }
}
