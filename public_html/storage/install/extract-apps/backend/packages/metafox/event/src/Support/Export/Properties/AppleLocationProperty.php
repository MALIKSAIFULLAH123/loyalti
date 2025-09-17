<?php

namespace MetaFox\Event\Support\Export\Properties;


class AppleLocationProperty extends AbstractProperty
{

    public function __construct(protected float $lat, protected float $lng, protected string $address, protected string $addressName, protected int $radius = 72)
    {
        $this->name = 'X-APPLE-STRUCTURED-LOCATION';

        $this->addParameter(new Parameter('VALUE', 'URI'));
        $this->addParameter(new Parameter('X-ADDRESS', $address));
        $this->addParameter(new Parameter('X-APPLE-RADIUS', $radius));
        $this->addParameter(new Parameter('X-TITLE', $addressName));
    }

    public function getValue(): string
    {
        return "geo:{$this->lat},{$this->lng}";
    }

    public function getOriginalValue(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }
}
