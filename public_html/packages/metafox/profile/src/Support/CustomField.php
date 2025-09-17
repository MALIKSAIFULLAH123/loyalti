<?php

namespace MetaFox\Profile\Support;

use ArrayObject;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\FormField;
use MetaFox\Form\Section as SectionForm;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\UserRole;
use MetaFox\Profile\Contracts\CustomFieldSupportInterface;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Section;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Profile\Support\Validation\ValidationFieldRule;
use MetaFox\Yup\MixedShape;
use MetaFox\Yup\Yup;

class CustomField implements CustomFieldSupportInterface
{
    public const TEXT             = 'text';
    public const RICH_TEXT_EDITOR = 'richTextEditor';
    public const RADIO_GROUP      = 'radioGroup';
    public const DROPDOWN         = 'dropdown';
    public const CHOICE           = 'choice';
    public const TEXT_AREA        = 'textArea';
    public const CHECK_BOX        = 'checkbox';
    public const MULTI_CHOICE     = 'multiChoice';
    public const DATE             = 'date';
    public const BASIC_DATE       = 'basicDate';
    public const URL              = 'url';

    public const SEARCH_TEXT_BOX_FIELD = 'searchTextBox';
    public const SWITCH_FIELD          = 'switch';
    public const TYPE_INT              = 'int';
    public const TYPE_STRING           = 'string';
    public const TYPE_ARRAY            = 'array';
    public const MAX_NAME_LENGTH       = 32;
    public const SECTION_TYPE_USER     = 'user';
    public const SECTION_TYPE_PAGE     = 'page';
    public const SECTION_TYPE_GROUP    = 'group';

    public const FIELD_USER_TYPE_NAME = 'field_%s_%s';
    public const VIEW_ALL             = 'all';
    public const VIEW_SEARCH          = 'search';
    public const VIEW_REGISTRATION    = 'registration';

    public const BASIC_DATE_FORMAT        = 'd/m/Y';
    public const BASIC_DATE_CLIENT_FORMAT = 'DD/MM/YYYY';
    public const BASIC_DATE_REGEX         = '/\d{2}\/\d{2}\/\d{4}/';

    public const BASIC_DATE_RENDER_AS          = 'Date';
    public const FIELD_NAME_ERROR_MESSAGE      = '%s_error_message';
    public const VALIDATION_MESSAGE            = 'validation_message';
    public const KEY_PHRASE_VALIDATION_MESSAGE = 'profile::phrase.%s_validation_%s';

    public const RELATIONSHIP_FIELD_NAME = 'relationship';

    public function __construct(
        protected FieldRepositoryInterface $fieldRepository,
        protected SectionRepositoryInterface $sectionRepository,
        protected RoleRepositoryInterface $roleRepository,
    ) {
    }

    public function getEditTypeAllowOptions(): array
    {
        return [
            self::MULTI_CHOICE,
            self::CHOICE,
            self::RADIO_GROUP,
        ];
    }

    public function getAllowSectionType(): array
    {
        return [
            self::SECTION_TYPE_USER,
            self::SECTION_TYPE_PAGE,
            self::SECTION_TYPE_GROUP,
        ];
    }

    public function transformVarType(string $type): string
    {
        $array = [
            self::MULTI_CHOICE => self::TYPE_ARRAY,
            self::DROPDOWN     => self::TYPE_INT,
            self::CHOICE       => self::TYPE_INT,
            self::RADIO_GROUP  => self::TYPE_INT,
            self::CHECK_BOX    => self::TYPE_INT,
        ];

        if (!Arr::has($array, $type)) {
            return self::TYPE_STRING;
        }

        return $array[$type];
    }

    public function transformForSearch(string $type, ?string $resolution = null): string
    {
        $array = [
            self::TEXT             => self::SEARCH_TEXT_BOX_FIELD,
            self::TEXT_AREA        => self::SEARCH_TEXT_BOX_FIELD,
            self::RICH_TEXT_EDITOR => self::SEARCH_TEXT_BOX_FIELD,
            self::URL              => self::SEARCH_TEXT_BOX_FIELD,
            self::RADIO_GROUP      => self::CHOICE,
            self::CHECK_BOX        => self::SWITCH_FIELD,
            self::MULTI_CHOICE     => self::CHOICE,
        ];

        if ($resolution == MetaFoxConstant::RESOLUTION_ADMIN) {
            $array = array_merge($array, [
                self::TEXT             => self::TEXT,
                self::TEXT_AREA        => self::TEXT,
                self::RICH_TEXT_EDITOR => self::TEXT,
                self::URL              => self::TEXT,
            ]);

            unset($array[self::CHECK_BOX]);
        }

        if (!Arr::has($array, $type)) {
            return $type;
        }

        return $array[$type];
    }

    public function transformYupType(string $varType, ?string $editType = null): string
    {
        $typeMap = [
            self::TYPE_INT    => 'number',
            self::TYPE_STRING => [
                self::DATE       => 'date',
                self::BASIC_DATE => 'date',
            ],
        ];

        if (!array_key_exists($varType, $typeMap)) {
            return $varType;
        }

        $type = $typeMap[$varType];

        if (is_string($type)) {
            return $type;
        }

        return $type[$editType] ?? $varType;
    }

    //@TODO refactor this => move to database
    public function allowHtml(string $type): bool
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if (!$settingAllowHtml) {
            return false;
        }

        $allowType = [
            self::RICH_TEXT_EDITOR,
        ];

        if (in_array($type, $allowType)) {
            return true;
        }

        return false;
    }

    public function allowLink(string $type): bool
    {
        $allowType = [
            self::RICH_TEXT_EDITOR,
            self::TEXT_AREA,
            self::URL,
        ];

        if (in_array($type, $allowType)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function transformValueForForm(string $type, mixed &$value): mixed
    {
        switch ($type) {
            case self::MULTI_CHOICE:
                $value = $value ? json_decode($value) : [];
                break;
            case self::RADIO_GROUP:
            case self::CHOICE:
                $value = $value ? (int) $value : null;
                break;
            case self::BASIC_DATE:
                $value = $this->getBasicDateValueForForm($value);
                break;
        }

        return $value;
    }

    private function getBasicDateValueForSection(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (preg_match(self::BASIC_DATE_REGEX, $value)) {
            return $value;
        }

        return Carbon::parse($value)->setTimezone(MetaFox::clientTimezone())->format(self::BASIC_DATE_FORMAT);
    }

    private function getBasicDateValueForForm(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $isDate = preg_match(self::BASIC_DATE_REGEX, $value);

        /*
         * Compatible with old mobile version when using Date component.
         */
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.9', '<') && $isDate) {
            return Carbon::createFromFormat('d/m/Y', $value, MetaFox::clientTimezone())
                ->startOfDay()
                ->utc()
                ->toIso8601ZuluString('millisecond');
        }

        if ($isDate) {
            return $value;
        }

        /*
         * If value of custom field is incompatible, we need to parse from zulu format to basic date format with client timezone.
         */
        return Carbon::parse($value)->setTimezone(MetaFox::clientTimezone())->format(self::BASIC_DATE_FORMAT);
    }

    /**
     * @inheritDoc
     */
    public function transformValueForSection(string $type, mixed &$value, array $options): mixed
    {
        switch ($type) {
            case self::MULTI_CHOICE:
                $valueOptions = [];

                if (!is_array($value)) {
                    $value = json_decode($value);
                }

                if (empty($options) || empty($value)) {
                    return $value;
                }

                foreach ($value as $key) {
                    $valueOptions[] = Arr::get($options, $key);
                }

                if (!MetaFox::isMobile() || version_compare(MetaFox::getApiVersion(), 'v1.16', '>=')) {
                    return $valueOptions;
                }

                $value = implode(', ', $valueOptions);
                break;
            case self::CHECK_BOX:
                $value = (bool) $value;
                break;
            case self::RADIO_GROUP:
            case self::CHOICE:
                $value = Arr::get($options, $value);
                break;
            case self::BASIC_DATE:
                $value = $this->getBasicDateValueForSection($value);
                break;
        }

        if ($value && !self::allowHtml($type)) {
            $value = htmlspecialchars($value);
        }

        if (self::allowLink($type)) {
            $value = parse_output()->parseUrl($value);
        }

        if ($this->shouldCleanBannedWord($type)) {
            $value = ban_word()->clean($value);
        }

        return $value;
    }

    protected function shouldCleanBannedWord(string $type): bool
    {
        $allowType = [self::TEXT, self::TEXT_AREA, self::URL];

        return in_array($type, $allowType);
    }

    /**
     * @inheritDoc
     */
    public function transformVarTypeForSearch(string $type): string
    {
        $array = [
            self::MULTI_CHOICE => self::TYPE_INT,
            self::DROPDOWN     => self::TYPE_INT,
            self::CHOICE       => self::TYPE_INT,
            self::RADIO_GROUP  => self::TYPE_INT,
            self::CHECK_BOX    => self::TYPE_INT,
        ];

        if (!Arr::has($array, $type)) {
            return self::TYPE_STRING;
        }

        return $array[$type];
    }

    public function getAllowedRoleOptions(): array
    {
        /** @var RoleRepositoryInterface $roleRepository */
        $roleRepository = resolve(RoleRepositoryInterface::class);
        $roles          = $roleRepository->getRoleOptions();

        $disallowedRoleIds = [UserRole::GUEST_USER, UserRole::BANNED_USER];

        return array_filter($roles, function ($role) use ($disallowedRoleIds) {
            return !in_array($role['value'], $disallowedRoleIds);
        });
    }

    public function getAllowedVisibleRoleOptions(): array
    {
        $roles             = self::getAllowedRoleOptions();
        $disallowedRoleIds = [UserRole::SUPER_ADMIN_USER];

        return array_filter($roles, function ($role) use ($disallowedRoleIds) {
            return !in_array($role['value'], $disallowedRoleIds);
        });
    }

    public function getAllowedRole(): array
    {
        return Arr::pluck($this->getAllowedRoleOptions(), 'value');
    }

    /**
     * @inheritDoc
     * @throws AuthenticationException
     */
    public function loadFieldEditRules(?User $user, ArrayObject $rules, array $attributes): void
    {
        $context     = user();
        $collections = $this->fieldRepository->getFieldCollections($user, $context, $attributes);

        if ($collections->isEmpty()) {
            return;
        }

        foreach ($collections as $field) {
            /** @var $field Field */
            $methodName = 'to' . Str::studly($field->field_name) . 'Rule';
            if (!method_exists($this, $methodName)) {
                $rules[$field->name] = $this->toRule($field);
                continue;
            }

            $requireBasicField = $field->is_required ? ['required'] : ['sometimes', 'nullable'];
            $this->$methodName($rules, $requireBasicField);
        }
    }

    public function toRule(Field $field): array
    {
        $varType = CustomFieldFacade::transformVarType($field->edit_type);
        $rules   = [$varType, 'sometimes', 'nullable'];

        if ($field->is_required) {
            $rules = ['required', $varType];
        }

        if (!empty($field->extra)) {
            $rules = array_merge($rules, $this->getRulesExtra($field->extra, $field->edit_type));
        }

        return $rules;
    }

    protected function toRelationshipRule(ArrayObject $rules, array $requireBasicField): void
    {
        $rules['relation']           = ['sometimes', 'nullable', 'numeric', new ExistIfGreaterThanZero('exists:user_relation,id')];

        if (self::isEnabledRelationshipStatus()) {
            $rules['relation_with']    = ['sometimes', 'nullable', 'array'];
            $rules['relation_with.id'] = ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')];
        }
    }

    protected function toGenderRule(ArrayObject $rules, array $requireBasicField): void
    {
        $rules['gender']        = [...$requireBasicField, 'nullable', 'numeric', new ExistIfGreaterThanZero('exists:user_gender,id')];
        $rules['custom_gender'] = [
            'required_if:gender,0', 'nullable', 'numeric', new ExistIfGreaterThanZero('exists:user_gender,id'),
        ];
    }

    protected function toBirthdateRule(ArrayObject $rules, array $requireBasicField): void
    {
        $rules['birthday'] = [...$requireBasicField, 'date'];
    }

    protected function toLocationRule(ArrayObject $rules, array $requireBasicField): void
    {
        $rules['postal_code']         = ['sometimes', 'nullable', 'string'];
        $rules['country_iso']         = [...$requireBasicField, 'string', 'min:2'];
        $rules['country_state_id']    = ['sometimes', 'nullable', 'string'];
        $rules['country_state']       = ['sometimes', 'nullable', 'array'];
        $rules['country_state.value'] = ['sometimes', 'nullable', 'string'];
        $rules['country_city_code']   = ['sometimes', 'nullable'];
    }

    protected function toAddressRule(ArrayObject $rules, array $requireBasicField): void
    {
        $rules['address'] = ['sometimes', 'nullable', 'string'];
    }

    private function getRulesExtra(array $extra, string $editType = self::TEXT): array
    {
        $collection = $this->getDriverValidationField();
        $validation = Arr::get($extra, 'validation', []);
        $rules      = [];

        foreach ($collection as $item) {
            $driver      = $item->driver;
            $driverClass = new $driver();

            if (!$driverClass instanceof ValidationFieldRule) {
                continue;
            }

            if (!in_array($editType, $driverClass->appliesEditingComponent())) {
                continue;
            }

            $rules = array_merge($rules, $driverClass->inputRules($validation));
        }

        return $rules;
    }

    public function loadFieldsEdit(AbstractForm $form, User $user, array $attributes): void
    {
        $resolution = Arr::get($attributes, 'resolution', MetaFoxConstant::RESOLUTION_WEB);
        $sections   = $this->sectionRepository->buildQuerySection($user, $attributes)->get();

        if ($sections->isEmpty()) {
            return;
        }

        /** @var Section[] $sections */
        foreach ($sections as $item) {
            if (!$item instanceof Section) {
                continue;
            }

            $section = $form->addSection($item->name)
                ->label($item->label)
                ->description($item->description);

            if (!$item->is_system) {
                $this->loadFieldInSection($section, $user, $item->id, $resolution);
                continue;
            }

            if (method_exists($form, 'getFieldInBasicInfoSection')) {
                $form->getFieldInBasicInfoSection($section);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function loadFieldName(?User $user, array $attributes): array
    {
        $view        = Arr::get($attributes, 'view', self::VIEW_ALL);
        $sectionType = Arr::get($attributes, 'section_type', self::SECTION_TYPE_USER);
        $data        = [];

        $collections = match ($view) {
            self::VIEW_SEARCH       => $this->fieldRepository->getFieldSearch($sectionType),
            self::VIEW_REGISTRATION => $this->fieldRepository->getFieldRegistration($attributes),
            default                 => $this->fieldRepository->getFieldsActiveCollectionByType($user, $attributes)
        };

        if ($collections->isEmpty()) {
            return $data;
        }

        foreach ($collections as $field) {
            /* @var $field Field */
            $data[] = $field->name;
        }

        return $data;
    }

    /**
     * @param SectionForm $section
     * @param array       $attributes
     *
     * @return void
     */
    public function loadFieldSearch(SectionForm $section, array $attributes): void
    {
        $sectionType = Arr::get($attributes, 'section_type', self::SECTION_TYPE_USER);
        $resolution  = Arr::get($attributes, 'resolution', MetaFoxConstant::RESOLUTION_WEB);
        $fields      = $this->fieldRepository->getFieldSearch($sectionType);

        if ($fields->isEmpty()) {
            return;
        }

        /** @var Field[] $fields */
        foreach ($fields as $field) {
            $formField  = $field->toSearchField($resolution);
            $fieldRoles = $field->roles;

            if (null === $formField) {
                continue;
            }

            switch ($resolution) {
                case MetaFoxConstant::RESOLUTION_ADMIN:
                    $formField->forAdminSearchForm()
                        ->label($field->editingLabel)
                        ->description(null)
                        ->showWhen(['and', ['truthy', 'view_more']]);

                    break;
                default:
                    $relationField = Arr::get($attributes, 'relation_field', 'subscription_package_id');

                    if ($fieldRoles->isNotEmpty()) {
                        $formField->showWhen(['includes', $relationField, $fieldRoles->pluck('id')->toArray()]);
                    }

                    break;
            }

            $section->addFields($formField);
        }
    }

    /**
     * @param ArrayObject $rules
     * @param string      $sectionType
     *
     * @return void
     */
    public function loadFieldSearchRules(ArrayObject $rules, string $sectionType): void
    {
        $fields = $this->fieldRepository->getFieldSearch($sectionType);

        if ($fields->isEmpty()) {
            return;
        }

        /** @var Field[] $fields */
        foreach ($fields as $field) {
            $rules[$field->name] = $field->toSearchRule();
        }
    }

    /**
     * @param SectionForm $section
     * @param array       $attributes
     *
     * @return void
     */
    public function loadFieldRegistration(SectionForm $section, array $attributes): void
    {
        $attributes       = $this->handleSubscriptionPackageRegistration($attributes);
        $fields           = $this->fieldRepository->getFieldRegistration($attributes);
        $sectionBasicInfo = $this->sectionRepository->getSectionByName('basic_info');

        if ($fields->isEmpty()) {
            return;
        }

        //Don't loadding fields in section basic information
        if ($sectionBasicInfo instanceof Section) {
            $fields = $fields->where('section_id', '!=', $sectionBasicInfo->entityId());
        }

        $resolution = Arr::get($attributes, 'resolution', MetaFoxConstant::RESOLUTION_WEB);

        /** @var Field[] $fields */
        foreach ($fields as $field) {
            $formField  = $field->toEditField($resolution);
            $fieldRoles = $field->roles;

            if (!$formField instanceof FormField) {
                continue;
            }

            $showWhenField = $this->handleShowWhenByRole($fieldRoles, $attributes);

            if (!empty($showWhenField)) {
                $formField->showWhen($showWhenField);
                if ($field->is_required) {
                    $formField->requiredWhen($showWhenField);
                    $formField->required(false);
                }
            }

            $yup = $this->handleYupValidationWhenByRole($fieldRoles, $field, $attributes);
            $formField->yup($yup);

            $section->addField($formField);
        }
    }

    /**
     * @param ArrayObject $rules
     * @param array       $attributes
     *
     * @return void
     */
    public function loadFieldRegistrationRules(ArrayObject $rules, array $attributes): void
    {
        $fields = $this->fieldRepository->getFieldRegistration($attributes);

        if ($fields->isEmpty()) {
            return;
        }

        /** @var Field[] $fields */
        foreach ($fields as $field) {
            $methodName = 'to' . Str::studly($field->field_name) . 'Rule';

            if (!method_exists($this, $methodName)) {
                $rules[$field->name] = $this->toRule($field);
                continue;
            }

            $requireBasicField = $field->is_required ? ['required'] : ['sometimes', 'nullable'];
            $this->$methodName($rules, $requireBasicField);
        }
    }

    /**
     * @param SectionForm $section
     * @param User        $user
     * @param int         $id
     * @param string|null $resolution
     *
     * @return void
     */
    private function loadFieldInSection(SectionForm $section, User $user, int $id, ?string $resolution = null): void
    {
        $context = Auth::user();
        if (!$context instanceof User) {
            return;
        }

        $table = $this->fieldRepository->getModel()->getTable();
        $query = $this->fieldRepository->getBuildQuery($user, ['section_id' => $id]);
        $query = $this->fieldRepository->buildQueryRoles($user, $query);

        if ($this->checkVisibleRole($context, $user)) {
            /** @var RoleRepositoryInterface $roleRepository */
            $roleRepository = resolve(RoleRepositoryInterface::class);
            $contextRole    = $roleRepository->roleOf($context);
            $query          = $this->fieldRepository->buildQueryVisibleRoles($query, $contextRole->entityId());
        }

        $fields = $query->orderBy("$table.ordering")->get();

        if ($fields->isEmpty()) {
            return;
        }

        /** @var Field[] $fields */
        foreach ($fields as $field) {
            $formField = $field->toEditField($resolution);
            $yup       = $this->setYupValidationRules($field);

            $formField->yup($yup);

            $section->addField($formField);
        }
    }

    /**
     * @inheritDoc
     */
    public function getFieldIdsByTypes(array $types): array
    {
        return $this->fieldRepository->getFieldIdsByTypes($types);
    }

    /**
     * @param array $data
     * @param array $attributes
     *
     * @return array
     */
    public function handleValidatedCustomFieldsForSearch(array $data, array $attributes): array
    {
        $sectionType = Arr::get($attributes, 'section_type', self::SECTION_TYPE_USER);
        $view        = Arr::get($attributes, 'view', self::VIEW_ALL);
        $roleId      = Arr::get($data, 'group');
        $result      = [];
        $fields      = match ($view) {
            self::VIEW_SEARCH       => $this->fieldRepository->getFieldSearch($sectionType),
            self::VIEW_REGISTRATION => $this->fieldRepository->getFieldRegistration($attributes),
            default                 => collect([])
        };

        if ($fields->isEmpty()) {
            return $data;
        }

        foreach ($fields as $field) {
            /** @var Field $field */
            $fieldId    = $field->entityId();
            $fieldName  = $field->name;
            $fieldRoles = $field->roles;
            $value      = Arr::get($data, $fieldName);

            Arr::forget($data, $fieldName);

            if (null == $value) {
                continue;
            }

            if ($fieldRoles->isNotEmpty() && $fieldRoles->where('id', $roleId)->isEmpty()) {
                continue;
            }

            $result[] = [
                'id'    => $fieldId,
                'value' => $value,
            ];
        }

        if (!empty($result)) {
            Arr::set($data, 'custom_fields', $result);
        }

        return $data;
    }

    /**
     * @param User|null $user
     *
     * @inheritDoc
     */
    public function handleCustomProfileFieldsForEdit(?User $user, array $data, array $attributes): array
    {
        $view   = Arr::get($attributes, 'view', self::VIEW_ALL);
        $fields = match ($view) {
            self::VIEW_ALL          => $this->fieldRepository->getFieldsActiveCollectionByType($user, $attributes),
            self::VIEW_REGISTRATION => $this->fieldRepository->getFieldRegistration($attributes),
            default                 => collect([])
        };

        if ($fields->isEmpty()) {
            return $data;
        }

        foreach ($fields as $field) {
            /** @var $field Field */
            $fieldName = $field->name;
            if (!Arr::has($data, $fieldName)) {
                continue;
            }

            Arr::set($data, 'additional_information.' . $fieldName, Arr::get($data, $fieldName));
            Arr::forget($data, $fieldName);
        }

        return $data;
    }

    /**
     * @param User|null $user
     *
     * @inheritDoc
     */
    public function filterVisibleRoleFieldsForEdit(?User $context, User $user, array $data, array $attributes): array
    {
        if (!$context || !CustomFieldFacade::checkVisibleRole($context, $user)) {
            return $data;
        }

        $roles      = $this->roleRepository->roleOf($context);
        $collection = $this->fieldRepository->getFieldsActiveByVisibleRole($roles->entityId(), $attributes)->groupBy('key');
        $additional = Arr::get($data, 'additional_information');

        if ($collection->isEmpty() || empty($additional)) {
            return $data;
        }

        foreach ($additional as $key => $value) {
            if ($collection->offsetExists($key)) {
                continue;
            }

            Arr::forget($additional, $key);
        }

        Arr::set($data, 'additional_information', $additional);

        return $data;
    }

    /**
     * @param Field $field
     *
     * @return MixedShape
     */
    public function setYupValidationRules(Field $field): MixedShape
    {
        $yupType = CustomFieldFacade::transformYupType($field->var_type, $field->edit_type);

        $yup = match ($field->is_required) {
            true  => Yup::$yupType()->required(),
            false => Yup::$yupType()->nullable()
        };

        $this->handleYupExtra($field, $yup);
        $this->setValidationMessage($field, $yup);

        return $yup;
    }

    private function handleYupExtra(Field $field, MixedShape $yup): void
    {
        $extra = $field->extra;
        if (empty($extra)) {
            return;
        }

        $collection = $this->getDriverValidationField();
        $validation = Arr::get($extra, 'validation', []);

        foreach ($collection as $item) {
            $driver      = $item->driver;
            $driverClass = new $driver();

            if (!$driverClass instanceof ValidationFieldRule) {
                continue;
            }

            if (!in_array($field->edit_type, $driverClass->appliesEditingComponent())) {
                continue;
            }

            $driverClass->setYupFieldParent($yup, $field, $validation);
        }
    }

    /**
     * @param Field      $field
     * @param MixedShape $yup
     *
     * @return void
     */
    public function setValidationMessage(Field $field, MixedShape $yup): void
    {
        switch ($field->edit_type) {
            case self::DATE:
                $yup->setError('typeError', __p('core::phrase.invalid_date'));
                break;
            case self::BASIC_DATE:
                $yup->inputFormat('DD/MM/YYYY')
                    ->setError('typeError', __p('core::phrase.invalid_date'));

                break;
            case self::CHECK_BOX:
            case self::MULTI_CHOICE:
                if ($field->is_required) {
                    $yup->setError('typeError', __p('validation.field_is_a_required_field', ['field' => $field->label]));
                }

                break;
        }
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function handleSubscriptionPackageRegistration(array $attributes): array
    {
        $settingRoleId = Settings::get('user.on_register_user_group', UserRole::NORMAL_USER);
        Arr::set($attributes, 'setting_role_id', $settingRoleId);

        $subscriptionPackages = app('events')->dispatch('subscription.package_registration', [], true) ?? collect([]);
        $valuesShowWhen       = [];

        $subscriptionPackage = $subscriptionPackages->groupBy('upgraded_role_id');
        foreach ($subscriptionPackage as $key => $option) {
            $packageIds       = $option->pluck('id')->toArray();
            $valuesShowWhen[] = [
                'role_id'  => $key,
                'includes' => $packageIds,
            ];
        }

        if (!empty($valuesShowWhen)) {
            $roleId           = Arr::pull($attributes, 'role_id');
            $valuesShowWhen[] = [
                'role_id'  => $roleId,
                'includes' => [],
            ];
        }

        Arr::set($attributes, 'show_when', ['includes', 'subscription_package_id']);
        Arr::set($attributes, 'relation_field', 'subscription_package_id');
        Arr::set($attributes, 'values_show_when', $valuesShowWhen);

        return $attributes;
    }

    /**
     * @param Collection $fieldRoles
     * @param array      $attributes
     *
     * @return array
     */
    protected function handleShowWhenByRole(Collection $fieldRoles, array $attributes): array
    {
        $showWhenField  = Arr::get($attributes, 'show_when', []);
        $valuesShowWhen = Arr::get($attributes, 'values_show_when', []);
        $settingRoleId  = Arr::get($attributes, 'setting_role_id', 0);

        if (!in_array('includes', $showWhenField)) {
            return [];
        }

        if ($fieldRoles->isEmpty()) {
            return [];
        }

        $valuesShowWhen = $this->handleByPackage($valuesShowWhen, $fieldRoles);
        $packageIds     = Arr::get($valuesShowWhen, 'includes', []);

        if (empty($packageIds)) {
            if ($fieldRoles->where('id', $settingRoleId)->isNotEmpty()) {
                return [
                    'and',
                    ['falsy', 'subscription_package_id'],
                ];
            }
        }

        if ($fieldRoles->where('id', $settingRoleId)->isEmpty()) {
            return [
                'and',
                ['truthy', 'subscription_package_id'],
                ['includes', 'subscription_package_id', $packageIds],
            ];
        }

        return [
            'or',
            ['falsy', 'subscription_package_id'],
            ['includes', 'subscription_package_id', $packageIds],
        ];
    }

    /**
     * @param array      $attributes
     * @param Collection $fieldRoles
     *
     * @return array
     */
    protected function handleByPackage(array $attributes, Collection $fieldRoles): array
    {
        $includes = [];
        if (empty($attributes)) {
            return $includes;
        }

        $result = [];
        foreach ($attributes as $attribute) {
            $roleId = Arr::get($attribute, 'role_id');
            if ($fieldRoles->where('id', $roleId)->isNotEmpty()) {
                $includes = array_merge($includes, Arr::get($attribute, 'includes', []));
                $result   = $attribute;
            }
        }

        Arr::set($result, 'includes', $includes);

        return $result;
    }

    /**
     * @param Collection $fieldRoles
     * @param Field      $field
     * @param array      $attributes
     *
     * @return MixedShape
     */
    protected function handleYupValidationWhenByRole(Collection $fieldRoles, Field $field, array $attributes): MixedShape
    {
        $showWhen       = Arr::get($attributes, 'show_when', []);
        $relationField  = Arr::get($attributes, 'relation_field');
        $settingRoleId  = Arr::get($attributes, 'setting_role_id', 0);
        $valuesShowWhen = Arr::get($attributes, 'values_show_when', []);
        $valuesShowWhen = $this->handleByPackage($valuesShowWhen, $fieldRoles);
        $packageIds     = Arr::get($valuesShowWhen, 'includes', []);
        $whenYupType    = Arr::get($valuesShowWhen, 'when_yup_type', 'number');
        $yup            = $this->setYupValidationRules($field);
        $yupType        = CustomFieldFacade::transformYupType($field->var_type, $field->edit_type);

        if (!$field->is_required || !in_array('includes', $showWhen)) {
            return $yup;
        }

        if (empty($packageIds)) {
            /*
             * Return the Yup default when this field applies to all roles.
             */
            if ($fieldRoles->isEmpty()) {
                return $yup;
            }

            /*
             * Return Yup nullable when setting the default role doesn't apply to this field
             * and the roles don't match with any in the package,
             * even though the field is required.
             */
            if ($fieldRoles->where('id', $settingRoleId)->isEmpty()) {
                return Yup::$yupType()->nullable();
            }
        }

        if (empty($relationField)) {
            return $yup;
        }

        $whenYup = Yup::when($relationField)->is(
            Yup::$whenYupType()
                ->oneOf($packageIds)
                ->required()
                ->toArray()
        );

        $thenWhenYup = Yup::$yupType()->required(__p('validation.field_is_a_required_field', ['field' => $field->label]));
        $yup         = Yup::$yupType()->nullable();

        $this->handleYupExtra($field, $thenWhenYup);
        $this->setValidationMessage($field, $thenWhenYup);

        /*
         * If setting the default role applies to this field and the roles match with any in the package:
         * Check when `$relationField` not in the `$packageIds` then custom `Yup`.
         * Otherwise, this field follow field rule.
         */
        if ($fieldRoles->where('id', $settingRoleId)->isNotEmpty()) {
            $whenYup->is(
                Yup::$whenYupType()
                    ->notOneOf($packageIds)
                    ->required()
                    ->toArray()
            )->otherwise($thenWhenYup);

            $thenWhenYup = Yup::$yupType();
        }

        $whenYup->then($thenWhenYup);

        return $yup->when($whenYup);
    }

    public function getDriverValidationField(): Collection
    {
        return Cache::rememberForever(__METHOD__, function () {
            /** @var DriverRepositoryInterface $driverRepo */
            $driverRepo = resolve(DriverRepositoryInterface::class);

            return $driverRepo->getDrivers(Constants::DRIVER_TYPE_CUSTOM_FIELD_VALIDATOR, null, MetaFoxConstant::RESOLUTION_ADMIN);
        });
    }

    /**
     * @param Field $field
     * @param array $attributes
     *
     * @return void
     */
    public function createValidationErrorMessage(Field $field, array $attributes): void
    {
        if (empty($attributes)) {
            return;
        }

        $service = resolve(PhraseRepositoryInterface::class);

        foreach ($attributes as $key => $value) {
            $keyPhrase = $this->getKeyPhraseErrorMessage($field, $key);
            foreach ($value as $locale => $text) {
                $service->updatePhraseByKey($keyPhrase, htmlspecialchars($text), $locale);
            }
        }
    }

    /**
     * @param Field  $field
     * @param string $fieldName
     *
     * @return string
     */
    public function getValidationErrorMessage(Field $field, string $fieldName): string
    {
        $keyPhrase = $this->getKeyPhraseErrorMessage($field, $fieldName);

        return htmlspecialchars_decode(__p($keyPhrase));
    }

    /**
     * @param Field  $field
     * @param string $type
     *
     * @return string
     */
    public function getKeyPhraseErrorMessage(Field $field, string $type): string
    {
        $nameField = sprintf(self::FIELD_NAME_ERROR_MESSAGE, $type);

        return sprintf(self::KEY_PHRASE_VALIDATION_MESSAGE, $field->key, $nameField);
    }

    /**
     * @param User|null $user
     * @param array     $attributes
     *
     * @return array
     */
    public function handleFieldValidationErrorMessage(?User $user, array $attributes): array
    {
        $view   = Arr::get($attributes, 'view', self::VIEW_ALL);
        $result = [];
        $fields = match ($view) {
            self::VIEW_ALL          => $this->fieldRepository->getFieldsActiveCollectionByType($user, $attributes),
            self::VIEW_REGISTRATION => $this->fieldRepository->getFieldRegistration($attributes),
            default                 => collect([])
        };

        if ($fields->isEmpty()) {
            return $result;
        }

        /** @var $fields Field[] */
        foreach ($fields as $field) {
            $extra = $field->extra;
            if (empty($extra)) {
                continue;
            }

            $collection = $this->getDriverValidationField();
            $validation = Arr::get($extra, 'validation', []);

            foreach ($collection as $item) {
                $driver      = $item->driver;
                $driverClass = new $driver();

                if (!$driverClass instanceof ValidationFieldRule) {
                    continue;
                }

                if (!in_array($field->edit_type, $driverClass->appliesEditingComponent())) {
                    continue;
                }

                $result = array_merge($result, $driverClass->inputMessagesRule($field, $validation));
            }
        }

        return $result;
    }

    public function checkVisibleRole(User $context, User $user): bool
    {
        return $context->entityId() != $user->entityId() && !$context->hasSuperAdminRole();
    }

    public function isEnabledRelationshipStatus(): bool
    {
        return $this->fieldRepository->isActiveField(self::RELATIONSHIP_FIELD_NAME);
    }
}
