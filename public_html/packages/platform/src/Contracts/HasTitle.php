<?php

namespace MetaFox\Platform\Contracts;

interface HasTitle
{
    /**
     * @return string
     */
    public function toTitle(): string;
}
