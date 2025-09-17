<?php

namespace MetaFox\Group\Contracts;

interface RuleSupportContract
{
    /**
     * @return int
     */
    public function getDescriptionMaxLength(): int;

    /**
     * @return int
     */
    public function getDescriptionMinLength(): int;
}
