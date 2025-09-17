<?php

namespace MetaFox\User\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\InactiveProcess;
use MetaFox\User\Models\InactiveProcessData;
use MetaFox\User\Models\User as ModelsUser;
use MetaFox\User\Notifications\ProcessMailingInactiveUser;
use MetaFox\User\Repositories\InactiveProcessAdminRepositoryInterface;

class InactiveProcessAdminRepository extends AbstractRepository implements InactiveProcessAdminRepositoryInterface
{
    public function model()
    {
        return InactiveProcess::class;
    }

    /**
     * @inheritDoc
     */
    public function createInactiveProcess(User $user, array $data): InactiveProcess
    {
        Arr::set($data, 'status', InactiveProcess::NOT_STARTED_STATUS);
        $ownerIds   = Arr::pull($data, 'owner_ids', []);
        $totalUsers = count($ownerIds);

        Arr::set($data, 'total_users', $totalUsers);
        Arr::set($data, 'user_id', $user->entityId());
        Arr::set($data, 'user_type', $user->entityType());

        if ($totalUsers < 5) {
            Arr::set($data, 'round', $totalUsers);
        }

        $model = new InactiveProcess();
        $model->fill($data);

        $model->save();

        match ($totalUsers) {
            1       => $this->handleProcessingUser($model, Arr::first($ownerIds)),
            default => $this->handleProcessingUsers($model, $ownerIds),
        };

        return $model;
    }

    private function handleProcessingUser(InactiveProcess $inactiveProcess, int $ownerId): void
    {
        $model = InactiveProcessData::query()
            ->newModelInstance()
            ->fill([
                'user_id'    => $ownerId,
                'status'     => InactiveProcess::COMPLETED_STATUS,
                'user_type'  => ModelsUser::ENTITY_TYPE,
                'process_id' => $inactiveProcess->entityId(),
            ]);

        $model->save();

        if (!$model instanceof InactiveProcessData) {
            return;
        }

        $inactiveProcess->updateQuietly([
            'total_sent'   => 1,
            'status'       => InactiveProcess::COMPLETED_STATUS,
            'last_sent_id' => $model->userId(),
        ]);

        Notification::send($model->user, new ProcessMailingInactiveUser($model->user));

    }

    private function handleProcessingUsers(InactiveProcess $model, array $ownerIds): void
    {
        $attributes = [];

        foreach ($ownerIds as $ownerId) {
            $attributes[] = [
                'user_id'    => $ownerId,
                'status'     => InactiveProcess::PENDING_STATUS,
                'user_type'  => ModelsUser::ENTITY_TYPE,
                'process_id' => $model->entityId(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        InactiveProcessData::query()->insert($attributes);
    }

    public function viewInactiveProcess(array $attributes): Builder
    {
        $query = $this->getModel()->newQuery();

        return $query->orderBy('created_at', 'desc');
    }

    public function pickStartInactiveProcess(): ?InactiveProcess
    {
        return $this->getModel()->newQuery()->where('status', InactiveProcess::PENDING_STATUS)->first();
    }

    public function startInactiveProcess(InactiveProcess $inactiveProcess): InactiveProcess
    {
        $oldStatus = $inactiveProcess->status;
        $inactiveProcess->updateQuietly([
            'status' => InactiveProcess::PENDING_STATUS,
        ]);
        if ($oldStatus === InactiveProcess::STOPPED_STATUS) {
            $inactiveProcess->stoppedProcess()
                ->update([
                    'status' => InactiveProcess::PENDING_STATUS,
                ]);
        }

        return $inactiveProcess;
    }

    /**
     * @inheritDoc
     */
    public function resend(InactiveProcess $inactiveProcess): InactiveProcess
    {
        $inactiveProcess->updateQuietly([
            'status'     => InactiveProcess::PENDING_STATUS,
            'total_sent' => 0,
        ]);

        return $inactiveProcess;
    }

    public function stopProcess(InactiveProcess $inactiveProcess): InactiveProcess
    {
        $inactiveProcess->updateQuietly([
            'status' => InactiveProcess::STOPPED_STATUS,
        ]);

        $inactiveProcess->pendingProcess()->update([
            'status' => InactiveProcess::STOPPED_STATUS,
        ]);

        return $inactiveProcess;
    }
}
