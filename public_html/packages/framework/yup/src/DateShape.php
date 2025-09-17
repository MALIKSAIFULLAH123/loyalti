<?php

namespace MetaFox\Yup;

/**
 * @link     https://dev-docs.metafoxapp.com/frontend/validation#date
 * @category framework
 */
class DateShape extends MixedShape
{
    public function __construct()
    {
        $this->setAttribute('type', 'date');
    }

    /**
     * @param int|string|array{ref:string} $min
     * @param string|null                  $error
     *
     * @return $this
     */
    public function min($minLength, ?string $error = null): self
    {
        return $this->setAttribute('min', $minLength, $error);
    }

    /**
     * @param int|string|array{ref:string} $min
     * @param string|null                  $error
     *
     * @return $this
     */
    public function max($maxLength, ?string $error = null): self
    {
        return $this->setAttribute('max', $maxLength, $error);
    }

    /**
     * @param string      $minYear
     * @param string|null $error
     *
     * @return $this
     */
    public function minYear(string $minYear, ?string $error = null): self
    {
        return $this->setAttribute('minYear', $minYear, $error);
    }

    /**
     * @param string      $maxYear
     * @param string|null $error
     *
     * @return $this
     */
    public function maxYear(string $maxYear, ?string $error = null): self
    {
        return $this->setAttribute('maxYear', $maxYear, $error);
    }

    /**
     * @param string $format
     * @return self
     */
    public function inputFormat(string $format): self
    {
        return $this->setAttribute('inputFormat', $format);
    }
}
