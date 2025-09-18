<?php

namespace MetaFox\Story\Contracts;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StorySet;
use MetaFox\Story\Support\StorySupport;

interface StoryContract
{
    /**
     * @param int $lifespan
     *
     * @return int
     */
    public function setExpired(int $lifespan = StorySupport::USER_STORY_LIFESPAN): int;

    /**
     * @param User  $context
     * @param Story $story
     *
     * @return bool
     */
    public function hasSeen(User $context, Story $story): bool;

    /**
     * @param User  $context
     * @param Story $story
     *
     * @return bool
     */
    public function hasLive(User $context, Story $story): bool;

    /**
     * @param User  $context
     * @param Story $story
     *
     * @return array|null
     */
    public function getReactionByUser(User $context, Story $story): ?array;

    /**
     * @param User     $context
     * @param StorySet $storySet
     *
     * @return Collection
     */
    public function getStories(User $context, StorySet $storySet): Collection;

    /**
     * @param User                 $context
     * @param array<string, mixed> $params
     *
     * @return Collection
     */
    public function viewServices(User $context, array $params = []): Collection;

    /**
     * @return VideoServiceInterface
     */
    public function getDefaultServiceClass(): VideoServiceInterface;

    /**
     * @return array
     */
    public function getServicesOptions(): array;

    /**
     * @return array
     */
    public function allowTypeViewStory(): array;

    /**
     * @return array
     */
    public function getFontStyleOptions(): array;

    /**
     * @return array
     */
    public function getPrivacyOptions(): array;

    /**
     * @param string $driver
     *
     * @return VideoServiceInterface
     */
    public function getVideoServiceClassByDriver(string $driver): VideoServiceInterface;

    /**
     * @return array
     */
    public function getLifespanOptions(): array;

    /**
     * @return bool
     */
    public function checkReadyService(): bool;

    /**
     * @return int
     */
    public function getConfiguredVideoDuration(): int;

    /**
     * @return array
     */
    public function getVideoDurationOptions(): array;

    /**
     * @return array
     */
    public function getMutedOptions(): array;

    /**
     * @param string $value
     *
     * @return string|null
     */
    public function parseMuteExpiredAt(string $value): ?string;

    /**
     * @param User $context
     * @param int  $ownerId
     *
     * @return bool
     */
    public function isMuted(User $context, int $ownerId): bool;

    /**
     * @return int
     */
    public function getLifespanDefault(): int;
}
