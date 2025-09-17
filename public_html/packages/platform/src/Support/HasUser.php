<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Support;

use Illuminate\Database\Eloquent\SoftDeletes;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\UserRole;
use MetaFox\User\Contracts\UserHasValuePermission;

/**
 * Trait HasUserEntity.
 * @mixin HasContent
 * @mixin HasEntity
 *
 * @property string $deleted_at
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
trait HasUser
{
    use HasContent;
    use SoftDeletes;
    use HasBigNumberId;

    /**
     * @return bool|null
     */
    public function isPendingMode(): ?bool
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getPendingMessage(): ?string
    {
        if (!$this->hasPendingMode()) {
            return null;
        }

        return __p('core::phrase.thanks_for_your_item_for_approval');
    }

    /**
     * @return string|null
     */
    public function getApprovedMessage(): ?string
    {
        if (!$this->hasPendingMode()) {
            return null;
        }

        return __p('core::phrase.an_admin_approved_your_post_in_entity_type_title', [
            'entity_type'  => $this->entityType(),
            'entity_title' => $this->toTitle(),
        ]);
    }

    public function getDeclinedMessage(): ?string
    {
        if (!$this->hasPendingMode()) {
            return null;
        }

        return __p('core::phrase.an_admin_declined_your_post_in_entity_type_title', [
            'entity_type'  => $this->entityType(),
            'entity_title' => $this->toTitle(),
        ]);
    }

    /**
     * @return bool
     */
    public function hasPendingMode(): bool
    {
        return null !== $this->isPendingMode();
    }

    public function getItemPrivacy(): ?int
    {
        return null;
    }

    /**
     * @return bool
     */
    public function isGuest(): bool
    {
        return $this->entityId() === MetaFoxConstant::GUEST_USER_ID;
    }

    /**
     * @return bool
     */
    public function hasAdminRole(): bool
    {
        if ($this->hasSuperAdminRole()) {
            return true;
        }

        return method_exists($this, 'hasRole') && $this->hasRole(UserRole::ADMIN_USER);
    }

    /**
     * @return bool
     */
    public function hasStaffRole(): bool
    {
        return method_exists($this, 'hasRole') && $this->hasRole(UserRole::STAFF_USER);
    }

    /**
     * @return bool
     */
    public function hasSuperAdminRole(): bool
    {
        return method_exists($this, 'hasRole') && $this->hasRole(UserRole::SUPER_ADMIN_USER);
    }

    /**
     * @return bool
     */
    public function hasContentPrivacy(): bool
    {
        return false;
    }

    /**
     * @return int|null
     */
    public function getRepresentativePrivacy(): ?int
    {
        return null;
    }

    public function getRepresentativePrivacyDetail(int $privacy): ?array
    {
        return null;
    }

    public function getSmallestRoleId(): int
    {
        if (!$this instanceof UserHasValuePermission) {
            return 0;
        }

        return $this->roles->pluck('id')->sort()->first();
    }

    public function toDeclinedContentLink(): ?string
    {
        return null;
    }

    public function toDeclinedContentUrl(): ?string
    {
        return null;
    }

    public function isDeleted(): bool
    {
        return array_key_exists('deleted_at', $this->attributes) && $this->deleted_at;
    }

    public function preferredLocale(): ?string
    {
        return null;
    }

    public function getTotalCommentAttribute(): int
    {
        return 0;
    }

    public function getTotalReplyAttribute(): int
    {
        return 0;
    }
    public function isTaggingAllowed(): bool
    {
        return true;
    }
}
