<?php

namespace MetaFox\Forum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * @property mixed       $title
 * @property string      $title_var
 * @property Forum       $subForums
 * @property Forum       $parentForums
 * @property int         $parent_id
 * @property string      $description
 * @property string      $description_var
 * @property int         $level
 * @property string      $created_at
 * @property string      $updated_at
 * @property bool        $is_closed
 * @property ForumThread $threads
 */
class Forum extends Model implements
    Entity,
    HasAmounts,
    HasTitle,
    HasUrl
{
    use HasEntity;
    use HasAmountsTrait;
    use SoftDeletes;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'forum';

    /**
     * @var array
     */
    protected $fillable = [
        'title',
        'parent_id',
        'description',
        'ordering',
        'level',
        'total_thread',
        'is_closed',
        'total_comment',
        'total_sub',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'level'        => 'integer',
        'total_thread' => 'integer',
        'ordering'     => 'integer',
        'is_closed'    => 'boolean',
    ];

    protected $translatableAttributes = [
        'title',
        'description',
    ];

    public function getLabelAttribute($value): ?string
    {
        return $value ? __p($value) : $this->title;
    }

    public function threads(): HasMany
    {
        return $this->hasMany(ForumThread::class, 'forum_id');
    }

    public function subForums(): ?HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
            ->orderBy('ordering');
    }

    public function parentForums(): ?BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id')
            ->withTrashed();
    }

    public function toTitle(): string
    {
        return __p($this->title);
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function getTotalThread(): int
    {
        return $this->total_thread;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('forum/' . $this->entityId() . '/' . $this->toSlug());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('forum/' . $this->entityId() . '/' . $this->toSlug());
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('forum/' . $this->entityId());
    }

    public function toSubLinkAdminCP(): string
    {
        return url_utility()->makeApiUrl(sprintf('forum/forum/%s/forum/browse?parent_id=%s', $this->entityId(), $this->entityId()));
    }

    protected function toSlug(): string
    {
        $title = Arr::get($this->attributes, 'title');

        if (null === $title) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return Str::slug(__p($title));
    }

    public function buildSeoData(string $resolution): array
    {
        if ($resolution == 'admin') {
            return [];
        }

        $generalBreadcrumbs = [
            ['label' => __p('forum::web.forums'), 'to' => '/forum'],
        ];

        $modelBreadcrumbs = resolve(ForumRepositoryInterface::class)->getBreadcrumbs($this->entityId());

        if (count($modelBreadcrumbs)) {
            $generalBreadcrumbs = array_merge($generalBreadcrumbs, $modelBreadcrumbs);
        }

        return [
            'breadcrumbs' => $generalBreadcrumbs,
        ];
    }

    public function toSubForumLink(): string
    {
        return sprintf('/forum/forum/%s/forum/browse?parent_id=%s', $this->id, $this->id);
    }

    public function getTitleAttribute(mixed $value): string
    {
        return is_string($value) ? __p($value) : '';
    }

    public function getTitleVarAttribute(): string
    {
        $varName = Arr::get($this->attributes, 'title', '');

        return is_string($varName) ? $varName : '';
    }

    public function getDescriptionAttribute(mixed $value): string
    {
        return is_string($value) ? __p($value) : '';
    }

    public function getDescriptionVarAttribute(mixed $value): string
    {
        $varName = Arr::get($this->attributes, 'description', '');

        return is_string($varName) ? $varName : '';
    }
}
