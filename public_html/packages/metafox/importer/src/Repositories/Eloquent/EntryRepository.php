<?php

namespace MetaFox\Importer\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Importer\Models\Entry;
use MetaFox\Importer\Repositories\EntryRepositoryInterface;
use MetaFox\Importer\Supports\Status;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class EntryRepository.
 */
class EntryRepository extends AbstractRepository implements EntryRepositoryInterface
{
    public function model()
    {
        return Entry::class;
    }

    public function getEntry(string $ref, string $source): ?Entry
    {
        /** @var ?Entry $entry */
        $entry = $this->getModel()->newQuery()->where([
            'source' => $source,
            'ref_id' => $ref,
        ])->first();

        return $entry;
    }

    public function getResource(string $ref, string $source): mixed
    {
        $entry = $this->getEntry($ref, $source);

        return $entry?->getResource();
    }

    public function viewEntries(array $attributes): Paginator
    {
        $query = $this->buildQueryViewEntry($attributes);

        return $query
            ->paginate($attributes['limit'] ?? 100);
    }

    private function buildQueryViewEntry(array $attributes)
    {
        $bundleId = Arr::get($attributes, 'bundle_id');

        $query = $this->getModel()->newModelQuery();

        if ($bundleId) {
            $query = $query->where('bundle_id', $bundleId);
        }

        return $query;
    }

    public function getProcessingEntries(int $bundleId, int $limit = 500): array
    {
        return $this->getModel()->newQuery()
            ->where('status', Status::processing)
            ->where('bundle_id', $bundleId)
            ->where(
                'updated_at',
                '<',
                Carbon::now()->subMinute()
            ) //get entries were set as processing from 1 minute before
            ->limit($limit)
            ->orderBy('priority')
            ->pluck('id')
            ->toArray();
    }

    public function getEntriesByResource(string $resource, string $source): Collection
    {
        return $this->getModel()->newQuery()
            ->where('resource_type', $resource)
            ->where('source', $source)
            ->whereNotNull('resource_id')
            ->pluck('resource_id', 'ref_id')
            ->chunk(100);
    }

    public function checkExistAnyEntry(): bool
    {
        return $this->getModel()->newQuery()->exists();
    }
}
