<?php

namespace MetaFox\Importer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Importer\Database\Factories\BundleFactory;
use MetaFox\Importer\Supports\Status;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Bundle.
 *
 * @property int    $id
 * @property string $status
 * @property string $source
 * @property string $start_time
 * @property string $end_time
 * @property string $priority
 * @property string $filename
 * @property string $total_entry
 * @property int    $total_retry
 * @property string $created_at
 * @property string $updated_at
 * @property string $resource
 * @property int    $entry_index
 *
 * @method static BundleFactory factory(...$parameters)
 */
class Bundle extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'importer_bundle';

    protected $table = 'importer_bundle';

    /** @var string[] */
    protected $fillable = [
        'source',
        'resource',
        'priority',
        'filename',
        'entry_index',
        'total_entry',
        'total_retry',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * @return BundleFactory
     */
    protected static function newFactory()
    {
        return BundleFactory::new();
    }

    /**
     * @return int[]
     */
    public function getPendingEntryIds(int $limit = 10): array
    {
        return Entry::query()
            ->where('bundle_id', $this->id)
            ->where('status', Status::pending)
            ->limit($limit)
            ->pluck('id')
            ->toArray();
    }

    public function isInitial(): bool
    {
        return $this->status === Status::initial;
    }

    public function isPending(): bool
    {
        return $this->status === Status::pending;
    }

    public function isProcessing(): bool
    {
        return $this->status === Status::processing;
    }

    public function hasFailedEntries(): bool
    {
        return 0 != Entry::query()
                ->where('bundle_id', $this->id)
                ->where('status', Status::failed)
                ->count();
    }

    public function isCompleted(): bool
    {
        return 0 == Entry::query()
                ->where('bundle_id', $this->id)
                ->whereIn('status', [Status::processing, Status::pending])
                ->count();
    }

    public function getJson(): array
    {
        $data = mf_get_contents(base_path($this->filename));

        return $data ? json_decode($data, true) : [];
    }

    public function markAsDone()
    {
        $this->end_time = now();
        $this->status   = Status::done;
        $this->saveQuietly();
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
        $this->saveQuietly();
    }

    public function markAsFailed()
    {
        $this->status = Status::failed;
        $this->saveQuietly();
    }

    public function markAsPending()
    {
        $this->status = Status::pending;
        $this->saveQuietly();
    }

    public function markAsProcessing()
    {
        $this->status     = Status::processing;
        $this->start_time = now();
        $this->saveQuietly();
    }
}

// end
