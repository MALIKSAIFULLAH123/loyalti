<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Traits\HasRelatedFieldNameTrait;

/**
 * Class Choice.
 * @method self   setMultiple(bool $multiple)
 * @method self   setSubOptions(array $subOptions)
 * @method bool   getMultiple()
 * @method array  getSubOptions()
 * @method array  getOptions()
 * @method self   setValueType(string $valueType)
 * @method string getValueType()
 * @method static setOptions(array $options)
 */
class Choice extends Radio
{
    use HasRelatedFieldNameTrait;

    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_SELECT)
            ->fullWidth();
    }

    /**
     * @param int $with
     *
     * @return $this
     */
    public function withSmallSize(int $with = 200): self
    {
        return $this->sizeSmall()
            ->marginDense()
            ->fullWidth(false)
            ->sx(['width' => $with]);
    }

    /**
     * Allow free edit.
     *
     * @param  mixed|bool $value
     * @return $this
     */
    public function freeSolo(mixed $value = true): self
    {
        return $this->setAttribute('freeSolo', $value);
    }

    /**
     * Setup for horizontal form.
     * @return $this
     */
    public function forAdminSearchForm(): static
    {
        return $this->sizeSmall()
            ->marginDense()
            ->maxWidth('220px');
    }

    /**
     * @param array<array<string,mixed> $options
     *
     * @return $this
     */
    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }

    /**
     * @param  array $options
     * @return $this
     */
    public function optionRelatedMapping(array $options): self
    {
        return $this->setAttribute('optionRelatedMapping', $options);
    }

    /**
     * @param  bool  $value
     * @return $this
     */
    public function alwaysShow(bool $value = true): self
    {
        return $this->setAttribute('alwaysShow', $value);
    }
}
