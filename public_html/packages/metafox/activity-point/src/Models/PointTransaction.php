<?php

namespace MetaFox\ActivityPoint\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Database\Factories\PointTransactionFactory;
use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class PointTransaction.
 *
 * @mixin Builder
 *
 * @property int           $id
 * @property int           $module_id
 * @property string        $package_id
 * @property int           $type
 * @property string        $action
 * @property int           $points
 * @property int           $action_id
 * @property bool          $is_hidden
 * @property array         $action_params
 * @property ?PointSetting $pointSetting
 * @property string        $created_at
 * @property string        $updated_at
 *
 * @method static PointTransactionFactory factory(...$parameters)
 */
class PointTransaction extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;
    use HasItemMorph;

    public const ENTITY_TYPE = 'activitypoint_transaction';

    protected $table = 'apt_transactions';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'module_id',
        'package_id',
        'type',
        'action',
        'points',
        'is_hidden',
        'is_admincp',
        'action_params',
        'point_setting_id',
        'created_at',
        'updated_at',
        'item_id',
        'item_type',
        'action_id',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_hidden'     => 'boolean',
        'action_params' => 'array',
    ];

    protected static function newFactory(): PointTransactionFactory
    {
        return PointTransactionFactory::new();
    }

    public function setting(): BelongsTo
    {
        return $this->belongsTo(PointSetting::class, 'point_setting_id', 'id');
    }

    protected function isSubtracted(): Attribute
    {
        return Attribute::make(
            get: fn () => ActivityPoint::isSubtracted($this->type),
        );
    }

    protected function isAdded(): Attribute
    {
        return Attribute::make(
            get: fn () => ActivityPoint::isAdded($this->type),
        );
    }

    public function getAction(?User $context): string
    {
        $actionParams = $this->action_params ?? [];
        $owner        = $this->owner ?? $this->user;

        if (!$context instanceof User) {
            $context = $this->user;
        }

        $userName = __p('core::phrase.deleted_user');
        $isSelf   = Arr::get($actionParams, 'is_self', 0);

        if ($owner instanceof User) {
            $isSelf   = (int) ($context?->entityId() == $owner?->entityId());
            $userName = $owner?->full_name;
        }

        Arr::set($actionParams, 'is_self', (int) $isSelf);

        if (!Arr::has($actionParams, 'user_name')) {
            Arr::set($actionParams, 'user_name', $userName);
        }

        return __p($this->action, $actionParams);
    }

    public function getActionType(?User $context): string
    {
        $actionType = $this->belongsTo(ActionType::class, 'action_id', 'id')->first();

        if (!$actionType instanceof ActionType) {
            return $this->getAction($context);
        }

        return __p($actionType->label_phrase);
    }
}

// end
