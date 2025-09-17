<?php

namespace MetaFox\Form;

use ArrayAccess;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Section.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class Section extends AbstractField implements ArrayAccess
{
    /**
     * @var array<string, FormField>
     */
    protected array $elements = [];

    public function initialize(): void
    {
        $this->setAttributes([
            'component' => MetaFoxForm::CONTAINER,
        ]);
    }

    /**
     * Add a field to this section.
     *
     * @param ?FormField $field
     *
     * @return $this
     */
    public function addField(?FormField $field): static
    {
        if (null === $field) {
            return $this;
        }

        $name = $field->getName();

        if (!$name) {
            throw new InvalidArgumentException('Missing required attribute "name" of %s', $field::class);
        } elseif (array_key_exists($name, $this->elements)) {
            throw new InvalidArgumentException(sprintf(
                'Duplicated field [name="%s"] of %s',
                $name,
                $field::class
            ));
        }

        $this->elements[$name] = $field;

        return $this;
    }

    /**
     * Add more form fields.
     *
     * @param FormField ...$fields
     *
     * @return Section
     */
    public function addFields(?FormField ...$fields): static
    {
        foreach ($fields as $field) {
            if (!$field) {
                continue;
            }
            $this->addField($field);
        }

        return $this;
    }

    public function justifyContent(string $flex): static
    {
        $value = match ($flex) {
            'end'    => 'flex-end',
            'center' => 'flex-center',
            default  => $flex
        };

        Arr::set($this->attributes, 'sx.justifyContent', $value);

        return $this;
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        $elements = [];

        foreach ($this->elements as $name => $field) {
            $field->setForm($this->form);
            $elements[$name] = $field->toArray();
        }

        $data['elements'] = $elements;

        return $data;
    }

    /**
     * Set attribute variant.
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
     * Set variant="horizontal" to render fields inline.
     *
     * @return $this
     */
    public function asHorizontal(): static
    {
        return $this->setAttribute('variant', 'horizontal');
    }

    /**
     * Remove all elements.
     *
     * @return $this
     */
    public function reset(): static
    {
        $this->elements[] = [];

        return $this;
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function hasElement(string $name): bool
    {
        return isset($this->elements[$name]);
    }

    public function getElement(string $name): ?FormField
    {
        return $this->elements[$name] ?? null;
    }

    public function offsetExists(mixed $offset): bool
    {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet(mixed $offset): mixed
    {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset(mixed $offset): void
    {
        // TODO: Implement offsetUnset() method.
    }

    public function describe(): array
    {
        $result = [];
        foreach ($this->elements as $name => $element) {
            $result[$name] = $element->describe();
        }

        $result[$this->getName()] = parent::describe();

        return $result;
    }

    public function separateBetweenFields(): static
    {
        return $this->setAttribute('separator', 'separateHr');
    }

    public function setMultiStepDescription(array $stepInfo): static
    {
        $this->description(__p('core::phrase.step_of_steps', [
            'current_step' => Arr::get($stepInfo, 'current_step'),
            'total_step'   => Arr::get($stepInfo, 'total_steps'),
        ]));

        $this->setAttribute('sx', [
            '& .description' => [
                'order'       => 1,
                'marginRight' => 'auto',
            ],
        ]);

        /*
         * It is flag for client to know this description is for multi-step form
         */
        $this->setAttribute('isMultiStep', true);

        return $this;
    }

    public function autoSubmit(mixed $flag = true): static
    {
        return $this->setAttribute('autoSubmit', $flag);
    }

    /**
     * @param array<string> $options
     */
    public function sxContainer(array $options): self
    {
        return $this->setAttribute('sxContainer', $options);
    }

    /**
     * Mark the section as collapsible.
     * @param bool $flag
     *
     * @return self
     */
    public function collapsible(bool $flag = true): self
    {
        return $this->setAttribute('collapsible', $flag);
    }

    /**
     * Mark the section as collapsed by default.
     * Otherwise it will be set as expanded.
     * @param bool $flag
     *
     * @return self
     */
    public function collapsed(bool $flag = true): self
    {
        return $this->setAttribute('collapsed', $flag);
    }
}
