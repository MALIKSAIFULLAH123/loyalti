<?php

namespace MetaFox\Yup;

use Illuminate\Support\Arr;

/**
 * @link     https://dev-docs.metafoxapp.com/frontend/validation#object
 * @category framework
 */
class ObjectShape extends MixedShape
{
    /**
     * @var array<string,Shape>
     */
    protected array $properties = [];

    public function __construct()
    {
        $this->setAttribute('type', 'object');
    }

    /**
     * @param string                     $path
     * @param array<string, mixed>|Shape $validator
     *
     * @return ObjectShape
     */
    public function addProperty(string $path, array|Shape $validator): self
    {
        Arr::set($this->properties, $path, $validator instanceof Shape ? $validator->toArray() : $validator);

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toArray(): ?array
    {
        if (!count($this->properties)) {
            return $this->attributes;
        }

        return array_merge($this->attributes, [
            'properties' => $this->properties,
        ]);
    }
}
