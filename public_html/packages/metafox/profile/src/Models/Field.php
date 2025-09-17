<?php

namespace MetaFox\Profile\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use MetaFox\Authorization\Models\Role;
use MetaFox\Form\Builder;
use MetaFox\Form\FormField;
use MetaFox\Form\Mobile\Builder as MobileBuilder;
use MetaFox\Form\Mobile\DateField;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Profile\Database\Factories\FieldFactory;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Yup\Yup;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * class Field.
 *
 * @mixin EloquentBuilder
 * @property        int          $id
 * @property        string       $section_id
 * @property        string       $field_name
 * @property        string       $module_id
 * @property        string       $product_id
 * @property        ?int         $role_id
 * @property        int          $privacy
 * @property        string       $type_id
 * @property        string       $edit_type
 * @property        string       $view_type
 * @property        string       $var_type
 * @property        bool         $is_active
 * @property        bool         $is_required
 * @property        bool         $is_feed
 * @property        int          $ordering
 * @property        bool         $is_register
 * @property        bool         $is_search
 * @property        bool         $has_description
 * @property        bool         $has_label
 * @property        string       $label
 * @property        string       $editingLabel
 * @property        string       $editingDescription
 * @property        string       $descriptionForSearch
 * @property        ?Section     $section
 * @property        Collection   $options
 * @property        string       $description
 * @property        string       $name
 * @property        string       $key
 * @property        ?array       $extra
 * @property        Collection   $roles
 * @property        Collection   $visibleRoles
 * @method   static FieldFactory factory(...$parameters)
 */
class Field extends Model implements Entity
{
    use HasEntity;
    use HasNestedAttributes;
    use HasFactory;

    public const ENTITY_TYPE = 'user_custom_field';

    protected $table = 'user_custom_fields';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'section_id',
        'field_name',
        'key',
        'is_section',
        'type_id',
        'edit_type',
        'view_type',
        'var_type',
        'privacy',
        'ordering',
        'is_active',
        'is_required',
        'is_feed',
        'is_register',
        'is_search',
        'has_label',
        'label',
        'description',
        'has_description',
        'extra',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_register' => 'boolean',
        'is_required' => 'boolean',
        'extra'       => 'array',
    ];
    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'roles',
        'visibleRoles',
    ];

    /**
     * @return FieldFactory
     */
    protected static function newFactory()
    {
        return FieldFactory::new();
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_custom_field_role_data',
            'field_id',
            'role_id'
        )->using(FieldRoleData::class);
    }

    /**
     * @return BelongsToMany
     */
    public function visibleRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_custom_field_visible_role_data',
            'field_id',
            'role_id'
        )->using(FieldVisibleRoleData::class);
    }

    public function getLabelAttribute(): ?string
    {
        if (!$this->has_label) {
            return null;
        }

        return htmlspecialchars_decode(__p('profile::phrase.' . $this->field_name . '_label'));
    }

    public function getEditingLabelAttribute()
    {
        return htmlspecialchars_decode(__p('profile::phrase.' . $this->field_name . '_label'));
    }

    public function getNameAttribute()
    {
        return $this->key;
    }

    public function setLabelAttribute($value)
    {
        $key = 'profile::phrase.' . $this->field_name . '_label';

        if (!is_array($value)) {
            return;
        }

        $service = resolve(PhraseRepositoryInterface::class);

        foreach ($value as $locale => $text) {
            $service->updatePhraseByKey($key, htmlspecialchars($text), $locale);
        }
    }

    public function setDescriptionAttribute($value)
    {
        $key = 'profile::phrase.' . $this->field_name . '_description';

        if (!is_array($value)) {
            return;
        }

        $service = resolve(PhraseRepositoryInterface::class);

        foreach ($value as $locale => $text) {
            $service->updatePhraseByKey($key, htmlspecialchars($text), $locale);
        }
    }

    public function getDescriptionAttribute(): ?string
    {
        return htmlspecialchars_decode(__p('profile::phrase.' . $this->field_name . '_description'));
    }

    public function getEditingDescriptionAttribute(): ?string
    {
        return htmlspecialchars_decode(__p('profile::phrase.' . $this->field_name . '_description'));
    }

    private function getCreator(?string $resolution = null, bool $allowSearch = false): ?string
    {
        $editType = $this->edit_type;

        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if (!$settingAllowHtml && $editType === CustomField::RICH_TEXT_EDITOR) {
            $editType = CustomField::TEXT_AREA;
        }

        if ($allowSearch) {
            $editType = CustomFieldFacade::transformForSearch($this->edit_type, $resolution);
        }

        /*
         * Compatible with old mobile version that does not have Basic Date field.
         */
        if (
            MetaFoxConstant::RESOLUTION_MOBILE === $resolution
            && version_compare(MetaFox::getApiVersion(), 'v1.9', '<')
            && CustomField::BASIC_DATE === $editType
        ) {
            return MobileBuilder::getCreator(CustomField::DATE);
        }

        return match ($resolution) {
            MetaFoxConstant::RESOLUTION_MOBILE => MobileBuilder::getCreator($this->edit_type),
            default                            => Builder::getCreator($editType),
        };
    }

    private function getOptions(mixed &$field): void
    {
        if (method_exists($field, 'options')) {
            // put option to fields.
            $options = $this->options->map(function (Option $item) {
                return ['label' => $item->label, 'value' => $item->entityId()];
            })->toArray();

            $field->options($options);
        }
    }

    public function toEditField(?string $resolution = null): ?FormField
    {
        $creator = $this->getCreator($resolution);

        if (!$creator) {
            return null;
        }

        $data = [
            'name'        => $this->name,
            'label'       => $this->editingLabel,
            'description' => strip_tags($this->editingDescription),
            'required'    => $this->is_required,
        ];

        $field = new $creator($data);

        switch ($this->edit_type) {
            case CustomField::RADIO_GROUP:
                if (!MetaFox::isMobile()) {
                    $field->setAttribute('descriptionPlacement', 'bottom');
                }
                break;
            case CustomField::CHECK_BOX:
                $field->setAttribute('uncheckedValue', 0)
                    ->setAttribute('checkedValue', 1);

                if ($this->is_required) {
                    $field->setAttribute('uncheckedValue', false);
                }
                break;
            case CustomField::BASIC_DATE:
                /*
                 * Compatible with old mobile version that does not have Basic Date field.
                 */
                if (
                    MetaFoxConstant::RESOLUTION_MOBILE === $resolution
                    && version_compare(MetaFox::getApiVersion(), 'v1.9', '<')
                    && $field instanceof DateField
                ) {
                    $field->setAttribute('displayFormat', 'DD/MM/YYYY');
                }
                break;
        }

        $this->getOptions($field);

        $this->setYupValidation($field);

        return $field;
    }

    /**
     * @param mixed $field
     *
     * @return void
     * @deprecated v5.2 remove this
     */
    protected function setYupValidation(mixed &$field): void
    {
        $yupType = CustomFieldFacade::transformYupType($this->var_type, $this->edit_type);

        $yup = match ($this->is_required) {
            true  => Yup::$yupType()->required(),
            false => Yup::$yupType()->nullable()
        };

        $this->setValidationRules($yup);

        $field->yup($yup);
    }

    /**
     * @param mixed $yup
     *
     * @return void
     * @deprecated v5.2 remove this
     */
    protected function setValidationRules(mixed $yup): void
    {
        switch ($this->edit_type) {
            case CustomField::DATE:
                $yup->setError('typeError', __p('core::phrase.invalid_date'));
                break;
            case CustomField::BASIC_DATE:
                $yup->inputFormat('DD/MM/YYYY')
                    ->setError('typeError', __p('core::phrase.invalid_date'));

                break;
            case CustomField::CHECK_BOX:
            case CustomField::MULTI_CHOICE:
                if ($this->is_required) {
                    $yup->setError('typeError', __p('validation.this_field_is_a_required_field'));
                }

                break;
        }
    }

    public function toSearchField(?string $resolution = null): ?FormField
    {
        $creator = $this->getCreator($resolution, true);

        if (!$creator) {
            return null;
        }

        $description = $this->has_description ? $this->description : null;
        $field       = new $creator([
            'name'        => $this->name,
            'label'       => $this->editingLabel,
            'description' => $description,
        ]);

        $this->getOptions($field);

        if (MetaFox::isMobile()) {
            return $field->marginNone();
        }

        return $field->marginDense();
    }

    public function section(): ?HasOne
    {
        return $this->hasOne(Section::class, 'id', 'section_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class, 'field_id', 'id')
            ->orderBy('ordering')
            ->orderBy('id');
    }

    /**
     * @return array
     * @deprecated v5.2 remove this
     */
    public function toRule(): array
    {
        $this->var_type = CustomFieldFacade::transformVarType($this->edit_type);

        if ($this->is_required) {
            return ['required', $this->var_type];
        }

        return [$this->var_type, 'sometimes', 'nullable'];
    }

    public function toSearchRule(): array
    {
        $this->var_type = CustomFieldFacade::transformVarTypeForSearch($this->edit_type);

        return [$this->var_type, 'sometimes', 'nullable'];
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        $profileType = $this->section?->getUserType();

        return match ($profileType) {
            CustomField::SECTION_TYPE_USER => url_utility()->makeApiUrl('profile/field/browse'),
            default                        => url_utility()->makeApiUrl("{$profileType}/field/browse")
        };
    }

    public function getAdminEditUrlAttribute(): string
    {
        $profileType = $this->section?->getUserType();

        return match ($profileType) {
            CustomField::SECTION_TYPE_USER => url_utility()->makeApiUrl("profile/field/edit/{$this->entityId()}"),
            default                        => url_utility()->makeApiUrl("{$profileType}/field/edit/{$this->entityId()}")
        };
    }
}

// end
