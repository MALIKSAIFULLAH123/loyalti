<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Interface ActionEntity.
 *
 * @property User       $user
 * @property UserEntity $userEntity
 * @property User       $owner
 * @property UserEntity $ownerEntity
 * @property Content    $item
 */
interface ActionEntity extends Entity
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
     * @return User|MorphTo
     */
    public function user();

    /**
     * @return UserEntity|BelongsTo
     */
    public function userEntity();

    /**
     * @return int
     */
    public function ownerId(): int;

    /**
     * @return string
     */
    public function ownerType(): string;

    /**
     * @return User|MorphTo
     */
    public function owner();

    /**
     * @return UserEntity|BelongsTo
     */
    public function ownerEntity();

    /**
     * @return MorphTo|null
     */
    public function item(): ?MorphTo;

    /**
     * @return string
     */
    public function itemType(): string;

    /**
     * @return int
     */
    public function itemId(): int;
}
