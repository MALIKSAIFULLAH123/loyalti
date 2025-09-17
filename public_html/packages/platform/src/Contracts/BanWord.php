<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface BanWord.
 */
interface BanWord
{
    /**
     * @param  string|null $findValue
     * @return string|null
     */
    public function transformRegexPattern(?string $findValue): ?array;

    /**
     * @param  string|null $string
     * @return string|null
     */
    public function clean(?string $string = null): ?string;

    /**
     * @param  string|null $string
     * @return string
     */
    public function parse(?string $string = null): string;

    /**
     * @param  string|null $string
     * @return bool
     */
    public function hasBanWord(?string $string): bool;
}
