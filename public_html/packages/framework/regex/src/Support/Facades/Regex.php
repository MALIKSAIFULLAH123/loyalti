<?php

namespace MetaFox\RegexRule\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\RegexRule\Contracts\Regex as SupportRegex;

/**
 * Class Regex.
 * @method static mixed getRegexSetting(string $fieldName)
 * @method static mixed getUsernameRegexSetting()
 * @method static mixed getRegexErrorMessage(string $fieldName, bool $toVarName = true)
 * @link \MetaFox\RegexRule\Support\Regex;
 */
class Regex extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SupportRegex::class;
    }
}
