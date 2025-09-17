<?php

namespace MetaFox\Translation\Contracts;

use MetaFox\Translation\Models\TranslationGateway;

interface TranslationGatewayInterface
{
    /**
     * Set gateway config.
     * @param TranslationGateway $gateway
     * @return self
     */
    public function setTranslationGateway(TranslationGateway $gateway): self;

    public function translate(string $text, array $attributes): array;

    public function isAvailable(): bool;
}
