<?php

namespace MetaFox\Page\Contracts;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\User;

interface PageClaimContract
{
    public function getAllowStatusOptions(): array;

    /**
     * @return array
     */
    public function getAllowStatus(): array;
    /**
     * @return array
     */
    public function getAllowStatusId(): array;

    /**
     * @param  string $key
     * @return int
     */
    public function getStatusId(string $key): int;

    /**
     * @param  string $status
     * @return ?array
     */
    public function getStatusInfo(string $status): ?array;
}
