<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Group\Database\Factories\RequestFactory;
use MetaFox\Group\Notifications\DeniedRequestNotification;
use MetaFox\Group\Notifications\PendingRequestNotification;
use MetaFox\Group\Support\Browse\Scopes\Request\StatusScope;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\UserEntity;

/**
 * Class Request.
 *
 * @property int             $id
 * @property int             $status_id
 * @property int             $group_id
 * @property int|null        $reviewer_id
 * @property string|null     $reviewer_type
 * @property string|null     $reason
 * @property Group           $group
 * @property Collection      $answers
 * @property User|null       $reviewer
 * @property UserEntity|null $reviewerEntity
 *
 * @method static RequestFactory factory(...$parameters)
 */
class Request extends Model implements
    Entity,
    IsNotifyInterface
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use CheckModeratorSettingTrait;

    public const ENTITY_TYPE = 'group_request';

    public const STATUS_PENDING  = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_DENIED   = 2;
    public const STATUS_CANCEL   = 3;

    protected $table = 'group_requests';

    protected $fillable = [
        'status_id',
        'group_id',
        'user_id',
        'user_type',
        'reviewer_id',
        'reviewer_type',
        'reason',
    ];

    protected static function newFactory(): RequestFactory
    {
        return RequestFactory::new();
    }

    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id')->withTrashed();
    }

    public function reviewerType(): string
    {
        return $this->reviewer_type ?? '';
    }

    public function reviewerId(): int
    {
        return $this->reviewer_id ?? 0;
    }

    public function reviewer(): MorphTo
    {
        return $this->morphTo('reviewer', 'reviewer_type', 'reviewer_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function reviewerEntity(): BelongsTo
    {
        return $this->belongsTo(UserEntity::class, 'reviewer_id', 'id')->withTrashed();
    }

    public function getReviewerAttribute()
    {
        return LoadReduce::getEntity($this->reviewerType(), $this->reviewerId(), fn () => $this->getRelationValue('reviewer'));
    }

    public function getReviewerEntityAttribute()
    {
        return LoadReduce::getEntity('user_entity', $this->reviewerId(), fn () => $this->getRelationValue('reviewerEntity'));
    }

    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'id', 'group_id')->withTrashed();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answers::class, 'request_id', 'id');
    }

    public function statusText(): string
    {
        $map = StatusScope::getStatusLabelMap();

        return $map[$this->status_id] ?? __p('core::phrase.unknown');
    }

    public function toNotification(): ?array
    {
        $group        = $this->group;
        $users        = [];
        $notification = new PendingRequestNotification($this);

        if (!$group instanceof Group) {
            return null;
        }

        if ($this->status_id === self::STATUS_APPROVED && $group->isPublicPrivacy()) {
            $groupOwner = $group->user;
            if (!$groupOwner instanceof User) {
                return null;
            }

            return [$groupOwner, $notification];
        }

        $authors = $group->authorizers()
            ->with(['user'])
            ->get();

        foreach ($authors as $author) {
            if (!$this->checkModeratorSetting($author->user, $group, 'approve_or_deny_membership_request')) {
                continue;
            }

            $users[] = $author->user;
        }

        if (empty($users)) {
            return null;
        }

        return [$users, $notification];
    }

    public function isDenied(): bool
    {
        return $this->status_id === self::STATUS_DENIED;
    }

    public function toDeniedNotification(): array
    {
        return [$this->user, new DeniedRequestNotification($this)];
    }
}
