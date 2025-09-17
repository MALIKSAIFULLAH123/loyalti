<?php

namespace MetaFox\Platform\Traits\Helpers;

/**
 * Trait InputCleanerTrait.
 */
trait InputCleanerTrait
{
    public function cleanTitle(?string $string): ?string
    {
        return parse_input()->clean($string, false, false);
    }

    public function cleanContent(?string $string): ?string
    {
        return parse_input()->clean($string);
    }
}
