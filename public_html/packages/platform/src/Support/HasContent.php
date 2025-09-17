<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Support;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\HasResourceStream;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasFeed;
use MetaFox\Platform\Traits\Helpers\CommentTrait;

/**
 * Trait HasResourceEntity.
 * @property mixed $is_draft
 * @property mixed $is_approved
 */
trait HasContent
{
    use HasEntity;
    use HasAmountsTrait;
    use HasFeed;
    use CommentTrait;

    public function getThumbnail(): ?string
    {
        return "$this->image_file_id";
    }

    public function likes(): MorphMany
    {
        /** @var string $related */
        $related = Relation::getMorphedModel('like');

        return $this->morphMany($related, 'item', 'item_type', 'item_id', $this->primaryKey);
    }

    public function shares(): MorphMany
    {
        /** @var string $related */
        $related = Relation::getMorphedModel('share');

        return $this->morphMany($related, 'item', 'item_type', 'item_id', $this->primaryKey);
    }

    /**
     * Create {item}_privacy_streams.
     *
     * @param array<mixed> $data
     */
    public function syncPrivacyStreams(array $data): void
    {
        if ($this instanceof HasResourceStream) {
            if ($this->exists) {
                $this->privacyStreams()->createMany($data);
            }
        }
    }

    /**
     * Delete {item}_privacy_streams.
     */
    public function deletePrivacyStreams(): void
    {
        if (!$this instanceof HasResourceStream) {
            return;
        }

        $this->privacyStreams()->delete();
    }

    public function isDraft()
    {
        return array_key_exists('is_draft', $this->attributes) ? $this->is_draft : false;
    }

    public function isPublished()
    {
        return array_key_exists('is_draft', $this->attributes) ? !$this->is_draft : true;
    }

    public function isApproved()
    {
        return array_key_exists('is_approved', $this->attributes) ? (bool) $this->is_approved : true;
    }

    public function reactItem()
    {
        return $this;
    }

    public function privacyItem()
    {
        return $this;
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiResourceUrl($this->entityType(), $this->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiResourceFullUrl($this->entityType(), $this->entityId());
    }

    public function isOwnerPending(): bool
    {
        $owner = $this->owner;

        if (null === $owner) {
            return false;
        }

        return $owner->hasPendingMode() && !$this->isApproved();
    }

    /**
     * This method is used for getting pending message if item is pending.
     */
    public function getOwnerPendingMessage(): ?string
    {
        if ($this->isApproved()) {
            return null;
        }

        if (!$this->isOwnerPending()) {
            return __p('core::phrase.thanks_for_your_item_for_approval');
        }

        return $this->owner->getPendingMessage();
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileResourceUrl($this->entityType(), $this->entityId());
    }

    public function __toString()
    {
        return $this->toTitle();
    }

    /**
     * @return bool
     */
    public function isSponsored(): bool
    {
        return (bool) Arr::get($this->attributes, 'is_sponsor', false);
    }

    /**
     * @return bool
     */
    public function isSponsoredInFeed(): bool
    {
        return (bool) Arr::get($this->attributes, 'sponsor_in_feed', false);
    }

    /**
     * @return array|null
     */
    public function toSponsorData(): ?array
    {
        return null;
    }

    /**
     * @return void
     */
    public function enableSponsor(): void
    {
        if (!Arr::has($this->attributes, 'is_sponsor')) {
            return;
        }

        $this->update(['is_sponsor' => 1]);
    }

    /**
     * @return void
     */
    public function disableSponsor(): void
    {
        if (!Arr::has($this->attributes, 'is_sponsor')) {
            return;
        }

        $this->update(['is_sponsor' => 0]);
    }

    public function enableFeedSponsor(): void
    {
        if (!Arr::has($this->attributes, 'sponsor_in_feed')) {
            return;
        }

        $this->update(['sponsor_in_feed' => 1]);
    }

    /**
     * @return void
     */
    public function disableFeedSponsor(): void
    {
        if (!Arr::has($this->attributes, 'sponsor_in_feed')) {
            return;
        }

        $this->update(['sponsor_in_feed' => 0]);
    }

    public function toSitemapUrl(): ?string
    {
        return $this->toUrl();
    }

    /**
     * @return array|null
     */
    public function toFeaturedData(): ?array
    {
        return null;
    }

    /**
     * @return void
     */
    public function activateFeature(): void
    {
        if (null === $this->toFeaturedData()) {
            return;
        }

        $this->update(['is_featured' => 1]);
    }

    public function deactivateFeature(): void
    {
        if (null === $this->toFeaturedData()) {
            return;
        }

        $this->update(['is_featured' => 0]);
    }
}
