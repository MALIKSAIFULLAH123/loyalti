<?php

namespace MetaFox\User\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use MetaFox\Platform\Contracts\User as UserContracts;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Http\Resources\v1\User\Admin\UserExport;
use MetaFox\User\Jobs\ExportUserProcessingJob;
use MetaFox\User\Models\ExportProcess;
use MetaFox\User\Notifications\DoneExportProcessNotification;
use MetaFox\User\Repositories\ExportProcessRepositoryInterface;
use MetaFox\User\Repositories\UserAdminRepositoryInterface;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\User as UserSupport;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class ExportProcessRepository
 *
 * @method   ExportProcess find($id, $columns = ['*'])
 * @property ExportProcess $model
 * @method   ExportProcess getModel()
 */
class ExportProcessRepository extends AbstractRepository implements ExportProcessRepositoryInterface
{
    public function model()
    {
        return ExportProcess::class;
    }

    protected function userAdminRepository(): UserAdminRepositoryInterface
    {
        return resolve(UserAdminRepositoryInterface::class);
    }

    public function viewExportHistories(UserContracts $context, array $attributes): Builder
    {
        $search   = Arr::get($attributes, 'q');
        $status   = Arr::get($attributes, 'status');
        $id       = Arr::get($attributes, 'process_id');
        $sort     = Arr::get($attributes, 'sort', 'created_at');
        $sortType = Arr::get($attributes, 'sort_type', Browse::SORT_TYPE_DESC);
        $query    = $this->getModel()->newQuery();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where('filename', $this->likeOperator(), '%' . $search . '%');
        }

        if ($id) {
            $query->where('id', $id);
        }

        if ($sort) {
            $query->orderBy($sort, $sortType);
        }

        return $query;
    }

    /**
     * Export to csv file.
     *
     * @param ExportProcess $exportProcess
     * @return void
     * @throws AuthenticationException
     */
    public function exportCSV(ExportProcess $exportProcess): void
    {
        $exportProcess->updateQuietly([
            'status' => UserSupport::EXPORT_STATUS_PROCESSING,
        ]);

        $user              = $exportProcess->user;
        $path              = $exportProcess->path;
        $filters           = $exportProcess->filters;
        $properties        = $exportProcess->properties;
        $allowedProperties = UserFacade::allowedPropertiesExport($user);
        $properties        = Arr::only($allowedProperties, $properties);
        $stream            = fopen($path, 'w');

        fputcsv($stream, array_values($properties));

        $properties = array_keys($properties);

        $query = Arr::has($filters, 'ids')
            ? $this->userAdminRepository()->getModel()->newQuery()->whereIn('id', $filters['ids'])
            : $this->userAdminRepository()->buildQueryViewUsers($filters);

        $limit  = 500;
        $offset = 0;

        do {
            $rows = $query->select(['users.*'])->limit($limit)->offset($offset)->cursor();

            foreach ($rows as $row) {
                $row       = (new UserExport($row))->setContext($user)->toArray(request());
                $rowValues = [];

                foreach ($properties as $key => $value) {
                    $rowValues[] = Arr::get($row, $value);
                }

                fputcsv($stream, array_values($rowValues));
            }

            $offset += $limit;
            $total  = $rows->count();

            $exportProcess->updateQuietly(['total_user' => $total + $exportProcess->total_user]);
        } while ($total > 0);

        $this->doneExportProcess($exportProcess);
    }

    /**
     * @inheritDoc
     */
    public function createExportProcess(UserContracts $context, array $attributes): void
    {
        $time     = Carbon::now()->format('Y-m-d-H-i-s');
        $filename = "export-{$time}.csv";
        $path     = '/user/export/' . $filename;

        if (!is_dir(dirname($path))) {
            @mkdir(dirname($path), 0755, true);
        }

        $path = tempnam(sys_get_temp_dir(), $path);

        Arr::set($attributes, 'user_id', $context->entityId());
        Arr::set($attributes, 'user_type', $context->entityType());
        Arr::set($attributes, 'path', $path);
        Arr::set($attributes, 'filename', $filename);

        $model = $this->getModel()->fill($attributes);
        $model->save();

        ExportUserProcessingJob::dispatch($model->entityId());
    }

    /**
     * @inheritDoc
     */
    public function doneExportProcess(ExportProcess $exportProcess): void
    {
        $disk = Storage::disk(ExportProcess::STORAGE_SERVICE);
        $path = $disk->putFileAs('export-user', $exportProcess->path, $exportProcess->filename);

        $exportProcess->updateQuietly([
            'status' => UserSupport::EXPORT_STATUS_COMPLETED,
            'path'   => DIRECTORY_SEPARATOR . $path,
        ]);

        // handle send notification
        Notification::send($exportProcess->user, new DoneExportProcessNotification($exportProcess));
    }

    public function deleteExportProcess(ExportProcess $exportProcess): void
    {
        $disk = Storage::disk(ExportProcess::STORAGE_SERVICE);

        if ($disk->exists($exportProcess->path)) {
            $disk->delete($exportProcess->path);
        }

        $exportProcess->delete();
    }
}
