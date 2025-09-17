<?php

namespace MetaFox\Translation\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\Translation\Http\Requests\v1\TranslationGateway\IndexRequest;
use MetaFox\Translation\Http\Resources\v1\TranslationGateway\Admin\GatewayForm;
use MetaFox\Translation\Http\Resources\v1\TranslationGateway\TranslationGatewayDetail;
use MetaFox\Translation\Http\Resources\v1\TranslationGateway\TranslationGatewayItemCollection;
use MetaFox\Translation\Repositories\TranslationGatewayRepositoryInterface;
use MetaFox\Translation\Support\Facades\Translation;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Translation\Http\Controllers\Api\TranslationGatewayAdminController::$controllers.
 */
class TranslationGatewayAdminController extends ApiController
{
    /**
     * @var TranslationGatewayRepositoryInterface
     */
    private TranslationGatewayRepositoryInterface $repository;

    /**
     * @param TranslationGatewayRepositoryInterface $repository
     */
    public function __construct(TranslationGatewayRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @param IndexRequest $request
     * @return TranslationGatewayItemCollection
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data = $this->repository->viewTranslationGateways(user(), $params);

        return new TranslationGatewayItemCollection($data);
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
        $form = Translation::getGatewayAdminFormById($id);
        if (!$form instanceof GatewayForm) {
            return $this->error();
        }

        return $this->success($form->toArray($request));
    }

    /**
     * Update Gateway`.
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
        $form = Translation::getGatewayAdminFormById($id);

        if (!$form instanceof GatewayForm) {
            return $this->error();
        }

        $params = $form->validated($request);
        $params['config'] = Arr::except($params, [
            'title',
            'description',
            'is_active',
        ]);

        $data = $this->repository->updateTranslationGateway(user(), $id, $params);

        return $this->success(new TranslationGatewayDetail($data), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Active translation.
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
            'is_active' => (int)$params['active'],
        ], [], __p('core::phrase.already_saved_changes'));
    }
}
