<?php

namespace MetaFox\LiveStreaming\Repositories;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\Platform\Contracts\ActionEntity;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface LiveVideo.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface LiveVideoRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * Create a video.
     *
     * @param  ContractUser         $context
     * @param  ContractUser         $owner
     * @param  array<string, mixed> $attributes
     * @return Model
     * @throws Exception
     * @see StoreBlockLayoutRequest
     */
    public function createLiveVideo(ContractUser $context, ContractUser $owner, array $attributes): Model;

    /**
     * @param  Model $liveVideo
     * @return void
     */
    public function createStoryItem(Model $liveVideo): void;
    /**
     * Update a video.
     *
     * @param  ContractUser            $context
     * @param  int                     $id
     * @param  array<string, mixed>    $attributes
     * @param  bool                    $isGoLive
     * @return Model
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updateLiveVideo(ContractUser $context, int $id, array $attributes, bool $isGoLive = false): Model;

    /**
     * Delete a video.
     *
     * @param ContractUser $context
     * @param int          $id
     *
     * @return int
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function deleteLiveVideo(ContractUser $context, int $id): int;

    /**
     * @param  ContractUser           $context
     * @param  ContractUser           $owner
     * @param  array<string, mixed>   $attributes
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewLiveVideos(ContractUser $context, ContractUser $owner, array $attributes): Paginator;

    /**
     * View a live video.
     *
     * @param ContractUser $context
     * @param int          $id
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function viewLiveVideo(ContractUser $context, int $id): Model;

    /**
     * @param  int         $id
     * @param  Model       $liveVideo
     * @param  string|null $dateTime
     * @return bool
     */
    public function startLiveStream(int $id, Model $liveVideo, string $dateTime = null): bool;

    /**
     * @param  int         $id
     * @param  Model|null  $liveVideo
     * @param  string|null $dateTime
     * @param  bool        $isDelete
     * @return Model
     */
    public function stopLiveStream(int $id, ?Model $liveVideo = null, string $dateTime = null, bool $isDelete = false): Model;

    /**
     * @param  int  $id
     * @return void
     */
    public function pingStreaming(int $id): void;

    /**
     * @param  int  $id
     * @return void
     */
    public function pingViewer(int $id): void;

    /**
     * @param  int    $id
     * @return string
     */
    public function getVideoPlayback(int $id): string;

    /**
     * @param  int   $id
     * @return array
     */
    public function getThumbnailPlayback(int $id): array;

    /**
     * @param array $data
     *
     * @return bool
     */
    public function handleMuxWebhook(array $data): bool;

    /**
     * Go live.
     *
     * @param  ContractUser            $context
     * @param  ContractUser            $owner
     * @param  array<string, mixed>    $attributes
     * @return Model
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function startGoLive(ContractUser $context, ContractUser $owner, array $attributes): Model;

    /**
     * Update viewer.
     *
     * @param  Model                   $liveVideo
     * @param  array<string, mixed>    $attributes
     * @return Model|bool
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updateViewerCount(Model $liveVideo, array $attributes): Model|bool;

    /**
     * Remove viewer.
     *
     * @param  Model                   $liveVideo
     * @param  ContractUser            $context
     * @return Model|bool
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function removeViewerCount(Model $liveVideo, ContractUser $context): Model|bool;

    /**
     * @param  Model        $liveVideo
     * @param  ActionEntity $comment
     * @return bool
     */
    public function addLiveComment(Model $liveVideo, ActionEntity $comment): bool;

    /**
     * @param  Model  $liveVideo
     * @param  Entity $like
     * @return bool
     */
    public function addLiveLike(Model $liveVideo, Entity $like): bool;

    /**
     * @param  Model $liveVideo
     * @param  array $playback
     * @return bool
     */
    public function updateAssets(Model $liveVideo, array $playback = []): bool;

    /**
     * @param  Model        $liveVideo
     * @param  ActionEntity $comment
     * @return bool
     */
    public function removeLiveComment(Model $liveVideo, ActionEntity $comment): bool;

    /**
     * @param  Model        $liveVideo
     * @param  ActionEntity $comment
     * @return bool
     */
    public function updateLiveComment(Model $liveVideo, ActionEntity $comment): bool;

    /**
     * @param  Model  $liveVideo
     * @return string
     */
    public function getDuration(Model $liveVideo): string;

    /**
     * @param ContractUser $context
     * @param Model        $liveVideo
     *
     * @return Collection
     * @throws AuthorizationException
     */
    public function getTaggedFriends(ContractUser $context, Model $liveVideo): Collection;

    /**
     * @param  Model  $liveVideo
     * @param  Entity $like
     * @return bool
     */
    public function removeLiveLike(Model $liveVideo, Entity $like): bool;

    /**
     * @param  ContractUser $context
     * @param  string       $streamId
     * @param  Model|null   $liveVideo
     * @param  bool|null    $warning
     * @param  bool|null    $mobileLive
     * @return void
     */
    public function validateLimitTime(ContractUser $context, string $streamId, ?Model $liveVideo = null, ?bool $warning = false, ?bool $mobileLive = false): void;

    /**
     * @param  Model $liveVideo
     * @param  array $tags
     * @return void
     */
    public function updateTagFriends(Model $liveVideo, array $tags): void;

    /**
     * @param  Model $liveVideo
     * @param  bool  $noFeed
     * @return bool
     */
    public function publishVideoActivity(Model $liveVideo, bool $noFeed = false): bool;

    /**
     * @param  string      $streamKey
     * @param  string|null $activeAssetId
     * @param  string|null $type
     * @return mixed
     */
    public function createLiveVideoWithStreamKey(string $streamKey, ?string $activeAssetId = null, ?string $type = ''): mixed;

    /**
     * @param  ContractUser $context
     * @param  array        $attributes
     * @return bool
     */
    public function validateStreamKey(ContractUser $context, array $attributes): bool;

    /**
     * @param  ContractUser $context
     * @param  array        $attributes
     * @return bool
     */
    public function getLiveByStreamKey(ContractUser $context, array $attributes): int;
}
