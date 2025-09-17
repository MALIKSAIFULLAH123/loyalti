<?php

namespace MetaFox\Platform\Support\Browse\Contracts;

/**
 * Interface BrowseInterface
 * @package MetaFox\Platform\Support\Browse\Contracts
 */
interface BrowseInterface
{
    /**
     * @return array<string, string>
     */
    public function getSortFilters(): array;

    /**
     * @return array<string, string>
     */
    public function getViewFilters(): array;

    /**
     * @return array<string, string>
     */
    public function getWhenFilters(): array;

    /**
     * @return array<string, array<string, string>>
     */
    public function getListFilter(): array;
}
