<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

/**
 * Interface Content.
 *
 * @mixin Model
 *
 * @property User       $user
 * @property User       $owner
 * @property UserEntity $userEntity
 * @property UserEntity $ownerEntity
 * @property Collection $comments
 * @property int        $total_comment
 * @property int        $total_reply
 * @property int        $total_like
 * @property string     $created_at
 * @property string     $updated_at
 * @property int        $sponsor_in_feed
 * @property int        $is_sponsor
 * @property bool       $is_featured
 */
interface Content extends Entity, HasAmounts, HasFeed, HasPolicy, HasUrl, HasTitle
{
    /**
     * @return int
     */
    public function userId(): int;

    /**
     * @return string
     */
    public function userType(): string;

    /**
     * @return int
     */
    public function ownerId(): int;

    /**
     * @return string
     */
    public function ownerType(): string;

    /**
     * @return User|MorphTo|BelongsTo
     */
    public function user();

    /**
     * @return UserEntity|BelongsTo
     */
    public function userEntity();

    /**
     * @return User|MorphTo|BelongsTo
     */
    public function owner();

    /**
     * @return UserEntity|BelongsTo
     */
    public function ownerEntity();

    /**
     * Get indicate item handle privacy logic.
     *
     * @return self
     */
    public function reactItem();

    /**
     * Get indicate item handle privacy logic.
     *
     * @return ?self
     */
    public function privacyItem();

    /**
     * @return bool
     */
    public function isDraft();

    /**
     * @return bool
     */
    public function isPublished();

    /**
     * @return bool
     */
    public function isApproved();

    /**
     * @return bool
     */
    public function isOwnerPending(): bool;

    /**
     * @return string|null
     */
    public function getOwnerPendingMessage(): ?string;

    /**
     * @return bool
     */
    public function isSponsored(): bool;

    /**
     * @return bool
     */
    public function isSponsoredInFeed(): bool;

    /**
     * @return array|null
     */
    public function toSponsorData(): ?array;

    /**
     * @return void
     */
    public function enableSponsor(): void;

    /**
     * @return void
     */
    public function disableSponsor(): void;

    /**
     * @return void
     */
    public function enableFeedSponsor(): void;

    /**
     * @return void
     */
    public function disableFeedSponsor(): void;

    /**
     * @return array|null
     */
    public function toFeaturedData(): ?array;

    /**
     * @return void
     */
    public function activateFeature(): void;

    public function deactivateFeature(): void;
}
