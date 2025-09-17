<?php

namespace MetaFox\Platform\Contracts;

interface InputCleaner
{
    public function cleanTitle(?string $string): ?string;

    public function cleanContent(?string $string): ?string;
}
