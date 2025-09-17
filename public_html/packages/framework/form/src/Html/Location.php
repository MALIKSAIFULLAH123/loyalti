<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Location.
 */
class Location extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::LOCATION)
            ->fullWidth(true)
            ->variant('outlined')
            ->label(__p('core::phrase.location'));
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        $value = $this->getAttribute('value', []);

        return $value['address'] ?? '';
    }

    /**
     * @param string|null $address
     *
     * @return Location
     */
    public function setAddress(?string $address): self
    {
        $value            = $this->getAttribute('value', []);
        $value['address'] = $address;

        return $this->setAttribute('value', $value);
    }

    /**
     * @return int
     */
    public function getLat(): int
    {
        $value = $this->getAttribute('value', []);

        return $value['lat'] ?? '';
    }

    /**
     * @param int|null $lat
     *
     * @return Location
     */
    public function setLat(?int $lat): self
    {
        $value        = $this->getAttribute('value', []);
        $value['lat'] = $lat;

        return $this->setAttribute('value', $value);
    }

    /**
     * @return int
     */
    public function getLng(): int
    {
        $value = $this->getAttribute('value', []);

        return $value['lng'] ?? '';
    }

    /**
     * @param int|null $lng
     *
     * @return Location
     */
    public function setLng(?int $lng): self
    {
        $value        = $this->getAttribute('value', []);
        $value['lng'] = $lng;

        return $this->setAttribute('value', $value);
    }

    public function hideMap(bool $flag = false): static
    {
        return $this->setAttribute('hideMap', $flag);
    }

    /**
     * @return mixed
     */
    public function getValue(): array
    {
        return [
            'address' => $this->getAddress(),
            'lat'     => $this->getLat(),
            'lng'     => $this->getLng(),
        ];
    }
}
