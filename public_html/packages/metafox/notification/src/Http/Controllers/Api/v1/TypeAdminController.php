<?php

namespace MetaFox\Notification\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Notification\Contracts\TypeManager;
use MetaFox\Notification\Http\Requests\v1\Type\Admin\ToggleChannelRequest;
use MetaFox\Notification\Http\Requests\v1\Type\Admin\UpdateRequest;
use MetaFox\Notification\Http\Requests\v1\Type\Admin\IndexRequest;
use MetaFox\Notification\Http\Resources\v1\Type\Admin\TypeDetail as Detail;
use MetaFox\Notification\Http\Resources\v1\Type\Admin\TypeItemCollection as ItemCollection;
use MetaFox\Notification\Http\Resources\v1\Type\Admin\UpdateTypeForm;
use MetaFox\Notification\Models\Type;
use MetaFox\Notification\Repositories\TypeChannelAdminRepositoryInterface;
use MetaFox\Notification\Repositories\TypeRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;
use Throwable;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Notification\Http\Controllers\Api\TypeAdminController::$controllers.
 */

/**
 * Class TypeAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group admincp/notification
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TypeAdminController extends ApiController
{
    public function __construct(
        private TypeRepositoryInterface $repository,
        private TypeChannelAdminRepositoryInterface $channelAdminRepository,
        private TypeManager $typeManager,
    ) {
    }

    /**
     * Browse all type.
     *
     * @return mixed
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();

        $data = $this->repository->viewTypes($params);

        return new ItemCollection($data);
    }

    /**
     * View type.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update type.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return Detail
     * @throws ValidatorException
     * @throws AuthenticationException
     * @throws Throwable
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateType(user(), $id, $params);

        return $this->success(new Detail($data), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Delete type.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->success([
            'id' => $id,
        ]);
    }

    /**
     * View edit form.
     *
     * @param int $id
     *
     * @return JsonResource
     */
    public function edit(int $id): JsonResource
    {
        $resource = $this->repository->find($id);

        return new UpdateTypeForm($resource);
    }

    public function channel(ToggleChannelRequest $request): JsonResponse
    {
        /** @var Type $type */
        $type    = $this->repository->find($request->validated('id'));
        $channel = $request->validated('channel');
        $active  = $request->validated('active', 0);

        $module = $this->typeManager->makeModule($type->module_id, $channel);
        $this->channelAdminRepository->toggleChannelForType($type, $channel, $active);

        if ($module) {
            // only reset cache if a new module is created, otherwise it's not necessary
            Artisan::call('cache:reset');
        }

        return $this->success(new Detail($type), [], __p('core::phrase.updated_successfully'));
    }
}
