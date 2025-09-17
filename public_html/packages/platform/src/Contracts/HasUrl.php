<?php

namespace MetaFox\Platform\Contracts;

interface HasUrl
{
    public function toLink(): ?string;

    public function toUrl(): ?string;

    public function toRouter(): ?string;
}
