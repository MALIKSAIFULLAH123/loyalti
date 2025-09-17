<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Yup;

use Illuminate\Support\Arr;

/**
 * Class MixedShape.
 * @ignore
 * @codeCoverageIgnore
 * @category framework
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class MixedShape implements Shape
{
    /**
     * @var string
     */
    protected string $type;

    /**
     * @var array <string,mixed>
     */
    protected array $attributes = [];

    /**
     * @param string|null $label
     *
     * @return $this
     */
    public function label(?string $label): self
    {
        if (!isset($this->attributes['label'])) {
            $this->attributes['label'] = $label;
        }

        return $this;
    }

    /**
     * @param string      $name
     * @param mixed       $value
     * @param string|null $error
     *
     * @return $this
     */
    protected function setAttribute(string $name, $value, $error = null): static
    {
        if ($error) {
            Arr::set($this->attributes, 'errors.' . $name, $error);
        }
        $this->attributes[$name] = $value;

        return $this;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @param string      $name
     * @param string|null $error
     *
     * @return $this
     */
    public function setError(string $name, ?string $error = null): self
    {
        if ($error) {
            Arr::set($this->attributes, 'errors.' . $name, $error);
        }

        return $this;
    }

    /**
     * Set required=false.
     * @return $this
     */
    public function optional(): self
    {
        return $this->setAttribute('required', false);
    }

    /**
     * Set required.
     *
     * @param string|null $error
     *
     * @return $this
     */
    public function required(?string $error = null): self
    {
        if (!$error) {
            $error = __p('validation.this_field_is_a_required_field');
        }

        return $this->setAttribute('required', true, $error);
    }

    /**
     * @param bool|null   $nullable
     * @param string|null $error
     *
     * @return $this
     */
    public function nullable(?bool $nullable = true, ?string $error = null): self
    {
        return $this->setAttribute('nullable', $nullable, $error);
    }

    /**
     * @param string|null $error
     *
     * @return $this
     */
    public function strict(?string $error = null): self
    {
        return $this->setAttribute('strict', true, $error);
    }

    /**
     * @param array<mixed> $values
     * @param string|null  $error
     *
     * @return $this
     */
    public function oneOf(array $values, ?string $error = null): self
    {
        return $this->setAttribute('oneOf', $values, $error);
    }

    /**
     * @param array<mixed> $values
     * @param string|null  $error
     *
     * @return $this
     */
    public function notOneOf(array $values, ?string $error = null): self
    {
        return $this->setAttribute('notOneOf', $values, $error);
    }

    /**
     * @param WhenShape $when
     *
     * @return self
     */
    public function when(WhenShape $when): self
    {
        if (!isset($this->attributes['when'])) {
            $this->attributes['when'] = [];
        }
        $this->attributes['when'][] = $when->toArray();

        return $this;
    }

    /**
     * One of url, email.
     *
     * @param  string      $format
     * @param  string|null $error
     * @return $this
     */
    public function format(string $format, ?string $error = null): self
    {
        return $this->setAttribute('format', $format, $error);
    }

    /**
     * @return array<mixed>|null
     */
    public function toArray(): ?array
    {
        return $this->attributes;
    }
}
