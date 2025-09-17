<?php

namespace MetaFox\Video\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Video\Models\VerifyProcess;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface VerifyProcess
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface VerifyProcessRepositoryInterface
{
    /**
     * @param User  $user
     * @param array $attributes
     * @return VerifyProcess
     */
    public function createProcess(User $user, array $attributes): VerifyProcess;

    /**
     * @return ?VerifyProcess
     */
    public function pickProcess(): ?VerifyProcess;

    /**
     * @param VerifyProcess $process
     * @param array         $attributes
     * @return VerifyProcess
     */
    public function updateProcess(VerifyProcess $process, array $attributes): VerifyProcess;

    /**
     * @return bool
     */
    public function checkProcessExist(): bool;

    /**
     * @param User  $user
     * @param array $attributes
     * @return Builder
     */
    public function viewProcesses(User $user, array $attributes): Builder;

    /**
     * @param VerifyProcess $process
     * @return VerifyProcess
     */
    public function process(VerifyProcess $process): VerifyProcess;

    /**
     * @param VerifyProcess $process
     * @return VerifyProcess
     */
    public function stopProcess(VerifyProcess $process): VerifyProcess;
}
