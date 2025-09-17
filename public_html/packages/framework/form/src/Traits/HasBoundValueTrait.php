<?php
namespace MetaFox\Form\Traits;

use MetaFox\Form\AbstractField;

/**
 * @mixin AbstractField
 */
trait HasBoundValueTrait
{
    public function min(float $min, bool $asInteger = false, string $fieldName = 'min'): static
    {
        if ($asInteger) {
            $min = intval($min);
        }

        return $this->setAttribute($fieldName, $min);
    }

    public function max(float $max, bool $asInteger = false, string $fieldName = 'max'): static
    {
        if ($asInteger) {
            $max = intval($max);
        }

        return $this->setAttribute($fieldName, $max);
    }
}
