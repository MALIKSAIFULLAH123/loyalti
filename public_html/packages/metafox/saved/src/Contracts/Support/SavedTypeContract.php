<?php

namespace MetaFox\Saved\Contracts\Support;

interface SavedTypeContract
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFilterOptions(): array;

    /**
     * @return array
     */
    public function transformItemType(): array;
}
