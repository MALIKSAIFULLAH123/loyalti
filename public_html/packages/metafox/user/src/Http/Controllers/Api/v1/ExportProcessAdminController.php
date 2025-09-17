<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\User\Http\Requests\v1\ExportProcess\Admin\BatchRequest;
use MetaFox\User\Http\Requests\v1\ExportProcess\Admin\IndexRequest;
use MetaFox\User\Http\Requests\v1\ExportProcess\Admin\StoreRequest;
use MetaFox\User\Http\Resources\v1\ExportProcess\Admin\ExportProcessItemCollection as ItemCollection;
use MetaFox\User\Http\Resources\v1\ExportProcess\Admin\ExportUserForm;
use MetaFox\User\Models\ExportProcess;
use MetaFox\User\Repositories\ExportProcessRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\User\Http\Controllers\Api\ExportProcessAdminController::$controllers;
 */

/**
 * Class ExportProcessAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class ExportProcessAdminController extends ApiController
{
    /**
     * ExportProcessAdminController Constructor
     *
     * @param ExportProcessRepositoryInterface $repository
     */
    public function __construct(protected ExportProcessRepositoryInterface $repository) {}

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $limit  = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $data   = $this->repository->viewExportHistories(user(), $params)
            ->paginate($limit);

        return new ItemCollection($data);
    }

    /**
     * Store item
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        if (!Arr::has($params, 'properties')) {
            return $this->error(__('user::phrase.please_select_least_one_piece'));
        }

        $this->repository->createExportProcess(user(), $params);

        $this->navigate('/user/export-process/browse');
        return $this->success([], [], __('user::phrase.create_export_process_successfully'));
    }

    public function create(Request $request): ExportUserForm
    {
        $form = new ExportUserForm();

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $form;
    }

    public function download(int $id)
    {
        try {
            $model = $this->repository->getModel()->newQuery()->findOrFail($id);

            if (!$model instanceof ExportProcess) {
                throw new ModelNotFoundException();
            }

        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException();
        }

        $headers = ['Access-Control-Expose-Headers' => 'Content-Disposition'];

        return response()->download($model->download_url, $model->filename, $headers);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @group admin/user
     */
    public function destroy(int $id): JsonResponse
    {
        /**@var ExportProcess $model */
        $model = $this->repository->find($id);

        $this->repository->deleteExportProcess($model);

        return $this->success([], [], __p('user::phrase.file_deleted_successfully'));
    }

    public function batchDelete(BatchRequest $request): JsonResponse
    {
        $params = $request->validated();
        $ids    = Arr::get($params, 'id', []);

        foreach ($ids as $id) {
            $exportProcess = $this->repository->find($id);
            $this->repository->deleteExportProcess($exportProcess);
        }

        return $this->success([], [], __p('user::phrase.file_s_deleted_successfully'));
    }
}
