<?php

namespace MetaFox\Event\Support\Export\Builders;

use MetaFox\Event\Support\Export\Properties\AbstractProperty;

class PropertyBuilder
{
    public function __construct(protected AbstractProperty $property) { }

    public function build(): array
    {
        $parameters = $this->resolveParameters();

        $value = $this->property->getValue();

        return array_map(
            fn(string $name) => $value !== null
                ? "{$name}{$parameters}:{$value}"
                : "{$name}{$parameters}",
            $this->property->getNameAndAliases()
        );
    }

    private function resolveParameters(): string
    {
        $parameters = '';

        foreach ($this->property->getParameters() as $parameter) {
            $name  = $parameter->getName();
            $value = $parameter->getValue();

            $parameters .= ";{$name}={$value}";
        }

        return $parameters;
    }
}
