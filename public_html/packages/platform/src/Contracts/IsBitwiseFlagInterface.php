<?php

namespace MetaFox\Platform\Contracts;

interface IsBitwiseFlagInterface
{
    /**
     * Define database column to storage bitwise value.
     *
     * @return string
     */
    public function getFlagName(): string;

    /**
     * Define bitwise abilities. Example: [ 'can_comment' => 1, 'can_like' => 2].
     *
     * @return array
     */
    public function getAbilities(): array;
}
