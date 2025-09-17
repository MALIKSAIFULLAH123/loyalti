<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform;

/**
 * Trait PropertyBagTrait.
 * @protected <string,string> $named
 * @protected <string,mixed> $attributes
 * @protected <string,mixed> $defaultProperties
 */
trait TraitAttributeBag
{
    /** @var array<string,mixed|null> */
    protected array $attributes = [];

    /**
     * This method is deprecated. Prefer `setAttributes` to be safe.
     *
     * @param array<string,mixed> $options
     *
     * @return $this
     * @deprecated
     */
    public function config(array $options): self
    {
        foreach ($options as $name => $value) {
            $methodExists = method_exists($this, $method = 'set' . ucfirst($name));

            if ($methodExists) {
                $this->{$method}($value);
            }
            if (!$methodExists) {
                $this->setAttribute($name, $value);
            }
        }

        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        foreach ($attributes as $name => $value) {
            if ($name === 'attributes') {
                continue;
            } //prevent recursive call.

            $methodExists = method_exists($this, $method = 'set' . ucfirst($name));

            if ($methodExists) {
                $this->{$method}($value);
            }
            if (!$methodExists) {
                $this->setAttribute($name, $value);
            }
        }

        return $this;
    }

    /**
     * Set attribute name/value pair.
     *
     * @param string     $name
     * @param mixed|null $value
     *
     * @return $this
     */
    public function setAttribute(string $name, $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed|null
     */
    public function getAttribute(string $name, $value = null)
    {
        return $this->attributes[$name] ?? $value;
    }

    /**
     * Remove an attribute by specific name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function removeAttribute(string $name): self
    {
        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * @param string           $method
     * @param array<int,mixed> $args
     *
     * @return self|mixed|string|null
     */
    public function __call($method, $args)
    {
        if (str_starts_with($method, 'get')) {
            return $this->getAttribute(lcfirst(substr($method, 3)));
        } elseif (str_starts_with($method, 'set')) {
            return $this->setAttribute(lcfirst(substr($method, 3)), $args[0]);
        } elseif (str_starts_with($method, 'remove')) {
            return $this->removeAttribute(lcfirst(substr($method, 6)));
        }

        return $this;
    }
}
