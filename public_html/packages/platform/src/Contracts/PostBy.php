<?php

namespace MetaFox\Platform\Contracts;

interface PostBy
{
    /**
     * @param User         $user
     * @param Content|null $content
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkPostBy(User $user, Content $content = null): bool;

    /**
     * @param User         $user
     * @param Content|null $content
     * @return bool
     */
    public function checkContentShareable(User $user, Content $content = null): bool;

    /**
     * Get privacy base on owner's requirement
     * Page => 0 (public)
     * Group => privacy_item
     * User => 1 (friend).
     * @return int
     */
    public function getPrivacyPostBy(): int;

    /**
     * @return null|string
     */
    public function hasNamedNotification(): ?string;

    /**
     * @return bool
     */
    public function hasFeedDetailPage(): bool;

    /**
     * @param User         $user
     * @param Content|null $content
     * @return bool
     */
    public function hasRemoveFeed(User $user, Content $content = null): bool;
}
