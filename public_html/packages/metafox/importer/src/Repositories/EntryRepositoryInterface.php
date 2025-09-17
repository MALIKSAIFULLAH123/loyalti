<?php

namespace MetaFox\Importer\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Importer\Models\Entry;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Entry.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface EntryRepositoryInterface
{
    public function getEntry(string $ref, string $source): ?Entry;

    public function getResource(string $ref, string $source): mixed;

    /**
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     */
    public function viewEntries(array $attributes): Paginator;

    public function getProcessingEntries(int $bundleId, int $limit): array;

    public function getEntriesByResource(string $resource, string $source): Collection;

    public function checkExistAnyEntry(): bool;
}
