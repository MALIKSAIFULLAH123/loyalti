<?php

namespace MetaFox\Core\Contracts;

/**
 * Interface FetchLinkInterface.
 */
interface FetchLinkInterface
{
    /**
     * @param string $url
     *
     * @return array<string, mixed>
     */
    public function parse(string $url): ?array;
}
