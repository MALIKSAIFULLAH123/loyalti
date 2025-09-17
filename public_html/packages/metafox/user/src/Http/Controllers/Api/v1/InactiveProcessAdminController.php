<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Http\Requests\v1\InactiveProcess\Admin\BatchProcessMailingRequest;
use MetaFox\User\Http\Requests\v1\InactiveProcess\Admin\IndexRequest;
use MetaFox\User\Http\Requests\v1\InactiveProcess\Admin\StoreRequest;
use MetaFox\User\Http\Resources\v1\InactiveProcess\Admin\CreateInactiveProcessForm;
use MetaFox\User\Http\Resources\v1\InactiveProcess\Admin\InactiveProcessDetail as Detail;
use MetaFox\User\Http\Resources\v1\InactiveProcess\Admin\InactiveProcessItemCollection as ItemCollection;
use MetaFox\User\Repositories\InactiveProcessAdminRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\User\Http\Controllers\Api\InactiveProcessAdminController::$controllers;
 */

/**
 * Class InactiveProcessAdminController
 *
 * @codeCoverageIgnore
 * @ignore
 */
class InactiveProcessAdminController extends ApiController
{
    /**
     * @var InactiveProcessAdminRepositoryInterface
     */
    private InactiveProcessAdminRepositoryInterface $repository;

    /**
     * InactiveProcessAdminController Constructor
     *
     * @param InactiveProcessAdminRepositoryInterface $repository
     */
    public function __construct(InactiveProcessAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param IndexRequest $request
     *
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewInactiveProcess($params)->paginate($params['limit'] ?? 100);

        return new ItemCollection($data);
    }

    /**
     * Store item
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createInactiveProcess(user(), $params);
        $this->navigate('user/inactive-process/browse');

        return $this->success([], [], __p('user::phrase.user_was_processed_mailing_job_successfully'));
    }

    /**
     * Store item
     *
     * @param BatchProcessMailingRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function batchProcessMailing(BatchProcessMailingRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createInactiveProcess(user(), $params);
        $this->navigate('user/inactive-process/browse');

        return $this->success([], [], __p('user::phrase.user_was_processed_mailing_job_successfully'));
    }

    public function processMailing(int $id): JsonResponse
    {
        $data = $this->repository->createInactiveProcess(user(), ['owner_ids' => [$id]]);
        $this->navigate('user/inactive-process/browse');

        return $this->success([], [], __p('user::phrase.inactive_send_successfully'));
    }

    public function startProcess(int $id): JsonResponse
    {
        $model = $this->repository->find($id);
        $this->repository->startInactiveProcess($model);

        return $this->success([], [], __p('user::phrase.inactive_processed_successfully'));
    }

    public function resend(int $id): JsonResponse
    {
        $model = $this->repository->find($id);

        $this->repository->resend($model);

        return $this->success(new Detail($model->refresh()), [], __p('user::phrase.inactive_resend_successfully'));
    }

    public function stop(int $id): JsonResponse
    {
        $model = $this->repository->find($id);

        $this->repository->stopProcess($model);

        return $this->success(new Detail($model->refresh()), [], __p('user::phrase.inactive_stopped_successfully'));
    }

    public function create(Request $request): CreateInactiveProcessForm
    {
        $form = new CreateInactiveProcessForm();

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }
        return $form;
    }
}
