<?php

namespace MetaFox\Form;

use Illuminate\Support\Arr;
use MetaFox\Yup\Shape;

/**
 * Class AbstractFormField.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @phpstan-consistent-constructor
 */
class AbstractField implements FormField
{
    /** @var array<string,string> */
    protected array $named = [
        'valueType'         => 'valued_type',
        'subOptions'        => 'suboptions',
        'hiddenBy'          => 'hidden_by',
        'hiddenValue'       => 'hidden_value',
        'dateFrom'          => 'from',
        'dateTo'            => 'to',
        'previewUrl'        => 'preview_url',
        'fileType'          => 'file_type',
        'maxUploadFilesize' => 'max_upload_filesize',
        'uploadEndpoint'    => 'upload_url',
    ];

    /** @var array<string,mixed>|Shape|null */
    protected array|Shape|null $validation = null;

    /** @var array<string,mixed> */
    protected array $extra = [];

    /**
     * @var ?AbstractForm
     */
    protected ?AbstractForm $form = null;

    /**
     * @var Shape
     */
    protected Shape $yup;

    /** @var array<string,mixed|null> */
    protected array $attributes = [];

    /**
     * AbstractFormField constructor.
     *
     * @param array<string,mixed> $properties
     */
    public function __construct(array $properties = [])
    {
        $this->initialize();

        $this->setAttributes($properties);
    }

    /**
     * @param array<string,mixed> $options
     *
     * @return $this
     */
    public function setAttributes(array $options): static
    {
        foreach ($options as $name => $value) {
            if (method_exists($this, $method = 'set' . ucfirst($name))) {
                $this->{$method}($value);
            } elseif (method_exists($this, $name)) {
                $this->{$name}($value);
            } else {
                $this->setAttribute($name, $value);
            }
        }

        return $this;
    }

    /**
     * Get file label.
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->getAttribute('label');
    }

    /**
     * @param string     $name
     * @param mixed|null $value
     *
     * @return $this
     */
    public function setAttribute(string $name, mixed $value): static
    {
        $this->attributes[$this->named[$name] ?? $name] = $value;

        return $this;
    }

    public function name(?string $name = null): static
    {
        if ($name) {
            $this->setAttribute('name', $name);
        }

        return $this;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function component(string $type): static
    {
        return $this->setAttribute('component', $type);
    }

    public function setComponent(string $type): static
    {
        return $this->setAttribute('component', $type);
    }

    /**
     * @param  int   $minLength
     * @return $this
     */
    public function minLength(int $minLength): static
    {
        return $this->setAttribute('minLength', $minLength);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function getAttribute(string $name, $value = null): mixed
    {
        $named = $this->named[$name] ?? $name;

        if (array_key_exists($named, $this->attributes)) {
            return $this->attributes[$named];
        }

        return $value;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function removeAttribute(string $name): static
    {
        $named = $this->named[$name] ?? $name;
        unset($this->attributes[$named]);

        return $this;
    }

    /**
     * @param string           $method
     * @param array<int,mixed> $args
     *
     * @return static|mixed|string|null
     */
    public function __call($method, $args)
    {
        if (str_starts_with($method, 'get')) {
            return $this->getAttribute(lcfirst(substr($method, 3)));
        } elseif (method_exists($this, $method)) {
            return $this->{$method}($args[0]);
        } elseif (str_starts_with($method, 'remove')) {
            return $this->removeAttribute(lcfirst(substr($method, 6)));
        }

        return $this;
    }

    /**
     * @param ?string $label
     *
     * @return $this
     */
    public function label(?string $label): static
    {
        return $this->setAttribute('label', $label);
    }

    /**
     * Set layout variant.
     *
     * @param string $variant
     *
     * @return $this
     */
    public function variant(string $variant): static
    {
        return $this->setAttribute('variant', $variant);
    }

    /**
     * @param ?string $description
     *
     * @return $this
     */
    public function description(?string $description): static
    {
        return $this->setAttribute('description', $description);
    }

    public function startAdornment(mixed $startAdornment): static
    {
        return $this->setAttribute('startAdornment', $startAdornment);
    }

    public function endAdornment(mixed $endAdornment): static
    {
        return $this->setAttribute('endAdornment', $endAdornment);
    }

    public function showErrorTooltip(mixed $value = true): static
    {
        return $this->setAttribute('showErrorTooltip', $value);
    }

    public function noFeedback(mixed $value = true): static
    {
        return $this->setAttribute('noFeedback', $value);
    }

    /**
     * Add placeholder attribute.
     *
     * @param string|null $string
     *
     * @return $this
     */
    public function placeholder(?string $string): static
    {
        return $this->setAttribute('placeholder', $string);
    }

    /**
     * Add a dangerous warning message to this field.
     *
     * @param string $warning
     *
     * @return $this
     */
    public function warning(string $warning): static
    {
        return $this->setAttribute('warning', $warning);
    }

    public function warningExperience(): static
    {
        return $this->warning(__p('core::phrase.experience_setting_description'));
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if (!$this->getAttribute('name')) {
            return $this->attributes['component'] ?? '--unknown';
        }

        return $this->getAttribute('name');
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return array_key_exists('value', $this->attributes) ?
            $this->attributes['value'] : null;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): static
    {
        $this->attributes['value'] = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInline(): bool
    {
        return (bool) $this->getAttribute('inline');
    }

    /**
     * @param  bool  $value
     * @return $this
     */
    public function inline(bool $value = true): static
    {
        return $this->setAttribute('inline', $value);
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
    }

    /**
     * @return void
     */
    protected function prepare(): void
    {
    }

    /**
     * @return AbstractForm|null
     */
    public function getForm(): ?AbstractForm
    {
        return $this->form;
    }

    /**
     * @param ?AbstractForm $form
     *
     * @return void
     */
    public function setForm(?AbstractForm $form): void
    {
        $this->form = $form;
    }

    public function apiEndpoint(mixed $value): static
    {
        return $this->setAttribute('api_endpoint', $value);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $this->prepare();

        if (isset($this->validation)) {
            $this->form?->addValidation($this->getName(), $this->getLabel(), $this->validation);
        }

        $data = array_merge($this->attributes, $this->extra);

        if (!isset($data['testid'])) {
            $data['testid'] = 'field ' . $this->getName();
        }

        return $data;
    }

    /**
     * Set return key type, One of done, go, next, search, send.
     * @link https://reactnative.dev/docs/textinput#returnkeytype
     *
     * @param string $type
     *
     * @return $this
     */
    public function returnKeyType(string $type): static
    {
        return $this->setAttribute('returnKeyType', $type);
    }

    /**
     * @return array<string,mixed>|Shape|null
     */
    public function getValidation(): array|Shape|null
    {
        return $this->validation;
    }

    /**
     * @param array<string,mixed>|null $validation
     */
    public function setValidation(?array $validation): void
    {
        $this->validation = $validation;
    }

    /**
     * Set size="small".
     *
     * @return $this
     */
    public function sizeSmall(): static
    {
        return $this->setAttribute('size', 'small');
    }

    /**
     * Set sx.width=$width and fullWidth=false.
     *
     * @param int $width
     *
     * @return $this
     */
    public function width(int $width): static
    {
        Arr::set($this->attributes, 'sx.width', $width);

        return $this;
    }

    /**
     * Set size="normal".
     *
     * @return $this
     */
    public function sizeNormal(): static
    {
        return $this->setAttribute('size', 'medium');
    }

    /**
     * Set size="large".
     *
     * @return $this
     */
    public function sizeLarge(): static
    {
        return $this->setAttribute('size', 'large');
    }

    /**
     * Set margin="$margin".
     *
     * @param string $margin
     *
     * @return $this
     */
    public function margin(string $margin): static
    {
        return $this->setAttribute('margin', $margin);
    }

    public function marginNone(): static
    {
        return $this->setAttribute('margin', 'none');
    }

    /**
     * Set margin="dense".
     *
     * @return $this
     */
    public function marginDense(): static
    {
        return $this->setAttribute('margin', 'dense');
    }

    /**
     * Set className "srOnly".
     *
     * @return $this
     */
    public function srOnly(): static
    {
        return $this->setAttribute('className', 'srOnly');
    }

    /**
     * Set margin="normal".
     *
     * @return $this
     */
    public function marginNormal(): static
    {
        return $this->setAttribute('margin', 'normal');
    }

    public function autoFocus(mixed $autoFocus = true): static
    {
        return $this->setAttribute('autoFocus', $autoFocus);
    }

    /**
     * Set margin="large".
     *
     * @return $this
     */
    public function marginLarge(): static
    {
        return $this->setAttribute('margin', 'large');
    }

    /**
     * Set className property.
     *
     * @param string $className
     *
     * @return $this
     */
    public function className(string $className): static
    {
        return $this->setAttribute('className', $className);
    }

    /**
     * Set fullWidth=true.
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function fullWidth(bool $flag = true): static
    {
        return $this->setAttribute('fullWidth', $flag);
    }

    /**
     * Set size="medium".
     *
     * @return $this
     */
    public function sizeMedium(): static
    {
        return $this->setAttribute('size', 'medium');
    }

    /**
     * @param array<string,mixed> $params
     *
     * @return $this
     */
    public function sx(array $params): static
    {
        return $this->setAttribute('sx', $params);
    }

    /**
     * Set required property.
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function required(bool $flag = true): static
    {
        return $this->setAttribute('required', $flag);
    }

    public function disabled(bool $flag = true): static
    {
        return $this->setAttribute('disabled', $flag);
    }

    /**
     * Set required="false".
     *
     * @return $this
     */
    public function optional(): static
    {
        return $this->setAttribute('required', false);
    }

    /**
     * Set readOnly=true.
     * @return $this
     */
    public function readOnly(): static
    {
        return $this->setAttribute('readOnly', true);
    }

    /**
     * Set show when rules.
     *
     * @param array<string,mixed> $rules
     *
     * @return $this
     */
    public function showWhen(array $rules): static
    {
        return $this->setAttribute('showWhen', $rules);
    }

    /**
     * Set require when rules.
     *
     * @param array<string,mixed> $rules
     *
     * @return $this
     */
    public function requiredWhen(array $rules): static
    {
        return $this->setAttribute('requiredWhen', $rules);
    }

    /**
     * Set enabled when rules.
     *
     * @param array<string,mixed>|array<int,mixed> $rules
     *
     * @return $this
     */
    public function enableWhen(array $rules): static
    {
        return $this->setAttribute('enabledWhen', $rules);
    }

    /**
     * Add validation.
     *
     * @param Shape $validator
     *
     * @return $this
     */
    public function yup(Shape $validator): static
    {
        $this->validation = $validator;

        $validator->label($this->getAttribute('label'));

        return $this;
    }

    /**
     * set "autocomplete" attribute, valid options
     * - off
     * - on
     * - email
     * - password
     * = username.
     *
     * @param string $string
     *
     * @return $this
     */
    public function autoComplete(string $string): static
    {
        return $this->setAttribute('autoComplete', $string);
    }

    /**
     * Register id.
     *
     * @param string $id
     *
     * @return $this
     */
    public function id(string $id): static
    {
        return $this->setAttribute('id', $id);
    }

    public function valueType(string $type): static
    {
        return $this->setAttribute('value_type', $type);
    }

    public function minWidth(int|string $minWidth): static
    {
        Arr::set($this->attributes, 'sxFieldWrapper.minWidth', $minWidth);

        return $this;
    }

    public function maxWidth(int|string $maxWidth): static
    {
        if (is_int($maxWidth)) {
            $maxWidth = $maxWidth . 'px';
        }

        Arr::set($this->attributes, 'sxFieldWrapper.maxWidth', $maxWidth);

        return $this;
    }

    public function maxLength(mixed $maxLength): static
    {
        return $this->setAttribute('maxLength', $maxLength);
    }

    /**
     * @param  string $phrasePrefix
     * @return $this
     */
    public function withLabelDescriptionPhrase(string $phrasePrefix): static
    {
        return $this->label(__p("{$phrasePrefix}_field_label"))
            ->description(__p("{$phrasePrefix}_field_desc"));
    }

    /**
     * @param  mixed $multiple
     * @return $this
     */
    public function multiple(bool $multiple = true): static
    {
        return $this->setAttribute('multiple', $multiple);
    }

    /**
     * @param  string $labelPlacement
     * @return $this
     */
    public function labelPlacement(string $labelPlacement): static
    {
        return $this->setAttribute('labelPlacement', $labelPlacement);
    }

    /**
     * @param  mixed $value
     * @return $this
     */
    public function defaultValue(mixed $value): static
    {
        return $this->setAttribute('defaultValue', $value);
    }

    /**
     * @param  array $options
     * @return $this
     */
    public function dataSource(array $options): static
    {
        return $this->setAttribute('dataSource', $options);
    }

    /**
     * @param  string $group
     * @return $this
     */
    public function styleGroup(string $group): static
    {
        return $this->setAttribute('styleGroup', $group);
    }

    /**
     * @param  array $options
     * @return $this
     */
    public function sxFieldWrapper(array $options): static
    {
        return $this->setAttribute('sxFieldWrapper', $options);
    }

    /**
     * @param  array $options
     * @return $this
     */
    public function confirmation(array $options): static
    {
        return $this->setAttribute('confirmation', $options);
    }

    /**
     * @return array<string,mixed>
     */
    public function describe(): array
    {
        $this->prepare();

        return [
            'id'          => $this->getName(),
            'label'       => $this->getAttribute('label'),
            'description' => $this->getAttribute('description'),
        ];
    }

    public function forAdminSearchForm(): static
    {
        return $this
            ->sizeSmall()
            ->marginDense()
            ->maxWidth('220px');
    }

    public function disableClearable(): static
    {
        return $this->setAttribute('disableClearable', true);
    }

    /**
     * @param  bool  $value
     * @return $this
     */
    public function noConfirmation(bool $value = true): static
    {
        return $this->setAttribute('noConfirmation', $value);
    }

    public function forBottomSheetForm(?string $name = null): static
    {
        $component = $name ?? 'SF' . $this->getAttribute('component');

        return $this->setComponent($component);
    }

    public function useOptionContext(): static
    {
        return $this->setAttribute('useOptionContext', true);
    }

    public function findReplace(array $findReplace): static
    {
        return $this->setAttribute('findReplace', $findReplace);
    }

    public function autoSubmit(mixed $flag = true): static
    {
        return $this->setAttribute('autoSubmit', $flag);
    }

    public function shrink(): static
    {
        $default = $this->getAttribute('inputLabelProps', []);

        $shrink = ['shrink' => true];
        $value  = array_merge(is_array($default) ? $default : [], $shrink);

        return $this->setAttribute('inputLabelProps', $value);
    }
}
