<?php

namespace MetaFox\Video\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Video\Support\Facade\Video as VideoSupportFacade;
use MetaFox\Video\Support\VideoSupport;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class VerifyProcess
 *
 * @property int    $id
 * @property int    $round
 * @property string $status
 * @property int    $total_verified
 * @property int    $last_id
 * @property int    $total_videos
 * @property array  $extra
 */
class VerifyProcess extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'video_verify_process';

    protected $table = 'video_verify_processes';

    /** @var string[] */
    protected $fillable = [
        'round',
        'user_id',
        'user_type',
        'status',
        'total_verified',
        'last_id',
        'total_videos',
        'extra',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public function getProcessTextAttribute(): string
    {
        if ($this->total_verified == 0 && $this->status == VideoSupport::PENDING_VERIFY_STATUS) {
            return VideoSupportFacade::getStatusVerifyProcessTexts($this)['label'];
        }

        return __p(
            'video::phrase.process_verify_videos',
            ['current' => $this->total_verified, 'total' => $this->total_videos]
        );
    }
}

// end
