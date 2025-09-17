<?php

namespace MetaFox\RegexRule\Contracts;

interface Regex
{
    /**
     * Get regex rule by field name.
     * @param  string $fieldName
     * @return mixed
     */
    public function getRegexSetting(string $fieldName): mixed;

    /**
     * @return mixed
     */
    public function getUsernameRegexSetting(): mixed;

    /**
     * @param  string $fieldName
     * @return mixed
     */
    public function getRegexErrorMessage(string $fieldName, bool $toVarName = true): mixed;
}
