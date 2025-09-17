<?php

namespace MetaFox\Event\Support\Export;

use MetaFox\Event\Support\Export\Builders\Builder;
use MetaFox\Event\Support\Export\Properties\AbstractProperty;

abstract class AbstractCalendar
{
    private array $appendedProperties = [];

    private array $appendedSubComponents = [];

    abstract public function getComponentType(): string;

    abstract public function getRequiredProperties(): array;

    abstract protected function payload(): Payload;

    public function resolvePayload(): Payload
    {
        $payload = $this->payload();

        foreach ($this->appendedProperties as $appendedProperty) {
            $payload->property($appendedProperty);
        }

        $payload->subComponent(...$this->appendedSubComponents);

        return $payload;
    }

    public function toString(): string
    {
        $payload = $this->resolvePayload();

        $this->ensureRequiredPropertiesAreSet($payload);

        $builder = new Builder($payload);

        return $builder->build();
    }

    public function appendProperty(AbstractProperty $property): AbstractCalendar
    {
        $this->appendedProperties[] = $property;

        return $this;
    }

    public function appendSubComponent(AbstractCalendar $component): AbstractCalendar
    {
        $this->appendedSubComponents[] = $component;

        return $this;
    }

    protected function ensureRequiredPropertiesAreSet(Payload $componentPayload)
    {
        $providedProperties = [];

        foreach ($componentPayload->getProperties() as $property) {
            $providedProperties = array_merge(
                $providedProperties,
                $property->getNameAndAliases()
            );
        }

        $requiredProperties = $this->getRequiredProperties();

        $intersection = array_intersect($requiredProperties, $providedProperties);

        if (count($intersection) !== count($requiredProperties)) {
            $missingProperties = array_diff($requiredProperties, $intersection);
            $properties        = implode(', ', $missingProperties);
            $type              = ucfirst(strtolower($this->getComponentType()));

            throw new \Exception("Properties `{$properties}` are required when creating an `{$type}`.");
        }
    }
}
