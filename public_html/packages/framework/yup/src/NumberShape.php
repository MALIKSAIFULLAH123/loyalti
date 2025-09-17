<?php

namespace MetaFox\Yup;

/**
 * @link     https://dev-docs.metafoxapp.com/frontend/validation#number
 * @category framework
 */
class NumberShape extends MixedShape
{
    public function __construct()
    {
        $this->setAttribute('type', 'number');
    }

    /**
     * @param int|array{ref:string} $value
     * @param string|null           $error
     *
     * @return $this
     */
    public function min($value, ?string $error = null): self
    {
        if (!$error && !is_array($value)) {
            $error = __p('validation.minimum_length_description_required', ['min' => $value]);
        }

        return $this->setAttribute('min', $value, $error);
    }

    public function max($value, ?string $error = null): self
    {
        if (!$error && !is_array($value)) {
            $error = __p('validation.maximum_length_description_required', ['max' => $value]);
        }

        return $this->setAttribute('max', $value, $error);
    }

    public function lessThan($value, ?string $error = null): self
    {
        return $this->setAttribute('lessThan', $value, $error);
    }

    public function moreThan($value, ?string $error = null): self
    {
        return $this->setAttribute('moreThan', $value, $error);
    }

    public function int(?string $error = null): self
    {
        if (null === $error) {
            $error = __p('core::validation.integer', ['attribute' => '${path}']);
        }

        return $this->setAttribute('integer', true, $error);
    }

    public function positive(?string $error = null): self
    {
        return $this->setAttribute('sign', 'positive', $error);
    }

    public function negative(?string $error = null): self
    {
        return $this->setAttribute('sign', 'negative', $error);
    }

    public function unint(?string $error = null): self
    {
        return $this->positive($error)
            ->int($error);
    }

    /**
     * @param string $fn One of 'floor' | 'ceil' | 'trunc' | 'round'
     *
     * @return $this
     */
    public function round(string $fn): self
    {
        return $this->setAttribute('round', $fn);
    }
}
