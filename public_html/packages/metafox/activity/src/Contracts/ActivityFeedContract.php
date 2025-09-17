<?php

namespace MetaFox\Activity\Contracts;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Post;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\FeedAction;
use Illuminate\Database\Eloquent\Model;

interface ActivityFeedContract
{
    /**
     * @param  FeedAction $feedAction
     * @return Feed|null
     */
    public function createActivityFeed(FeedAction $feedAction): ?Feed;

    /**
     * @param  int  $feedId
     * @return bool
     */
    public function deleteActivityFeed(int $feedId): bool;

    /**
     * @param  string    $content
     * @param  int       $privacy
     * @param  User      $user
     * @param  User|null $owner
     * @param  array     $list
     * @param            $relations
     * @return Post
     */
    public function createActivityPost(string $content, int $privacy, User $user, ?User $owner = null, array $list = [], $relations = []): Post;

    /**
     * @param  Feed  $feed
     * @param  User  $context
     * @param  int   $userAutoTag
     * @param  array $attributes
     * @return void
     */
    public function putToTagStream(Feed $feed, User $context, int $userAutoTag, array $attributes = []): void;

    /**
     * @param  int        $bgStatusId
     * @return array|null
     * @deprecated  Remove in 5.1.13
     */
    public function getBackgroundStatusImage(int $bgStatusId): ?array;

    /**
     * @param  int                     $bgStatusId
     * @return JsonResource|array|null
     */
    public function getBackgroundStatus(int $bgStatusId): JsonResource|array|null;

    /**
     * @param  int       $shareId
     * @return Feed|null
     */
    public function getFeedByShareId(int $shareId): ?Feed;

    /**
     * @param  Feed $feed
     * @return bool
     */
    public function sendFeedComposeNotification(Feed $feed): bool;

    /**
     * @param  string $ownerType
     * @param  int    $ownerId
     * @return void
     */
    public function deleteCoreFeedsByOwner(string $ownerType, int $ownerId): void;

    /**
     * @param  array $conditions
     * @return void
     */
    public function deleteTagsStream(array $conditions): void;

    /**
     * @param  User     $context
     * @param  Content  $feed
     * @param  int|null $representativePrivacy
     * @return array
     */
    public function getPrivacyDetail(User $context, Content $feed, ?int $representativePrivacy = null): array;

    /**
     * @param  Model       $model
     * @param  string|null $fromResource
     * @return Feed|null
     */
    public function createFeedFromFeedSource(Model $model, ?string $fromResource = Feed::FROM_APP_RESOURCE): ?Feed;

    /**
     * @param  Feed  $feed
     * @param  array $attributes
     * @return void
     */
    public function putToStream(Feed $feed, array $attributes = []): void;
}
