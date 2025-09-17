<?php
namespace MetaFox\Form\Traits;

use MetaFox\Form\AbstractField;

/**
 * @mixin AbstractField
 */
trait HasRelatedFieldNameTrait
{
    /**
     * @param  string $name
     * @return AbstractField
     */
    public function relatedFieldName(string $name): static
    {
        return $this->setAttribute('relatedFieldName', $name);
    }
}
