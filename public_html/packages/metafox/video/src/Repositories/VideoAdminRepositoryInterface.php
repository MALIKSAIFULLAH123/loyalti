<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\Video\Models\VerifyProcess;
use MetaFox\Video\Models\Video as Model;

/**
 * Interface VideoAdminRepositoryInterface.
 * @mixin AbstractRepository
 * @mixin CollectTotalItemStatTrait
 */
interface VideoAdminRepositoryInterface extends HasSponsor, HasSponsorInFeed
{
    /**
     * View videos.
     *
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    public function viewVideos(User $context, array $attributes): Builder;

    /**
     * @param ContractUser $context
     * @param int          $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param ContractUser $context
     * @param int          $id
     * @return bool
     */
    public function deleteVideo(ContractUser $context, int $id): bool;

    /**
     * Check video existence and update validity status
     *
     * @param ContractUser|null $context
     * @param Model             $video The video model to check
     * @return void Returns true if video is valid, false otherwise
     */
    public function checkVideoExistence(?ContractUser $context, Model $video): void;

    /**
     * @param ContractUser|null $context
     * @return void
     */
    public function sendMailDoneVerifyExistence(?ContractUser $context): void;

    /**
     * @param array         $videoIds
     * @param VerifyProcess $process
     * @return void
     */
    public function handleSpecificVerification(array $videoIds, VerifyProcess $process): void;
}
