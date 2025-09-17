<?php

namespace MetaFox\Platform\Contracts;

interface Membership extends Entity, HasFeed
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
     * @return int|null
     */
    public function privacy(): ?int;

    /**
     * @return int|null
     */
    public function privacyUserId(): ?int;
}
