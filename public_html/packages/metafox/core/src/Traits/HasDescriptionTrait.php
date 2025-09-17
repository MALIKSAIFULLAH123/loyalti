<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Traits;

use MetaFox\Hashtag\Traits\HasHashtagTextTrait;

/**
 * Trait HasEditorDescriptionTrait.
 */
trait HasDescriptionTrait
{
    use HasHashtagTextTrait;

    public function getShortDescription(string|array $field = 'description'): string
    {
        return parse_output()->getDescription($this->getDescriptionValue($field));
    }

    public function getDescription(string|array $field = 'description'): string
    {
        return parse_output()
            ->parse($this->getTransformContent($this->getDescriptionValue($field)));
    }

    private function getDescriptionValue(string|array $field = 'description'): string
    {
        if (is_string($field)) {
            return $this->resource->{$field} ?? '';
        }

        $key   = key($field);
        $value = $field[$key];

        return $this->resource->{$key}?->{$value} ?? '';
    }
}
