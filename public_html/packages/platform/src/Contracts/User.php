<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Spatie\Permission\Traits\HasRoles;

/**
 * Interface User.
 * @mixin HasRoles
 * @mixin \App\Models\User
 */
interface User extends Content, BigNumberId, PlatformRole, HasLocalePreference
{
    /**
     * @return string|null
     */
    public function getPendingMessage(): ?string;

    /**
     * @return string|null
     */
    public function getApprovedMessage(): ?string;

    /**
     * @return string|null
     */
    public function getDeclinedMessage(): ?string;

    /**
     * @return bool
     */
    public function hasPendingMode(): bool;

    /**
     * @return bool|null
     */
    public function isPendingMode(): ?bool;

    /**
     * @return array<string, mixed>
     */
    public function toUserResource(): array;

    /**
     * Determine if resource can be blocked.
     *
     * @return bool
     */
    public function canBeBlocked(): bool;

    /**
     * Indicates its content has privacy.
     *
     * @return ?int
     */
    public function getItemPrivacy(): ?int;

    /**
     * @return bool
     */
    public function isGuest(): bool;

    /**
     * @return bool
     */
    public function hasStaffRole(): bool;

    /**
     * @return bool
     */
    public function hasAdminRole(): bool;

    /**
     * @return bool
     */
    public function hasSuperAdminRole(): bool;

    /**
     * Indicates this has content-based privacy, so it will be treated as a
     * regular content (like Blog, Poll) when checking for view privacy.
     *
     * @return bool
     */
    public function hasContentPrivacy(): bool;

    /**
     * @return int|null
     */
    public function getRepresentativePrivacy(): ?int;

    /**
     * @param  int                       $privacy
     * @return array<string, mixed>|null
     */
    public function getRepresentativePrivacyDetail(int $privacy): ?array;

    /**
     * This method shall return the highest priority role base on user role level.
     *
     * @return int
     */
    public function getSmallestRoleId(): int;

    /**
     * @return string|null
     */
    public function toDeclinedContentLink(): ?string;

    /**
     * @return string|null
     */
    public function toDeclinedContentUrl(): ?string;

    public function isDeleted(): bool;

    /**
     * @return bool
     */
    public function isTaggingAllowed(): bool;
}
