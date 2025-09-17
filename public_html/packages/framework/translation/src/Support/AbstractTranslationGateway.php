<?php

namespace MetaFox\Translation\Support;

use MetaFox\Translation\Contracts\TranslationGatewayInterface;
use MetaFox\Translation\Models\TranslationGateway;

class AbstractTranslationGateway implements TranslationGatewayInterface
{
    protected TranslationGateway $gateway;

    public function __construct(TranslationGateway $gateway)
    {
        $this->setTranslationGateway($gateway);
    }

    public function setTranslationGateway(TranslationGateway $gateway): TranslationGatewayInterface
    {
        $this->gateway = $gateway;

        return $this;
    }

    public function translate(string $text, array $attributes): array
    {
        return [];
    }

    public function isAvailable(): bool
    {
        return false;
    }

    protected function getTranslationGateway(): TranslationGateway
    {
        return $this->gateway;
    }
}
