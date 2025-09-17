<?php

namespace MetaFox\ActivityPoint\Contracts\Support;

interface PointSetting
{
    /**
     * @return array<int, mixed>
     */
    public function getAllowedRoleOptions(): array;

    /**
     * @return array<int, mixed>
     */
    public function getAllowedRole(): array;
}
