<?php

namespace MetaFox\Profile\Contracts;

use ArrayObject;
use Illuminate\Support\Collection;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Section as SectionForm;
use MetaFox\Platform\Contracts\User;
use MetaFox\Profile\Models\Field;
use MetaFox\Yup\MixedShape;

interface CustomFieldSupportInterface
{
    /**
     * @return array
     */
    public function getEditTypeAllowOptions(): array;

    /**
     * @return array
     */
    public function getAllowSectionType(): array;

    /**
     * @param string $type
     *
     * @return string
     */
    public function transformVarType(string $type): string;

    /**
     * @param string $type
     *
     * @return string
     */
    public function transformVarTypeForSearch(string $type): string;

    /**
     * @param string  $type
     * @param ?string $resolution
     *
     * @return string
     */
    public function transformForSearch(string $type, ?string $resolution = null): string;

    /**
     * @param string  $varType
     * @param ?string $editType
     *
     * @return string
     */
    public function transformYupType(string $varType, ?string $editType = null): string;

    /**
     * @param string $type
     * @param mixed  $value
     *
     * @return mixed
     */
    public function transformValueForForm(string $type, mixed &$value): mixed;

    /**
     * @param string $type
     * @param mixed  $value
     * @param array  $options
     *
     * @return mixed
     */
    public function transformValueForSection(string $type, mixed &$value, array $options): mixed;

    /**
     * @param string $type
     *
     * @return bool
     */
    public function allowHtml(string $type): bool;

    /**
     * @param string $type
     *
     * @return bool
     */
    public function allowLink(string $type): bool;

    /**
     * @return array
     */
    public function getAllowedRoleOptions(): array;

    /**
     * @return array
     */
    public function getAllowedVisibleRoleOptions(): array;

    /**
     * @return array
     */
    public function getAllowedRole(): array;

    /**
     * @param User|null    $user
     * @param \ArrayObject $rules
     * @param array        $attributes
     */
    public function loadFieldEditRules(?User $user, ArrayObject $rules, array $attributes): void;

    /**
     * @param AbstractForm $form
     * @param User         $user
     * @param array        $attributes
     *
     * @return void
     */
    public function loadFieldsEdit(AbstractForm $form, User $user, array $attributes): void;

    /**
     * @param ?User $user
     * @param array $attributes
     *
     * @return array
     */
    public function loadFieldName(?User $user, array $attributes): array;

    /**
     * @param SectionForm $section
     * @param array       $attributes
     *
     * @return void
     */
    public function loadFieldSearch(SectionForm $section, array $attributes): void;

    /**
     * @param ArrayObject $rules
     * @param string      $sectionType
     *
     * @return void
     */
    public function loadFieldSearchRules(ArrayObject $rules, string $sectionType): void;

    /**
     * @param SectionForm $section
     * @param array       $attributes
     *
     * @return void
     */
    public function loadFieldRegistration(SectionForm $section, array $attributes): void;

    /**
     * @param ArrayObject $rules
     * @param array       $attributes
     *
     * @return void
     */
    public function loadFieldRegistrationRules(ArrayObject $rules, array $attributes): void;

    /**
     * @param array $types
     *
     * @return array
     */
    public function getFieldIdsByTypes(array $types): array;

    /**
     * @param array $data
     * @param array $attributes
     *
     * @return array
     */
    public function handleValidatedCustomFieldsForSearch(array $data, array $attributes): array;

    /**
     * @param User|null $user
     * @param array     $data
     * @param array     $attributes
     *
     * @return array
     */
    public function handleCustomProfileFieldsForEdit(?User $user, array $data, array $attributes): array;

    /**
     * @param User|null $context
     * @param User      $user
     * @param array     $data
     * @param array     $attributes
     *
     * @return array
     */
    public function filterVisibleRoleFieldsForEdit(?User $context, User $user, array $data, array $attributes): array;

    /**
     * @param Field $field
     *
     * @return MixedShape
     */
    public function setYupValidationRules(Field $field): MixedShape;

    /**
     * @param Field $field
     * @param mixed $yup
     *
     * @return void
     */
    public function setValidationMessage(Field $field, MixedShape $yup): void;

    /**
     * @return Collection
     */
    public function getDriverValidationField(): Collection;

    /**
     * @param Field $field
     * @param array $attributes
     *
     * @return void
     */
    public function createValidationErrorMessage(Field $field, array $attributes): void;

    /**
     * @param Field  $field
     * @param string $fieldName
     *
     * @return string
     */
    public function getValidationErrorMessage(Field $field, string $fieldName): string;

    /**
     * @param Field $field
     *
     * @return array
     */
    public function toRule(Field $field): array;

    /**
     * @param ?User $user
     * @param array $attributes
     *
     * @return array
     */
    public function handleFieldValidationErrorMessage(?User $user, array $attributes): array;

    /**
     * @param Field  $field
     * @param string $type
     *
     * @return string
     */
    public function getKeyPhraseErrorMessage(Field $field, string $type): string;

    /**
     * @param User $context
     * @param User $user
     *
     * @return bool
     */
    public function checkVisibleRole(User $context, User $user): bool;

    /**
     * @return bool
     */
    public function isEnabledRelationshipStatus(): bool;
}
