<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Yup;

/**
 * Class WhenShape.
 * @link     https://dev-docs.metafoxapp.com/frontend/validation#when
 * @category framework
 */
class WhenShape implements Shape
{
    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    public function label(?string $label): Shape
    {
        $this->attributes['label'] = $label;

        return $this;
    }

    /**
     * WhenShape constructor.
     *
     * @param string|string[] $fields
     */
    public function __construct(string|array $fields)
    {
        $this->attributes['fields'] = $fields;
    }

    /**
     * @param mixed $is
     *
     * @return WhenShape
     */
    public function is(mixed $is): self
    {
        $this->attributes['is'] = $is;

        return $this;
    }

    /**
     * @param Shape $shape
     *
     * @return $this
     */
    public function then(Shape $shape): self
    {
        $this->attributes['then'] = $shape->toArray();

        return $this;
    }

    /**
     * @param array $configs
     * @return $this
     */
    public function thenArray(array $configs): self
    {
        $this->attributes['then'] = $configs;

        return $this;
    }

    /**
     * @param Shape $shape
     *
     * @return $this
     */
    public function otherwise(Shape $shape): self
    {
        $this->attributes['otherwise'] = $shape->toArray();

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
