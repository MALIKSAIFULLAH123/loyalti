<?php

namespace MetaFox\Core\Contracts;

interface LinkSupportContract
{
    /**
     * @param array<string> $options
     */
    public function setOptions(array $options): void;

    /**
     * @param string       $url
     * @param array<mixed> $matches
     *
     * @return bool
     */
    public function verifyUrl(string $url, &$matches = []): bool;

    /**
     * @param string $url
     *
     * @return ?array<mixed>
     */
    public function parseUrl(string $url): ?array;
}
