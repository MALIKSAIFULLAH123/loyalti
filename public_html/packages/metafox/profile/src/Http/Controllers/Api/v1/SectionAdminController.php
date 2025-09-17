<?php

namespace MetaFox\Profile\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Profile\Http\Requests\v1\Section\Admin\DeleteRequest;
use MetaFox\Profile\Http\Requests\v1\Section\Admin\IndexRequest;
use MetaFox\Profile\Http\Requests\v1\Section\Admin\StoreRequest;
use MetaFox\Profile\Http\Requests\v1\Section\Admin\UpdateRequest;
use MetaFox\Profile\Http\Resources\v1\Section\Admin\CreateSectionForm;
use MetaFox\Profile\Http\Resources\v1\Section\Admin\DestroySectionForm;
use MetaFox\Profile\Http\Resources\v1\Section\Admin\EditSectionForm;
use MetaFox\Profile\Http\Resources\v1\Section\Admin\SectionDetail as Detail;
use MetaFox\Profile\Http\Resources\v1\Section\Admin\SectionItemCollection as ItemCollection;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Profile\Http\Controllers\Api\SectionAdminController::$controllers;.
 */

/**
 * Class SectionAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class SectionAdminController extends ApiController
{
    /**
     * @var SectionRepositoryInterface
     */
    private SectionRepositoryInterface $repository;

    /**
     * SectionAdminController Constructor.
     *
     * @param SectionRepositoryInterface $repository
     */
    public function __construct(SectionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewSections(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createSection($params);

        Artisan::call('cache:reset');

        $this->navigate($data->admin_browse_url);

        $message = __p('profile::phrase.custom_group_has_been_created_successfully');

        return $this->success(new Detail($data), [], $message);
    }

    /**
     * View item.
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
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->update($params, $id);

        Artisan::call('cache:reset');

        $this->navigate($data->admin_browse_url);

        $message = __p('profile::phrase.custom_group_has_been_updated_successfully');

        return $this->success(new Detail($data), [], $message);
    }

    /**
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        $form = new CreateSectionForm();

        return $this->success($form);
    }

    /**
     * @param               $id
     * @return JsonResponse
     */
    public function edit($id): JsonResponse
    {
        $item = $this->repository->find($id);

        $form = new EditSectionForm($item);

        return $this->success($form);
    }

    /**
     * Delete item.
     *
     * @param DeleteRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(DeleteRequest $request, int $id): JsonResponse
    {
        $params               = $request->validated();
        $params['section_id'] = $id;
        $result               = $this->repository->deleteOrMoveToNewSection(user(), $params);

        Artisan::call('cache:reset');

        $message = __p('profile::phrase.custom_group_has_been_deleted_successfully');

        return $this->success(['id' => $id], [], $message);
    }

    public function delete(int $id): JsonResponse
    {
        $section = $this->repository->find($id);
        $form    = new DestroySectionForm($section);

        app()->call([$form, 'boot'], ['id' => $id]);

        return $this->success($form);
    }

    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids');

        $this->repository->orderSections($orderIds);

        return $this->success([], [], __p('profile::phrase.sections_successfully_ordered'));
    }

    /**
     * Update active status.
     *
     * @param Request $request
     * @param int     $id
     * @return JsonResponse
     */
    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $params = ['is_active' => $request->get('active')];
        $item   = $this->repository->toggleActive($id, $params);

        return $this->success(new Detail($item), [], __p('core::phrase.already_saved_changes'));
    }

}
