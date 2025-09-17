<?php

namespace MetaFox\Profile\Support\Facade;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Section as SectionForm;
use MetaFox\Platform\Contracts\User;
use MetaFox\Profile\Contracts\CustomFieldSupportInterface;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;
use MetaFox\Yup\MixedShape;

/**
 * @method static array      getEditTypeAllowOptions()
 * @method static array      getAllowSectionType()
 * @method static string     transformVarType(string $type)
 * @method static string     transformVarTypeForSearch(string $type)
 * @method static string     transformForSearch(string $varType, ?string $resolution = null)
 * @method static string     transformYupType(string $varType, ?string $editType)
 * @method static bool       allowHtml(string $type)
 * @method static bool       allowLink(string $type)
 * @method static bool       checkVisibleRole(User $context, User $user)
 * @method static mixed      transformValueForForm(string $type, mixed &$value)
 * @method static mixed      transformValueForSection(string $type, mixed &$value, array $options)
 * @method static array      getAllowedRoleOptions()
 * @method static array      getAllowedVisibleRoleOptions()
 * @method static array      getAllowedRole()
 * @method static array      getFieldIdsByTypes(array $types)
 * @method static array      loadFieldName(?User $user, array $attributes)
 * @method static void       loadFieldEditRules(?User $user, \ArrayObject $rules, array $attributes)
 * @method static void       loadFieldsEdit(AbstractForm $form, User $user, array $attributes)
 * @method static void       loadFieldSearchRules(\ArrayObject $rules, string $sectionType)
 * @method static void       loadFieldRegistrationRules(\ArrayObject $rules, array $attributes)
 * @method static void       loadFieldSearch(SectionForm $section, array $attributes)
 * @method static void       loadFieldRegistration(SectionForm $section, array $attributes, ?string $resolution = null)
 * @method static array      handleValidatedCustomFieldsForSearch(array $data, array $attributes)
 * @method static array      handleCustomProfileFieldsForEdit(?User $user, array $data, array $attributes)
 * @method static array      filterVisibleRoleFieldsForEdit(?User $context, User $user, array $data, array $attributes)
 * @method static array      toRule(Field $field)
 * @method static array      handleFieldValidationErrorMessage(?User $user, array $attributes)
 * @method static void       setValidationMessage(Field $field, MixedShape $yup)
 * @method static void       createValidationErrorMessage(Field $field, array $attributes)
 * @method static string     getValidationErrorMessage(Field $field, string $fieldName)
 * @method static string     getKeyPhraseErrorMessage(Field $field, string $type)
 * @method static MixedShape setYupValidationRules(Field $field)
 * @method static Collection getDriverValidationField()
 * @method static bool       isEnabledRelationshipStatus()
 *
 * @see CustomFieldSupport
 */
class CustomField extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CustomFieldSupportInterface::class;
    }
}
