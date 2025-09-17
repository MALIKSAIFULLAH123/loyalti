<?php

namespace MetaFox\Payment\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Payment\Http\Requests\v1\Gateway\IndexRequest;
use MetaFox\Payment\Http\Requests\v1\Gateway\TestModeRequest;
use MetaFox\Payment\Http\Resources\v1\Gateway\Admin\GatewayForm;
use MetaFox\Payment\Http\Resources\v1\Gateway\GatewayDetail as Detail;
use MetaFox\Payment\Http\Resources\v1\Gateway\GatewayItemCollection as ItemCollection;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Payment\Http\Controllers\Api\GatewayAdminController::$controllers.
 */

/**
 * Class GatewayAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group payment
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ElseExpression)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class GatewayAdminController extends ApiController
{
    /**
     * @var GatewayRepositoryInterface
     */
    private GatewayRepositoryInterface $repository;

    /**
     * @param GatewayRepositoryInterface $repository
     */
    public function __construct(GatewayRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse payments.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->viewGateways(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * View payment.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewGateway(user(), $id);

        return new Detail($data);
    }

    /**
     * Update payment`.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $form = Payment::getGatewayAdminFormById($id);

        if (!$form instanceof GatewayForm) {
            return $this->error();
        }

        $params           = $form->validated($request);
        $params['config'] = Arr::except($params, [
            'title',
            'description',
            'is_test',
            'is_active',
        ]);

        $data = $this->repository->updateGateway(user(), $id, $params);

        return $this->success(new Detail($data), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Active payment.
     *
     * @param ActiveRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $this->repository->updateActive(user(), $id, $params['active']);

        return $this->success([
            'id'        => $id,
            'is_active' => (int) $params['active'],
        ], [], __p('core::phrase.already_saved_changes'));
    }

    /**
     * Active test mode.
     *
     * @param TestModeRequest $request
     * @param int             $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function testMode(TestModeRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $this->repository->updateTestMode(user(), $id, $params['test_mode']);

        return $this->success([
            'id'      => $id,
            'is_test' => (int) $params['test_mode'],
        ]);
    }

    /**
     * Get the gateway form.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     * @group admin/menu
     */
    public function edit(Request $request, int $id): JsonResponse
    {
        $form = Payment::getGatewayAdminFormById($id);
        if (!$form instanceof GatewayForm) {
            return $this->error();
        }

        return $this->success($form->toArray($request));
    }
}
