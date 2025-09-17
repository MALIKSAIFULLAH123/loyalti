<?php

namespace MetaFox\Announcement\Contracts\Support;

interface Announcement
{
    /**
     * @return array<int, mixed>
     */
    public function getStyleOptions(): array;

    /**
     * @return array<int, mixed>
     */
    public function getAllowedRoleOptions(): array;

    /**
     * @return array<int, mixed>
     */
    public function getAllowedRole(): array;
}
