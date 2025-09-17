<?php

namespace MetaFox\Platform\HealthCheck;

abstract class Resolver
{
    abstract public function resolve(): bool;

    public function successMessage(): string
    {
        return __p('core::phrase.successfully_resolved');
    }

    public function errorMessage(): string
    {
        return __p('core::phrase.failed');
    }
}
