<?php

namespace MetaFox\LiveStreaming\Repositories;

use Exception;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\LiveStreaming\Models\NotificationSetting as Model;

/**
 * Interface NotificationSetting.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface NotificationSettingRepositoryInterface
{
    /**
     * @param  ContractUser|null $owner
     * @param  ContractUser      $user
     * @return bool
     * @throws Exception
     */
    public function isTurnOffNotify(?ContractUser $owner, ContractUser $user): bool;

    /**
     * @param  ContractUser $owner
     * @param  ContractUser $user
     * @return bool|Model
     * @throws Exception
     */
    public function disabledNotification(ContractUser $owner, ContractUser $user): bool|Model;

    /**
     * @param  ContractUser $owner
     * @param  ContractUser $user
     * @return bool
     * @throws Exception
     */
    public function enabledNotification(ContractUser $owner, ContractUser $user): bool;

    /**
     * @param  ContractUser $owner
     * @return array
     */
    public function getDisabledUserIds(ContractUser $owner): array;
}
