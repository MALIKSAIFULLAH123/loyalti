<?php

namespace MetaFox\LiveStreaming\Repositories;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface UserStreamKey.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface UserStreamKeyRepositoryInterface
{
    /**
     * @param  ContractUser $context
     * @return mixed
     */
    public function getUserStreamKey(ContractUser $context): mixed;

    /**
     * @param  ContractUser          $user
     * @param  VideoServiceInterface $serviceDriver
     * @return mixed
     */
    public function createUserStreamKey(ContractUser $user, VideoServiceInterface $serviceDriver): mixed;

    /**
     * @param  string    $streamKey
     * @param  string    $type
     * @param  LiveVideo $liveVideo
     * @return mixed
     */
    public function updateUserStreamKey(string $streamKey, string $type, LiveVideo $liveVideo): mixed;
}
