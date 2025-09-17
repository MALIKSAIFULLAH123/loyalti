<?php

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use MetaFox\Platform\Facades\LoadReduce;

/**
 * Trait HasFeed.
 */
trait HasFeed
{
    use HasSetActivityTypeIdTrait;

    /**
     * Morph Relation.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function activity_feed(): ?MorphOne
    {
        if (!app_active('metafox/activity')) {
            return null;
        }

        /** @var string $related */
        $related = Relation::getMorphedModel('feed');

        return $this->morphOne($related, 'item', 'item_type', 'item_id');
    }

    public function getActivityFeedAttribute()
    {
        if (!app_active('metafox/activity')) {
            return null;
        }

        /* @see \MetaFox\Activity\Support\LoadMissingFeed::handle */
        return LoadReduce::remember(sprintf('feed::of(%s:%s)', $this->entityType(), $this->entityId()), function () {
            return $this->getRelationValue('activity_feed');
        });
    }
}
