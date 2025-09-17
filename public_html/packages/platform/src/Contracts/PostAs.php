<?php

namespace MetaFox\Platform\Contracts;

interface PostAs
{
    public function getPostAsDefault(): int;

    public function checkPostAs(User $user): bool;
}
