<?php

namespace MetaFox\User\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\User as UserContracts;
use MetaFox\User\Models\ExportProcess;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ExportProcess
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ExportProcessRepositoryInterface
{
    /**
     * @param UserContracts $context
     * @param array         $attributes
     * @return Builder
     */
    public function viewExportHistories(UserContracts $context, array $attributes): Builder;

    /**
     * @param ExportProcess $exportProcess
     * @return void
     */
    public function exportCSV(ExportProcess $exportProcess): void;

    /**
     * @param User  $context
     * @param array $attributes
     * @return void
     */
    public function createExportProcess(User $context, array $attributes): void;

    /**
     * @param ExportProcess $exportProcess
     * @return void
     */
    public function doneExportProcess(ExportProcess $exportProcess): void;

    /**
     * @param ExportProcess $exportProcess
     * @return void
     */
    public function deleteExportProcess(ExportProcess $exportProcess): void;
}
