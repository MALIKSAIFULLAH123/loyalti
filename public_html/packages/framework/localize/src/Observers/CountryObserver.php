<?php

namespace MetaFox\Localize\Observers;

use Exception;
use MetaFox\Localize\Models\Country;

/**
 * Class CountryObserver.
 */
class CountryObserver
{
    /**
     * @throws Exception
     */
    public function deleted(Country $country): void
    {
        $country->children()->delete();
    }
}
