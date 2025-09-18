<?php

namespace MetaFox\Marketplace\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Marketplace\Http\Requests\v1\Category\Admin\DeleteRequest;
use MetaFox\Marketplace\Http\Requests\v1\Category\Admin\IndexRequest;
use MetaFox\Marketplace\Http\Requests\v1\Category\Admin\StoreRequest;
use MetaFox\Marketplace\Http\Requests\v1\Category\Admin\UpdateRequest;
use MetaFox\Marketplace\Http\Resources\v1\Category\Admin\CategoryItem as Detail;
use MetaFox\Marketplace\Http\Resources\v1\Category\Admin\CategoryItemCollection as ItemCollection;
use MetaFox\Marketplace\Http\Resources\v1\Category\Admin\DestroyCategoryForm;
use MetaFox\Marketplace\Http\Resources\v1\Category\Admin\StoreCategoryForm;
use MetaFox\Marketplace\Models\Category;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Traits\Http\Controllers\OrderCategoryTrait;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class CategoryAdminController.
 *
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group admincp/marketplace
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
     * @return ItemCollection<Detail>
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->viewForAdmin($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Create category.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @throws AuthorizationException|AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        /** @var Category $data */
        $data = $this->repository->createCategory(user(), $params);

        $this->navigate($data->admin_browse_url, true);

        Artisan::call('cache:reset');
        return $this->success(new Detail($data), [], __p('core::phrase.resource_create_success', [
            'resource_name' => __p('core::phrase.category'),
        ]));
    }

    /**
     * View category.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthorizationException|AuthenticationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewCategory(user(), $id);

        return new Detail($data);
    }

    /**
     * Update category.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        /** @var Category $data */
        $data = $this->repository->updateCategory(user(), $id, $params);

        $this->navigate($data->admin_browse_url, true);
        Artisan::call('cache:reset');
        return $this->success(new Detail($data), [], __p('core::phrase.updated_successfully'));
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

        return $this->success([], [], __p('group::phrase.successfully_deleted_the_category'));
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

    public function default(int $id): JsonResponse
    {
        $item = $this->repository->find($id);
        $data = [
            'marketplace.default_category' => $id,
        ];
        Settings::save($data);

        Artisan::call('cache:reset');

        return $this->success([new Detail($item)], [], __p('core::phrase.updated_successfully'));
    }
}
