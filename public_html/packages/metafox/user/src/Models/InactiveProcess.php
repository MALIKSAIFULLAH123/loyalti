<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class InactiveProcess
 *
 * @property int        $id
 * @property int        $status
 * @property int        $total_sent
 * @property int        $total_users
 * @property int        $round
 * @property string     $created_at
 * @property string     $updated_at
 * @property Collection $pendingProcess
 * @property Collection $stoppedProcess
 */
class InactiveProcess extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'user_inactive_process';

    public const NOT_STARTED_STATUS = 0;
    public const PENDING_STATUS     = 1;
    public const SENDING_STATUS     = 2;
    public const COMPLETED_STATUS   = 3;
    public const STOPPED_STATUS     = 9;

    protected $table = 'user_inactive_process';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'status',
        'total_sent',
        'total_users',
        'round',
        'created_at',
        'updated_at',
    ];

    public function process(): HasMany
    {
        return $this->hasMany(InactiveProcessData::class, 'process_id', 'id');
    }

    public function pendingProcess(): HasMany
    {
        return $this->hasMany(InactiveProcessData::class, 'process_id', 'id')
            ->where('status', self::PENDING_STATUS);
    }

    public function stoppedProcess(): HasMany
    {
        return $this->hasMany(InactiveProcessData::class, 'process_id', 'id')
            ->where('status', self::STOPPED_STATUS);
    }

    public function statusText(): string
    {
        return match ($this->status) {
            self::NOT_STARTED_STATUS => __p('user::phrase.not_started'),
            self::PENDING_STATUS     => __p('core::phrase.pending'),
            self::SENDING_STATUS     => __p('user::phrase.sending'),
            self::COMPLETED_STATUS   => __p('user::phrase.completed'),
            self::STOPPED_STATUS     => __p('user::phrase.status_stopped'),
        };
    }

    public function processText(): string
    {
        if ($this->total_sent == 0 && in_array($this->status, [self::NOT_STARTED_STATUS, self::PENDING_STATUS])) {
            return $this->statusText();
        }

        return __p(
            'user::phrase.process_sent_emails',
            ['current' => $this->total_sent, 'total' => $this->total_users]
        );
    }

    public function isStopped(): bool
    {
        return $this->status === self::STOPPED_STATUS;
    }
}

// end
