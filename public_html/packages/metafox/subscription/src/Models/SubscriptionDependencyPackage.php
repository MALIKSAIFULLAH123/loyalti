<?php

namespace MetaFox\Subscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Subscription\Database\Factories\SubscriptionDependencyPackageFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class SubscriptionDependencyPackage.
 *
 * @property int                 $id
 * @property int                 $current_package_id
 * @property int                 $dependency_package_id
 * @property string              $dependency_type
 * @property SubscriptionPackage $dependencyPackage
 * @method   static              SubscriptionDependencyPackageFactory factory(...$parameters)
 */
class SubscriptionDependencyPackage extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'subscription_dependency_package';

    protected $table = 'subscription_dependency_packages';

    /** @var string[] */
    protected $fillable = [
        'current_package_id',
        'dependency_package_id',
        'dependency_type',
    ];

    public $timestamps = false;

    public function dependencyPackage(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class, 'dependency_package_id');
    }

    /**
     * @return SubscriptionDependencyPackageFactory
     */
    protected static function newFactory()
    {
        return SubscriptionDependencyPackageFactory::new();
    }
}

// end
