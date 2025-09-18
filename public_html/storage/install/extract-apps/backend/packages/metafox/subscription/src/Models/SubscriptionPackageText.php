<?php

namespace MetaFox\Subscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model_text.stub.
 */

/**
 * Class SubscriptionPackage.
 *
 * @property int    $id
 * @property string $text
 * @property string $text_parsed
 *
 * @mixin Builder
 */
class SubscriptionPackageText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'subscription_packages_text';

    public $timestamps = false;

    public $incrementing = false;

    /**
     * @var string
     */
    protected $table = 'subscription_packages_text';

    protected $fillable = [
        'id',
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class, 'id');
    }
}
