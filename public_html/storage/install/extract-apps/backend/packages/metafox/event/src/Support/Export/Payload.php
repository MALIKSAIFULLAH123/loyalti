<?php

namespace MetaFox\Event\Support\Export;

use Closure;
use Exception;
use MetaFox\Event\Support\Export\Properties\AbstractProperty;

class Payload
{
    private string  $type;
    private array   $properties = [];
    private array   $subs       = [];
    protected array $parameters = [];

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function property(AbstractProperty $property, array $parameters = null): self
    {
        $property->addParameters($parameters ?? []);

        $this->properties[] = $property;

        return $this;
    }

    public function optional($when, Closure $closure): self
    {
        if ($when === null || $when === false) {
            return $this;
        }

        $this->properties[] = $closure();

        return $this;
    }

    public function multiple(array $items, Closure $closure): self
    {
        foreach ($items as $item) {
            $this->property($closure($item));
        }

        return $this;
    }

    public function subComponent(AbstractCalendar ...$items): self
    {
        foreach ($items as $item) {
            $this->subs[] = $item;
        }

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param string $name
     *
     * @return array|mixed
     */
    public function getProperty(string $name)
    {
        $filteredProperties = array_filter(
            $this->properties,
            function (AbstractProperty $property) use ($name) {
                return in_array($name, $property->getNameAndAliases());
            }
        );

        $properties = array_values($filteredProperties);

        if (count($properties) === 0) {
            throw new Exception("Property `{$name}` does not exist in the payload");
        }

        if (count($properties) === 1) {
            return $properties[0];
        }

        return $properties;
    }

    public function getSubComponents(): array
    {
        return $this->subs;
    }
}
