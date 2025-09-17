<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface BBCode
 * @package MetaFox\Platform\Contracts
 */
interface BBCode
{
    /**
     * @param string $string
     * @param string $code
     *
     * @return array<mixed>
     */
    public function getAllBBCodeContent(string $string, string $code): array;
}
