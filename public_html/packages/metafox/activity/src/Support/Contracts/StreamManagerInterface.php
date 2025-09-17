<?php

namespace MetaFox\Activity\Support\Contracts;

use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;

interface StreamManagerInterface
{
    /**
     * @param  User  $user
     * @return $this
     */
    public function setUser(User $user): self;

    /**
     * @return array
     */
    public function getEagerLoads(): array;

    /**
     * @param  array|null $ids
     * @return $this
     */
    public function setLoadedSponsoredFeedIds(?array $ids): self;

    /**
     * @param  bool  $value
     * @return $this
     */
    public function setIsGreaterThanLastFeed(bool $value = true): self;

    /**
     * @return bool
     */
    public function getIsGreaterThanLastFeed(): bool;

    /**
     * @return array
     */
    public function getStatus(): array;

    /**
     * @param  array|null $status
     * @return $this
     */
    public function setStatus(?array $status = null): self;

    /**
     * @return bool
     */
    public function isOnlyFriends(): bool;

    /**
     * @param  bool  $onlyFriends
     * @return $this
     */
    public function setOnlyFriends(bool $onlyFriends): self;

    /**
     * @return bool
     */
    public function isSearchByStreamId(): bool;

    /**
     * @param  bool  $value
     * @return $this
     */
    public function setSearchByStreamId(bool $value): self;

    /**
     * @return bool
     */
    public function isViewOnProfile(): bool;

    /**
     * @param  bool  $isPreviewTag
     * @return $this
     */
    public function setPreviewTag(bool $isPreviewTag): self;

    /**
     * @return int
     */
    public function isPreviewTag(): int;

    /**
     * @param  bool  $isViewOnProfile
     * @return $this
     */
    public function setIsViewOnProfile(bool $isViewOnProfile): self;

    /**
     * @return int|null
     */
    public function getUserId(): ?int;

    /**
     * @return int|null
     */
    public function getOwnerId(): ?int;

    /**
     * @param  int   $ownerId
     * @return $this
     */
    public function setOwnerId(int $ownerId): self;

    /**
     * @param  int   $userId
     * @return $this
     */
    public function setUserId(int $userId): self;

    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @param  int   $limit
     * @return $this
     */
    public function setLimit(int $limit): self;

    /**
     * @return array
     */
    public function getSelect(): array;

    /**
     * @param  array $select
     * @return $this
     */
    public function setSelect(array $select): self;

    /**
     * @return array
     */
    public function getSortFields(): array;

    /**
     * @param  string $view
     * @param  string $sortType
     * @return $this
     */
    public function setSortFields(string $view, string $sortType = MetaFoxConstant::SORT_DESC): self;

    /**
     * @return string
     */
    public function getSortView(): string;

    /**
     * @return string
     */
    public function getSortType(): string;

    /**
     * @return array
     */
    public function getFeedSortView(): array;

    /**
     * @param  string $hashtag
     * @return $this
     */
    public function setHashtag(string $hashtag): self;

    /**
     * @param  string $search
     * @return $this
     */
    public function setSearchString(string $search): self;

    /**
     * @param  bool  $isViewSearch
     * @return $this
     */
    public function setIsViewSearch(bool $isViewSearch): self;

    /**
     * @return bool
     */
    public function isViewSearch(): bool;

    /**
     * @return $this
     */
    public function isApproved(): self;

    /**
     * @return $this
     */
    public function isDenied(): self;

    /**
     * @return $this
     */
    public function isPending(): self;

    /**
     * @return $this
     */
    public function isRemoved(): self;

    /**
     * @return void
     */
    public function fetchPinnedFeeds(): void;

    /**
     * @param  int|null    $lastFeedId
     * @param  string|null $timeFrom
     * @param  string|null $timeTo
     * @return mixed
     */
    public function fetchStream(?int $lastFeedId = null, ?string $timeFrom = null, ?string $timeTo = null);

    /**
     * @return array
     */
    public function getPinnedFeedIds(): array;

    /**
     * @param  Collection $result
     * @param  int        $need
     * @param  int|null   $lastFeedId
     * @param  int        $try
     * @return void
     */
    public function fetchStreamContinuous(Collection $result, int $need, ?int $lastFeedId, int $try): void;

    /**
     * @param  array      $feedIds
     * @return Collection
     */
    public function toFeeds(array $feedIds): Collection;

    /**
     * @param  bool  $isViewHashtag
     * @return $this
     */
    public function setIsViewHashtag(bool $isViewHashtag): self;

    /**
     * @return bool
     */
    public function isViewHashtag(): bool;

    /**
     * @param  array $additionalConditions
     * @return $this
     */
    public function setAdditionalConditions(array $additionalConditions): self;

    /**
     * @return bool
     */
    public function hasAdditionalConditions(): bool;

    /**
     * @param  Collection $collection
     * @return mixed
     */
    public function addPinnedFeedIds(Collection $collection);

    /**
     * @param  array|null $loadedSponsoredFeedIds
     * @return array
     */
    public function fetchSponsoredFeeds(?array $loadedSponsoredFeedIds = null): array;

    /**
     * @return int
     */
    public function getSponsoredFeedLimit(): int;

    /**
     * @param  Collection $collection
     * @return void
     */
    public function addSponsoredFeed(Collection $collection): void;
}
