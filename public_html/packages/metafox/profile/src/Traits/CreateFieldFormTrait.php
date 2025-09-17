<?php

namespace MetaFox\Profile\Traits;

use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Models\Field as Model;
use MetaFox\Profile\Models\Option;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Profile\Support\Validation\ValidationFieldRule;
use MetaFox\Yup\Yup;

/**
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
trait CreateFieldFormTrait
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.profile.field.store'))
            ->asPost()
            ->setValue($this->getValues());
    }

    /**
     * @return void
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        if (empty($this->getSectionOptions()) && !$this->isEdit()) {
            $basic->addField(
                Builder::typography('no_custom_sections')
                    ->plainText(__p('profile::phrase.no_custom_sections_available'))
            );
            return;
        }

        $basic->addFields(
            Builder::text('field_name')
                ->maxLength(CustomFieldSupport::MAX_NAME_LENGTH)
                ->label(__p('core::phrase.name'))
                ->description(__p('profile::phrase.name_desc'))
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->matches(MetaFoxConstant::RESOURCE_IDENTIFIER_REGEX, __p('validation.alpha_underscore_lower_only', [
                            'attribute' => '${path}',
                        ]))
                ),
            Builder::translatableText('label')
                ->label(__p('core::phrase.label'))
                ->required()
                ->buildFields(),
            Builder::translatableText('description')
                ->asTextArea()
                ->label(__p('core::phrase.description'))
                ->buildFields(),
            Builder::dropdown('section_id')
                ->required()
                ->label(__p('profile::phrase.section'))
                ->options($this->getSectionOptions())
                ->yup(Yup::number()->required()),
            $this->getEditTypeField(),
            $this->getOptionsField(),
            $this->getRoleField(),
            $this->getVisibleRoleField(),
            Builder::checkbox('has_label')
                ->label(__p('profile::phrase.has_label')),
            $this->getActiveField(),
            $this->getRegisterField(),
            $this->getSearchField(),
            $this->getHasDescriptionField(),
            Builder::checkbox('is_required')
                ->label(__p('profile::phrase.is_required')),
        );

        $this->getValidationFields();
        $this->addDefaultFooter();
    }

    /**
     * getSectionOptions.
     *
     * @return array
     */
    protected function getSectionOptions(): array
    {
        return $this->sectionRepository()->getSectionByTypeForForm($this->getUserType());
    }

    /**
     * getEditTypeOptions.
     *
     * @return array<mixed>
     */
    protected function getEditTypeOptions(): array
    {
        return [
            [
                'value' => CustomFieldSupport::TEXT,
                'label' => __p('profile::phrase.edit_type.text'),
            ],
            [
                'value' => CustomFieldSupport::TEXT_AREA,
                'label' => __p('profile::phrase.edit_type.textarea'),
            ],
            [
                'value' => CustomFieldSupport::RICH_TEXT_EDITOR,
                'label' => __p('profile::phrase.edit_type.rich_text_editor'),
            ],
            [
                'value' => CustomFieldSupport::CHOICE,
                'label' => __p('profile::phrase.edit_type.selection'),
            ],
            [
                'value' => CustomFieldSupport::CHECK_BOX,
                'label' => __p('profile::phrase.edit_type.checkbox'),
            ],
            [
                'value' => CustomFieldSupport::MULTI_CHOICE,
                'label' => __p('profile::phrase.edit_type.multiple_selection'),
            ],
            [
                'value' => CustomFieldSupport::RADIO_GROUP,
                'label' => __p('profile::phrase.edit_type.radio'),
            ],
            [
                'value' => CustomFieldSupport::BASIC_DATE,
                'label' => __p('profile::phrase.edit_type.basic_date'),
            ],
            [
                'value' => CustomFieldSupport::URL,
                'label' => __p('profile::phrase.edit_type.link'),
            ],
        ];
    }

    public function getActiveField(): ?AbstractField
    {
        return null;
    }

    public function getSearchField(): ?AbstractField
    {
        return Builder::checkbox('is_search')
            ->label(__p('profile::phrase.include_on_search_user'));
    }

    public function getHasDescriptionField(): ?AbstractField
    {
        if ($this->getSearchField() == null) {
            return null;
        }

        return Builder::checkbox('has_description')
            ->showWhen(['truthy', 'is_search'])
            ->label(__p('profile::phrase.include_the_field_description_in_the_search_form'));
    }

    public function getRegisterField(): ?AbstractField
    {
        return null;
    }

    public function getRoleField(): ?AbstractField
    {
        return null;
    }

    public function getVisibleRoleField(): ?AbstractField
    {
        return null;
    }

    public function isEdit(): bool
    {
        return false;
    }

    protected function getValues(): array
    {
        $values = [
            'type_id'         => 'main',
            'edit_type'       => CustomFieldSupport::TEXT,
            'view_type'       => 'text',
            'var_type'        => CustomFieldSupport::TYPE_STRING,
            'has_label'       => 1,
            'has_description' => 1,
            'is_active'       => 1,
        ];
        $values = array_merge($values, $this->getValueDefaultValidationFields());

        if ($this->isEdit()) {
            $extra            = $this->resource->extra;
            $validationFields = Arr::get($extra, 'validation', []);
            $values           = [
                'type_id'         => $this->resource->type_id,
                'is_active'       => $this->resource->is_active,
                'field_name'      => $this->resource->field_name,
                'section_id'      => $this->resource->section_id,
                'var_type'        => $this->resource->var_type,
                'view_type'       => $this->resource->view_type,
                'edit_type'       => $this->resource->edit_type,
                'is_required'     => $this->resource->is_required,
                'label'           => $this->getPhraseValues(sprintf('profile::phrase.%s_label', $this->resource->field_name)),
                'description'     => $this->getPhraseValues(sprintf('profile::phrase.%s_description', $this->resource->field_name)),
                'has_label'       => $this->resource->has_label,
                'has_description' => $this->resource->has_description,
            ];

            $values = array_merge($values, $validationFields, $this->getValidationFieldsValues($validationFields, $this->resource->edit_type));

            if (count($this->resource->options)) {
                Arr::set($values, 'options', $this->getOptionsFieldForEdit());
            }
        }

        return $values;
    }

    public function getPhraseValues(string $keyPhrase): array
    {
        $values = Language::getPhraseValues($keyPhrase);

        return array_map(function ($value) {
            return htmlspecialchars_decode($value);
        }, $values);
    }

    public function getUserType(): string
    {
        return CustomFieldSupport::SECTION_TYPE_USER;
    }

    protected function sectionRepository(): SectionRepositoryInterface
    {
        return resolve(SectionRepositoryInterface::class);
    }

    public function getEditTypeField(): ?AbstractField
    {
        return Builder::dropdown('edit_type')
            ->label(__p('profile::phrase.edit_type_label'))
            ->disabled($this->isEdit())
            ->options($this->getEditTypeOptions());
    }

    protected function getValidationFields(): void
    {
        $collection    = CustomFieldFacade::getDriverValidationField();
        $valueShowWhen = [];
        if ($collection->isEmpty()) {
            return;
        }

        $section = $this->addSection('validation')->label(__p('profile::phrase.validation_rule'));
        foreach ($collection as $driver) {
            $class = $driver->driver;
            $class = new $class();

            if (!$class instanceof ValidationFieldRule) {
                continue;
            }

            $class->setSection($section);
            $class->setRelationField('edit_type');
            $class->getFields();
            $this->translateMessageFields($class, $section);

            $valueShowWhen = array_merge($valueShowWhen, $class->appliesEditingComponent());
        }

        if (!empty($valueShowWhen)) {
            $section->showWhen(['includes', 'edit_type', $valueShowWhen]);
        }
    }

    protected function getValidationFieldsValues(array $validationFields, string $editType = CustomFieldSupport::TEXT): array
    {
        $results = [];

        if (empty($validationFields)) {
            $collection = CustomFieldFacade::getDriverValidationField();
            if ($collection->isEmpty()) {
                return $results;
            }

            foreach ($collection as $driver) {
                $class = $driver->driver;
                $class = new $class();

                if (!$class instanceof ValidationFieldRule) {
                    continue;
                }

                if (!in_array($editType, $class->appliesEditingComponent())) {
                    continue;
                }

                $validationFields = array_merge($validationFields, $class->fieldsErrorMessageLabel());
            }
        }

        foreach ($validationFields as $key => $item) {
            $nameField  = sprintf(CustomField::FIELD_NAME_ERROR_MESSAGE, $key);
            $valueField = Language::getPhraseValues(CustomFieldFacade::getKeyPhraseErrorMessage($this->resource, $key));
            Arr::set($results, $nameField, $valueField);
        }

        return $results;
    }

    private function translateMessageFields(ValidationFieldRule $class, Section $section): void
    {
        foreach ($class->fieldsName() as $item) {
            $message = Arr::get($class->fieldsErrorMessageLabel(), $item);
            if (empty($message)) {
                continue;
            }

            $section->addFields(
                Builder::translatableText(sprintf(CustomFieldSupport::FIELD_NAME_ERROR_MESSAGE, $item))
                    ->label($message)
                    ->showWhen($class->getShowWhenByParent())
                    ->buildFields()
            );
        }
    }

    private function getValueDefaultValidationFields(): array
    {
        $results    = [];
        $collection = CustomFieldFacade::getDriverValidationField();
        if ($collection->isEmpty()) {
            return $results;
        }

        foreach ($collection as $driver) {
            $class = $driver->driver;
            $class = new $class();

            if (!$class instanceof ValidationFieldRule) {
                continue;
            }

            $results = array_merge($results, $class->getValuesDefault());
        }

        return $results;
    }

    /**
     * @return AbstractField
     */
    protected function getOptionsField(): AbstractField
    {
        $field = Builder::freeOptions('options')
            ->showWhen(['includes', 'edit_type', CustomFieldFacade::getEditTypeAllowOptions()])
            ->translatable()
            ->sortable()
            ->minLength(1);

        $this->handleYupOptionsField($field);

        return $field;
    }

    /**
     * @param AbstractField $field
     *
     * @return void
     */
    protected function handleYupOptionsField(AbstractField $field): void
    {
        $valueOptionThenYup      = Yup::object();
        $valueOptionOtherwiseYup = Yup::object();
        $languages               = Language::getAllActiveLanguages();
        $fieldLabel              = $field->getAttribute('label');

        /**
         * @var \MetaFox\Localize\Models\Language[] $languages
         */
        foreach ($languages as $language) {
            $errorRequired = __p('validation.required', [
                'attribute' => __p('localize::phrase.name_in_language_name', ['name' => $fieldLabel, 'language' => $language->name]),
            ]);

            $yupLanguageRequired = match ($language->is_default) {
                true    => Yup::string()->required($errorRequired),
                default => Yup::string()->nullable(),
            };

            $yupLanguage = Yup::string()->nullable();

            $valueOptionThenYup->addProperty($language->language_code, $yupLanguageRequired);
            $valueOptionOtherwiseYup->addProperty($language->language_code, $yupLanguage);
        }

        $field->yup(Yup::array()->nullable()
            ->when(
                Yup::when('edit_type')
                    ->is(
                        Yup::string()
                            ->oneOf(CustomFieldFacade::getEditTypeAllowOptions())
                            ->toArray()
                    )
                    ->then(Yup::array()
                        ->required(__p('validation.min.array', [
                            'attribute' => $fieldLabel,
                            'min'       => 1,
                        ]))
                        ->of(Yup::object()
                            ->addProperty('value', $valueOptionThenYup)
                        ))
            )
            ->of(Yup::object()
                ->addProperty('value', $valueOptionOtherwiseYup)
            )
        );
    }

    protected function getOptionsFieldForEdit(): array
    {
        return $this->resource->options->map(function (Option $option) {
            return [
                'id'       => $option->id,
                'value'    => Language::getPhraseValues($option->label_var),
                'ordering' => $option->ordering,
            ];
        })->toArray();
    }

    protected function getOptionsFieldForDuplicate(): array
    {
        return $this->resource->options->map(function (Option $option) {
            return [
                'value'    => Language::getPhraseValues($option->label_var),
                'ordering' => $option->ordering,
                'status'   => MetaFoxConstant::FILE_NEW_STATUS,
                'uid'      => 'freeOption' . $option->id,
            ];
        })->toArray();
    }
}
