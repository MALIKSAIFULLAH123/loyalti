<?php

namespace MetaFox\Chat\Contracts;

interface ChatContract
{
    public function disableChat(string $package, bool $optimizeClear = true): void;
}
