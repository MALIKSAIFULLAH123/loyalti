<?php

namespace MetaFox\LiveStreaming\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface LiveVideo.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface LiveVideoAdminRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * @param ContractUser         $context
     * @param array<string, mixed> $attributes
     * @return Builder
     */
    public function viewLiveVideos(ContractUser $context, array $attributes): Builder;

    /**
     * @param int $id
     * @return string|null
     */
    public function getThumbnailPlayback(int $id): ?string;
}
