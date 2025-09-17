<?php

namespace MetaFox\EMoney\Contracts;

interface CurrencyConverterInterface
{
    /**
     * @param  string     $src
     * @param  string     $dest
     * @return float|null
     */
    public function getExchangeRate(string $src, string $dest): ?float;

    /**
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * @return string
     */
    public function getServiceName(): string;

    /**
     * @return array|null
     */
    public function getPayload(): ?array;

    /**
     * @return array|null
     */
    public function getResponse(): ?array;

    /**
     * @return string|null
     */
    public function getInformationLink(): ?string;

    /**
     * @return string
     */
    public function getTitle(): string;
}
