<?php

namespace MetaFox\Yup;

/**
 * Class ArrayShape.
 * @link     https://dev-docs.metafoxapp.com/frontend/validation#array
 * @category framework
 */
class ArrayShape extends MixedShape
{
    public function __construct()
    {
        $this->setAttribute('type', 'array');
    }

    public function of(Shape $yup): self
    {
        $this->setAttribute('of', $yup->toArray());

        return $this;
    }

    /**
     * @param int|array   $minLength
     * @param string|null $error
     *
     * @return $this
     */
    public function min(int|array $minLength, ?string $error = null): self
    {
        return $this->setAttribute('min', $minLength, $error);
    }

    /**
     * @param int|array   $minLength
     * @param string|null $error
     *
     * @return $this
     */
    public function max(int|array $minLength, ?string $error = null): self
    {
        return $this->setAttribute('max', $minLength, $error);
    }

    /**
     * @param string|null $error
     *
     * @return $this
     */
    public function unique(?string $error = null): self
    {
        return $this->setAttribute('unique', true, $error);
    }

    /**
     * @param string      $name
     * @param string|null $error
     *
     * @return $this
     */
    public function uniqueBy(string $name, ?string $error = null): self
    {
        return $this->setAttribute('uniqueBy', $name, $error);
    }

    /**
     * @param  array<int, mixed> $when
     * @param  string|null       $error
     * @return $this
     */
    public function minWhen(array $when, ?string $error = null): self
    {
        return $this->setAttribute('minWhen', $when, $error);
    }

    /**
     * @param  array<int, mixed> $when
     * @param  string|null       $error
     * @return $this
     */
    public function maxWhen(array $when, ?string $error = null): self
    {
        return $this->setAttribute('maxWhen', $when, $error);
    }
}
