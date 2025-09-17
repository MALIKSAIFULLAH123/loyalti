<?php

namespace MetaFox\Activity\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use MetaFox\Activity\Database\Factories\TypeFactory;
use MetaFox\App\Models\Package;

/**
 * Class Type.
 *
 * @mixin Builder
 * @property int     $id
 * @property string  $type
 * @property ?string $title
 * @property string  $module_id
 * @property string  $entity_type
 * @property string  $title
 * @property string  $description
 * @property ?array  $value_actual
 * @property array   $value_default
 * @property bool    $is_active
 * @property bool    $is_system
 * @property array   $params
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
 */
class Type extends Model
{
    use HasFactory;

    public const CAN_COMMENT              = 1;
    public const CAN_LIKE                 = 2;
    public const CAN_SHARE                = 4;
    public const CAN_EDIT                 = 8;
    public const CAN_CREATE_FEED          = 16;
    public const ACTION_ON_FEED           = 32;
    public const CHANGE_PRIVACY_FROM_FEED = 64;
    public const CAN_REDIRECT_TO_DETAIL   = 128;
    public const PREVENT_EDIT_FEED_ITEM   = 256;

    public const CAN_COMMENT_TYPE                    = 'can_comment';
    public const CAN_LIKE_TYPE                       = 'can_like';
    public const CAN_SHARE_TYPE                      = 'can_share';
    public const CAN_EDIT_TYPE                       = 'can_edit';
    public const CAN_CREATE_FEED_TYPE                = 'can_create_feed';
    public const ALLOW_COMMENT_TYPE                  = 'allow_comment';
    public const ALLOW_LIKE_TYPE                     = 'allow_like';
    public const ALLOW_SHARE_TYPE                    = 'allow_share';
    public const ALLOW_EDIT_TYPE                     = 'allow_edit';
    public const ALLOW_CREATE_FEED_TYPE              = 'allow_create_feed';
    public const ACTION_ON_FEED_TYPE                 = 'action_on_feed';
    public const ALLOW_ACTION_ON_FEED_TYPE           = 'allow_action_on_feed';
    public const CAN_CHANGE_PRIVACY_FROM_FEED_TYPE   = 'can_change_privacy_from_feed';
    public const CAN_REDIRECT_TO_DETAIL_TYPE         = 'can_redirect_to_detail';
    public const ALLOW_CHANGE_PRIVACY_FROM_FEED_TYPE = 'allow_change_privacy_from_feed';
    public const ALLOW_REDIRECT_TO_DETAIL_TYPE       = 'allow_redirect_to_detail';
    public const PREVENT_EDIT_FEED_ITEM_TYPE         = 'prevent_from_edit_feed_item';
    public const PREVENT_DELETE_FEED_ITEMS_TYPE      = 'prevent_delete_feed_items';
    public const ALLOW_PREVENT_EDIT_FEED_ITEM_TYPE   = 'allow_prevent_from_edit_feed_item';
    public const PREVENT_DISPLAY_TAG_ON_HEADLINE     = 'prevent_display_tag_on_headline';

    protected $table = 'activity_types';

    /**
     * @return array|int[]
     */
    public function getAbilities(): array
    {
        return [
            self::CAN_COMMENT_TYPE                  => self::CAN_COMMENT,
            self::CAN_LIKE_TYPE                     => self::CAN_LIKE,
            self::CAN_SHARE_TYPE                    => self::CAN_SHARE,
            self::CAN_EDIT_TYPE                     => self::CAN_EDIT,
            self::CAN_CREATE_FEED_TYPE              => self::CAN_CREATE_FEED,
            self::ACTION_ON_FEED_TYPE               => self::ACTION_ON_FEED,
            self::CAN_CHANGE_PRIVACY_FROM_FEED_TYPE => self::CHANGE_PRIVACY_FROM_FEED,
            self::CAN_REDIRECT_TO_DETAIL_TYPE       => self::CAN_REDIRECT_TO_DETAIL,
            self::PREVENT_EDIT_FEED_ITEM_TYPE       => self::PREVENT_EDIT_FEED_ITEM,
        ];
    }

    public function getAllowValue(): array
    {
        return [
            self::ALLOW_COMMENT_TYPE                  => true,
            self::ALLOW_LIKE_TYPE                     => true,
            self::ALLOW_SHARE_TYPE                    => true,
            self::ALLOW_EDIT_TYPE                     => true,
            self::ALLOW_CREATE_FEED_TYPE              => true,
            self::ALLOW_CHANGE_PRIVACY_FROM_FEED_TYPE => true,
            self::ALLOW_REDIRECT_TO_DETAIL_TYPE       => true,
            self::ALLOW_PREVENT_EDIT_FEED_ITEM_TYPE   => true,
            self::ALLOW_ACTION_ON_FEED_TYPE           => true,
        ];
    }

    public function getAllowAbilities(): array
    {
        return [
            self::CAN_COMMENT_TYPE                  => self::ALLOW_COMMENT_TYPE,
            self::CAN_LIKE_TYPE                     => self::ALLOW_LIKE_TYPE,
            self::CAN_SHARE_TYPE                    => self::ALLOW_SHARE_TYPE,
            self::CAN_EDIT_TYPE                     => self::ALLOW_EDIT_TYPE,
            self::CAN_CREATE_FEED_TYPE              => self::ALLOW_CREATE_FEED_TYPE,
            self::ACTION_ON_FEED_TYPE               => self::ALLOW_ACTION_ON_FEED_TYPE,
            self::CAN_CHANGE_PRIVACY_FROM_FEED_TYPE => self::ALLOW_CHANGE_PRIVACY_FROM_FEED_TYPE,
            self::CAN_REDIRECT_TO_DETAIL_TYPE       => self::ALLOW_REDIRECT_TO_DETAIL_TYPE,
            self::PREVENT_EDIT_FEED_ITEM_TYPE       => self::ALLOW_PREVENT_EDIT_FEED_ITEM_TYPE,
        ];
    }

    public function getSettings(): array
    {
        return [
            self::CAN_COMMENT_TYPE,
            self::CAN_LIKE_TYPE,
            self::CAN_SHARE_TYPE,
            self::CAN_EDIT_TYPE,
            self::CAN_CREATE_FEED_TYPE,
            self::ACTION_ON_FEED_TYPE,
            self::CAN_CHANGE_PRIVACY_FROM_FEED_TYPE,
            self::CAN_REDIRECT_TO_DETAIL_TYPE,
            self::PREVENT_EDIT_FEED_ITEM_TYPE,
        ];
    }

    /**
     * @var string[]
     */
    protected $fillable = [
        'type',
        'module_id',
        'entity_type',
        'title',
        'description',
        'is_active',
        'system_value',
        'is_system',
        'value_actual',
        'value_default',
        'params',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_active'     => 'boolean',
        'is_system'     => 'boolean',
        'params'        => 'array',
        'value_default' => 'array',
        'value_actual'  => 'array',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $appends = [];

    protected static function newFactory(): TypeFactory
    {
        return TypeFactory::new();
    }

    /**
     * @return array<string, mixed>
     */
    public function describe(): array
    {
        $data = $this->toArray();

        $data = array_merge($data, $this->value_default, $this->value_actual ?? []);

        return Arr::except($data, ['value_actual', 'value_default']);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'module_id', 'alias');
    }

    public function getTitleAttribute(?string $value): string
    {
        return $value ? __p($value) : '';
    }
}
