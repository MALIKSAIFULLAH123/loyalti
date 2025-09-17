<?php

namespace MetaFox\User\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\InactiveProcess;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface InactiveProcessAdminRepositoryInterface.
 *
 * @mixin BaseRepository
 */
interface InactiveProcessAdminRepositoryInterface
{
    /**
     * @param array $attributes
     *
     * @return Builder
     */
    public function viewInactiveProcess(array $attributes): Builder;

    /**
     * @param array $data
     *
     * @return InactiveProcess
     */
    public function createInactiveProcess(User $user, array $data): InactiveProcess;

    /**
     * @return InactiveProcess
     */
    public function pickStartInactiveProcess(): ?InactiveProcess;

    /**
     * @param InactiveProcess $inactiveProcess
     *
     * @return InactiveProcess
     */
    public function startInactiveProcess(InactiveProcess $inactiveProcess): InactiveProcess;

    /**
     * @param InactiveProcess $inactiveProcess
     *
     * @return InactiveProcess
     */
    public function stopProcess(InactiveProcess $inactiveProcess): InactiveProcess;

    /**
     * @param InactiveProcess $inactiveProcess
     *
     * @return InactiveProcess
     */
    public function resend(InactiveProcess $inactiveProcess): InactiveProcess;
}
