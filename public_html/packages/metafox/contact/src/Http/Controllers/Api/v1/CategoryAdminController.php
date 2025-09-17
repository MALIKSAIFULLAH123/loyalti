<?php

namespace MetaFox\Contact\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Contact\Http\Requests\v1\Category\Admin\DeleteRequest;
use MetaFox\Contact\Http\Requests\v1\Category\Admin\IndexRequest;
use MetaFox\Contact\Http\Requests\v1\Category\Admin\StoreRequest;
use MetaFox\Contact\Http\Requests\v1\Category\Admin\UpdateRequest;
use MetaFox\Contact\Http\Resources\v1\Category\Admin\CategoryItem as Detail;
use MetaFox\Contact\Http\Resources\v1\Category\Admin\CategoryItemCollection as ItemCollection;
use MetaFox\Contact\Http\Resources\v1\Category\Admin\DestroyCategoryForm;
use MetaFox\Contact\Http\Resources\v1\Category\Admin\StoreCategoryForm;
use MetaFox\Contact\Http\Resources\v1\Category\Admin\UpdateCategoryForm;
use MetaFox\Contact\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Traits\Http\Controllers\OrderCategoryTrait;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Http/Controllers/Api/v1/CategoryAdminController.stub.
 */

/**
 * Class CategoryAdminController.
 *
 * @ignore
 * @codeCoverageIgnore
 * @group admincp/contact
 * @authenticated
 */
class CategoryAdminController extends ApiController
{
    use OrderCategoryTrait;

    /**
     * @var CategoryRepositoryInterface
     */
    public CategoryRepositoryInterface $repository;

    /**
     * @param CategoryRepositoryInterface $repository
     */
    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse category.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewForAdmin(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Create category.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createCategory(user(), $params);
        $this->navigate($data->admin_browse_url, true);

        Artisan::call('cache:reset');
        return $this->success(new Detail($data), [], __p('core::phrase.resource_create_success', [
            'resource_name' => __p('core::phrase.category'),
        ]));
    }

    /**
     * Update category.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return Detail
     * @throws AuthorizationException|AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): Detail
    {
        $params = $request->validated();
        $data   = $this->repository->updateCategory(user(), $id, $params);
        $this->navigate($data->admin_browse_url, true);

        Artisan::call('cache:reset');
        return new Detail($data);
    }

    /**
     * Delete category.
     *
     * @param DeleteRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function destroy(DeleteRequest $request, int $id): JsonResponse
    {
        $params        = $request->validated();
        $newCategoryId = $params['new_category_id'];

        $this->repository->deleteCategory(user(), $id, $newCategoryId);

        return $this->success([], [], __p('core::phrase.deleted_the_category_successfully'));
    }

    /**
     * Update active status.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function toggleActive(int $id): JsonResponse
    {
        $item = $this->repository->toggleActive($id);

        return $this->success([new Detail($item)], [], __p('core::phrase.already_saved_changes'));
    }

    /**
     * View creation form.
     *
     * @return StoreCategoryForm
     */
    public function create(): StoreCategoryForm
    {
        return new StoreCategoryForm();
    }

    /**
     * View editing form.
     *
     * @param int $id
     *
     * @return UpdateCategoryForm
     */
    public function edit(int $id): UpdateCategoryForm
    {
        $item = $this->repository->find($id);

        return new UpdateCategoryForm($item);
    }

    public function default(int $id): JsonResponse
    {
        $item = $this->repository->find($id);
        $data = [
            'contact.default_category' => $id,
        ];
        Settings::save($data);

        Artisan::call('cache:reset');

        return $this->success([new Detail($item)], [], __p('core::phrase.updated_successfully'));
    }

    /**
     * View deleting form.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        $form = new DestroyCategoryForm($item);

        app()->call([$form, 'boot'], ['id' => $id]);

        return $this->success($form);
    }
}
