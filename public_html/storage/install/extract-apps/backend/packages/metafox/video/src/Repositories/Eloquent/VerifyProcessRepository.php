<?php

namespace MetaFox\Video\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Video\Models\VerifyProcess;
use MetaFox\Video\Repositories\VerifyProcessRepositoryInterface;
use MetaFox\Video\Support\VideoSupport;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class VerifyProcessRepository
 *
 */
class VerifyProcessRepository extends AbstractRepository implements VerifyProcessRepositoryInterface
{
    public function model()
    {
        return VerifyProcess::class;
    }

    public function createProcess(User $user, array $attributes): VerifyProcess
    {
        $extra = Arr::except($attributes, $this->getModel()->getFillable());
        $data  = Arr::only($attributes, $this->getModel()->getFillable());

        $data = array_merge($data, [
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'extra'     => json_encode($extra),
        ]);

        $model = $this->getModel()->fill($data);
        $model->save();

        return $model;
    }

    public function pickProcess(): ?VerifyProcess
    {
        return $this->getModel()->newQuery()->where('status', 'pending')->first();
    }

    public function updateProcess(VerifyProcess $process, array $attributes): VerifyProcess
    {
        $process->update($attributes);

        return $process;
    }

    /**
     * @inheritDoc
     */
    public function checkProcessExist(): bool
    {
        return $this->getModel()->newQuery()
            ->whereIn('status', [
                VideoSupport::PENDING_VERIFY_STATUS,
                VideoSupport::PROCESSING_VERIFY_STATUS,
            ])->exists();
    }

    public function viewProcesses(User $user, array $attributes): Builder
    {
        $query = $this->getModel()->newQuery();

        return $query->orderBy('created_at', 'desc');
    }

    public function process(VerifyProcess $process): VerifyProcess
    {
        $process->updateQuietly([
            'status' => VideoSupport::PENDING_VERIFY_STATUS,
        ]);

        return $process;
    }

    public function stopProcess(VerifyProcess $process): VerifyProcess
    {
        $process->updateQuietly([
            'status' => VideoSupport::STOPPED_VERIFY_STATUS,
        ]);

        return $process;
    }
}
