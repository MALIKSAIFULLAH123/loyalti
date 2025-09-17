<?php

namespace MetaFox\Form;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\TraitAttributeBag;
use MetaFox\Yup\Shape;

/**
 * Class AbstractForm.
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
abstract class AbstractForm extends JsonResource
{
    use TraitAttributeBag;

    /**
     * @var ?FormValidationSchema
     */
    protected ?FormValidationSchema $formValidationSchema = null;

    /** @var bool */
    private bool $initialized = false;

    /** @var array<string,FormField> */
    protected array $named = [];

    /** @var array<string,FormField> */
    protected array $elements = [];

    /** @var array<string,mixed> */
    protected array $defaultProperties = [
        'component' => 'Form',
        'method'    => 'GET',
    ];

    /**
     * @var array<string,mixed>|JsonResource
     */
    protected array $value = [];

    protected array $multiStepFormMeta = [];
    protected array $formMeta          = [];

    /**
     * @param mixed $resource
     */
    public function __construct($resource = null)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string,mixed>|JsonResource
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array<string,mixed>|JsonResource $value
     */
    public function setValue(mixed $value): static
    {
        $this->value = is_array($value) ? $value : $value->toArray(\request());

        return $this;
    }

    /**
     * Value assign if not exists.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return static
     */
    public function assignValue(string $name, mixed $value): static
    {
        Arr::set($this->value, $name, $value);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasValue(string $name): bool
    {
        return Arr::has($this->value, $name);
    }

    /**
     * @return mixed|null
     */
    public function getResource()
    {
        return $this->resource;
    }

    public function setCaptcha(mixed $array): static
    {
        return $this->setAttribute('captcha', $array);
    }

    /**
     * @param array<string,mixed>|Section $section
     *
     * @return Section
     */
    public function addSection(string|array|Section $section): Section
    {
        if (is_string($section)) {
            $section = ['name' => $section];
        }

        if (!$section instanceof Section) {
            $section = new Section($section);
        }

        $name = $section->getName();

        if (array_key_exists($name, $this->elements)) {
            return $this->elements[$name];
        }

        $this->elements[$name] = $section;

        $section->setForm($this);

        return $section;
    }

    /**
     * @param FormField $field
     *
     * @deprecated
     */
    public function addField(FormField $field): void
    {
        $name = $field->getName();

        if (array_key_exists($name, $this->elements)) {
            abort(500, "Section '$name' is already existed");
        }
        $this->elements[$name] = $field;

        $field->setForm($this);
    }

    protected function prepare(): void {}

    protected function initialize(): void {}

    /**
     * @param Request $request
     *
     * @return array<string,mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        $this->prepare();

        if (!$this->initialized) {
            $method = $this->getMethodInitialize();

            $this->$method();
            $this->initialized = true;
        }

        $elements = [];

        foreach ($this->elements as $name => $element) {
            $element->setForm($this);
            $elements[$name] = $element->toArray();
        }

        return array_merge($this->defaultProperties, $this->attributes, [
            'value'      => $this->value,
            'validation' => $this->formValidationSchema?->toArray($request),
            'elements'   => $elements,
        ]);
    }

    protected function getMethodInitialize(): string
    {
        $methodDefault = 'initialize';
        $theme         = MetaFox::clientTheme();

        if (!$theme) {
            return $methodDefault;
        }

        $method = $methodDefault . Str::studly($theme);

        if (method_exists($this, $method)) {
            return $method;
        }

        return $methodDefault;
    }

    /**
     * @param array<string,mixed> $config
     *
     * @return Section
     */
    protected function addBasic(array $config = []): Section
    {
        return $this->addSection(array_merge(['name' => 'basic'], $config));
    }

    /**
     * @param array<string,mixed> $config
     *
     * @return Section
     */
    protected function addFooter(array $config = []): Section
    {
        return $this->addSection(array_merge([
            'name'      => 'footer',
            'component' => 'FormFooter',
        ], $config));
    }

    protected function addDefaultFooter(bool $edit = null): void
    {
        if (null === $edit) {
            $edit = is_array($this->resource) ? !empty($this->resource) : $this->resource?->id > 0;
        }

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label($edit
                        ?
                        __p('core::phrase.save_changes')
                        :
                        __p('core::phrase.create')),
                Builder::cancelButton(),
            );
    }

    /**
     * @param string                                  $name
     * @param string|null                             $label
     * @param array<string,array<string,mixed>>|Shape $yup
     */
    public function addValidation(string $name, ?string $label, $yup): void
    {
        $validation = $yup instanceof Shape ? $yup->toArray() : $yup;

        if (!$this->formValidationSchema) {
            $this->formValidationSchema = new FormValidationSchema(null);
        }

        $this->formValidationSchema->add($name, $label, $validation);
    }

    /**
     * Set form title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function title(string $title): self
    {
        return $this->setAttribute('title', $title);
    }

    /**
     * Set form description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function description(string $description): self
    {
        return $this->setAttribute('description', $description);
    }

    /**
     * Set form title.
     *
     * @param string $apiUrl
     *
     * @return $this
     */
    public function action(string $apiUrl): self
    {
        return $this->setAttribute('action', $apiUrl);
    }

    /**
     * Set next action name.
     *
     * @param mixed $action
     *
     * @return static
     */
    public function secondAction(mixed $action): static
    {
        return $this->setAttribute('secondAction', $action);
    }

    /**
     * Set the submitting dispatch action type, by default "@form/submit".
     *
     * @param mixed $action
     *
     * @return static
     */
    public function submitAction(mixed $action): static
    {
        return $this->setAttribute('submitAction', $action);
    }

    /**
     * Set the dispatch action when form success saved, by default "@form/submit".
     *
     * @param mixed $action
     *
     * @return static
     */
    public function successAction(mixed $action): static
    {
        return $this->setAttribute('successAction', $action);
    }

    /**
     * Set the dispatch action when form submit failure, by default "@form/submit".
     *
     * @param string $action
     *
     * @return static
     */
    public function failureAction(string $action): static
    {
        return $this->setAttribute('failureAction', $action);
    }

    /**
     * Set acceptable page params name.
     *
     * @param string[] $names
     *
     * @return static
     */
    public function acceptPageParams(array $names): static
    {
        return $this->setAttribute('acceptPageParams', $names);
    }

    /**
     * Set attribute noHeader.
     *
     * @param bool $flag
     *
     * @return static
     */
    public function noHeader(bool $flag = true): static
    {
        return $this->setAttribute('noHeader', $flag);
    }

    /**
     * Set attribute noBreadcrumb.
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function noBreadcrumb(bool $flag = true): static
    {
        return $this->setAttribute('noBreadcrumb', $flag);
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function method(string $method): static
    {
        return $this->setAttribute('method', $method);
    }

    /**
     * Set form method="GET".
     *
     * @return $this
     */
    public function asGet(): static
    {
        return $this->setAttribute('method', 'GET');
    }

    /**
     * Set form method="DElETE".
     *
     * @return $this
     */
    public function asDelete(): static
    {
        return $this->setAttribute('method', 'DELETE');
    }

    /**
     * Set form method="PUT".
     *
     * @return $this
     */
    public function asPut(): static
    {
        return $this->setAttribute('method', 'PUT');
    }

    public function preventReset(mixed $value = true): static
    {
        return $this->setAttribute('preventReset', $value);
    }

    /**
     * Set form method="PUT".
     *
     * @return $this
     */
    public function asPatch(): self
    {
        return $this->setAttribute('method', 'PATCH');
    }

    /**
     * Set form method="POST".
     *
     * @return $this
     */
    public function asPost(): self
    {
        return $this->setAttribute('method', 'POST');
    }

    /**
     * Set method="POST" and "enctype"="multipart/form-data".
     * @return $this
     */
    public function asMultipart(): self
    {
        return $this->setAttribute('enctype', 'multipart/form-data')
            ->asPost();
    }

    /**
     * <code>
     *  $this->getField("basic/mail.queue")
     * </code>.
     *
     * @param string $path
     *
     * @return mixed
     */
    public function getElementByPath(string $path): mixed
    {
        return null;
    }

    /**
     * @param string $name
     *
     * @return ?FormField
     */
    public function getElementByName(string $name): ?FormField
    {
        return Arr::get($this->elements, $name, null);
    }

    /**
     * @param string $name
     *
     * @return ?Section
     */
    public function getSectionByName(string $name): ?Section
    {
        $section = $this->getElementByName($name);

        return $section instanceof Section ? $section : null;
    }

    /**
     * <code>
     *  $this->withElement("basic/mail.queue", function(Text $field){
     *      $text->disabled()
     *  })
     * </code>.
     *
     * @param string  $path
     * @param Closure $closure
     *
     * @return void
     */
    public function withElement(string $path, Closure $closure)
    {
        // 1. find element by xpath.
    }

    public function asHorizontal(): static
    {
        return $this->setAttribute('variant', 'horizontal');
    }

    /**
     * Set test id.
     *
     * @param mixed $testId
     *
     * @return static
     */
    public function testId(mixed $testId): static
    {
        return $this->setAttribute('testid', $testId);
    }

    public function alertPreSubmitErrors(mixed $value): static
    {
        return $this->setAttribute('alertPreSubmitErrors', $value);
    }

    public function describe(): array
    {
        $this->prepare();

        $this->initialize();

        $elements = [];

        foreach ($this->elements as $name => $element) {
            $elements[$name] = $element->describe();
        }

        return $elements;
    }

    public function setBackProps(mixed $backProps): static
    {
        if (is_string($backProps)) {
            $backProps = ['label' => $backProps];
        }

        return $this->setAttribute('backProps', $backProps);
    }

    /**
     * @param mixed $flag
     *
     * @return $this
     *               mark frontend to auto submit form values.
     */
    public function submitOnValueChanged(mixed $flag = true): static
    {
        return $this->setAttribute('submitOnValueChanged', $flag);
    }

    /**
     * @param bool|array<string, mixed> $flag
     *
     * @return $this
     */
    public function resetFormOnSuccess(mixed $flag = true): static
    {
        return $this->setAttribute('resetFormWhenSuccess', $flag);
    }

    /**
     * @param bool|array<string, mixed> $flag
     *
     * @return $this
     */
    public function reloadFormWhenSuccess(mixed $flag = true): static
    {
        return $this->setAttribute('reloadFormWhenSuccess', $flag);
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function navigationConfirmation(mixed $value = false): static
    {
        return $this->setAttribute('navigationConfirmWhenDirty', $value);
    }

    /**
     * @param array<string,mixed> $config
     *
     * @return Section
     */
    protected function addHeader(array $config = []): Section
    {
        return $this->addSection(array_merge(['name' => 'header'], $config));
    }

    public function confirm(array $config): static
    {
        return $this->setAttribute('confirm', $config);
    }

    public function updateResponseValue(mixed $flag = true): static
    {
        return $this->setAttribute('updateResponseValue', $flag);
    }

    public function scrollable(bool $scrollable = true): static
    {
        return $this->setAttribute('scrollable', $scrollable);
    }

    public function setMultiStepFormMeta(array $config = []): static
    {
        $this->multiStepFormMeta = $config;

        return $this;
    }

    public function getMultiStepFormMeta(): array
    {
        return $this->multiStepFormMeta;
    }

    public function setFormMeta(array $config = []): static
    {
        $this->formMeta = $config;

        return $this;
    }

    public function getFormMeta(): array
    {
        if (empty($this->getMultiStepFormMeta())) {
            return $this->formMeta;
        }

        return array_merge($this->formMeta, $this->getMultiStepFormMeta());
    }

    public function resetDirtyWhenSuccess(bool $value = true): static
    {
        return $this->setAttribute('resetDirtyWhenSuccess', $value);
    }
}
