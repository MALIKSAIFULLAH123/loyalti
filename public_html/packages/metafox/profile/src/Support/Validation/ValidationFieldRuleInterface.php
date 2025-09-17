<?php

namespace MetaFox\Profile\Support\Validation;

use MetaFox\Profile\Models\Field;
use MetaFox\Yup\MixedShape;

interface ValidationFieldRuleInterface
{
    /**
     * @return void
     */
    public function getFields(): void;

    /**
     * @return array
     */
    public function appliesEditingComponent(): array;

    /**
     * @return array
     */
    public function configRules(): array;

    /**
     * @param $data
     *
     * @return array
     */
    public function configValidated($data): array;

    /**
     * @return array
     */
    public function configMessagesRule(): array;

    /**
     * @param array $rules
     *
     * @return array
     */
    public function inputRules(array $rules): array;

    /**
     * @param Field $field
     * @param array $data
     *
     * @return array
     */
    public function inputMessagesRule(Field $field, array $data): array;

    /**
     * @return array
     */
    public function fieldsName(): array;

    /**
     * @return array
     * Establish the relationship between the label field, message, and field names.
     */
    public function fieldsErrorMessageLabel(): array;

    /**
     * @param MixedShape $yup
     * @param Field      $field
     * @param array      $rules
     *
     * @return void
     */
    public function setYupFieldParent(MixedShape $yup, Field $field, array $rules): void;

    /**
     * @return array
     */
    public function getValuesDefault(): array;

    /**
     * @return array
     */
    public function getShowWhenByParent(): array;
}
