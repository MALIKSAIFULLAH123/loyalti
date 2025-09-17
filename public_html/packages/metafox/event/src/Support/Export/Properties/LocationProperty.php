<?php

namespace MetaFox\Event\Support\Export\Properties;

class LocationProperty extends AbstractProperty
{
    public function __construct(protected string $name, protected float $lat, protected float $lng)
    {
    }

    public function getValue(): string
    {
        return json_encode($this->lat) . ';' . json_encode($this->lng);
    }

    public function getOriginalValue(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }
}
