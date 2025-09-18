<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Repositories;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\TempFileModel;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;
use MetaFox\Video\Models\Video as Model;

/**
 * Interface VideoRepositoryInterface.
 * @mixin AbstractRepository
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface VideoRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * View videos.
     *
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewVideos(ContractUser $context, ContractUser $owner, array $attributes): Paginator;

    /**
     * View a video.
     *
     * @param ContractUser $context
     * @param int          $id
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function viewVideo(ContractUser $context, int $id): Model;

    /**
     * Create a video.
     *
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param array<string, mixed> $attributes
     * @return Model
     * @throws Exception
     * @see StoreBlockLayoutRequest
     */
    public function createVideo(ContractUser $context, ContractUser $owner, array $attributes): Model;

    /**
     * Update a video.
     *
     * @param ContractUser         $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     * @return Model
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updateVideo(ContractUser $context, int $id, array $attributes): Model;

    /**
     * Delete a video.
     *
     * @param ContractUser $context
     * @param int          $id
     *
     * @return bool
     */
    public function deleteVideo(ContractUser $context, int $id): bool;

    /**
     * @param int $limit
     *
     * @return Paginator
     */
    public function findFeature(int $limit = 4): Paginator;

    /**
     * @param int $limit
     *
     * @return Paginator
     */
    public function findSponsor(int $limit = 4): Paginator;

    /**
     * @param ContractUser $context
     * @param int          $id
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function approve(ContractUser $context, int $id): Content;

    /**
     * @param Content $model
     *
     * @return bool
     */
    public function isPending(Content $model): bool;

    /**
     * @param string $assetId
     * @return bool
     */
    public function deleteVideoByAssetId(string $assetId): bool;

    /**
     * @param int                  $videoId
     * @param array<string, mixed> $attributes
     * @return bool
     */
    public function doneProcessVideo(int $videoId, array $attributes): bool;

    /**
     * @param array $params
     * @return bool
     */
    public function failedProcessVideo(array $params): bool;

    /**
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param TempFileModel        $tempFile
     * @param array<string, mixed> $params
     * @return Model
     */
    public function tempFileToVideo(User $context, User $owner, TempFileModel $tempFile, array $params = []): Model;

    /**
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param Model                $video
     * @param TempFileModel        $tempFile
     * @param array<string, mixed> $params
     * @return Model
     */
    public function tempFileToExistVideo(User $context, User $owner, Model $video, TempFileModel $tempFile, array $params = []): Model;

    /**
     * @param int $groupId
     * @return Collection|null
     */
    public function getVideosByGroupId(int $groupId): ?Collection;

    /**
     * @param int $groupId
     * @return bool
     */
    public function doneProcessingVideosInGroup(int $groupId): bool;

    /**
     * @param int   $id
     * @param array $attributes
     * @return bool
     */
    public function updatePatchVideo(int $id, array $attributes): bool;

    /**
     * @param string $assetId
     * @return Model|null
     */
    public function getVideoByAssetId(string $assetId): ?Model;

    /**
     * @param int $id
     * @return Model|null
     */
    public function increaseView(int $id): ?Model;

    /**
     * @param int $id
     * @return bool
     */
    public function markVideoProcessing(int $id): bool;
}
