<?php

namespace MetaFox\Photo\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Photo\Http\Requests\v1\Category\Admin\DeleteRequest;
use MetaFox\Photo\Http\Requests\v1\Category\Admin\IndexRequest;
use MetaFox\Photo\Http\Requests\v1\Category\Admin\StoreRequest;
use MetaFox\Photo\Http\Requests\v1\Category\Admin\UpdateRequest;
use MetaFox\Photo\Http\Resources\v1\Category\Admin\CategoryItem as Detail;
use MetaFox\Photo\Http\Resources\v1\Category\Admin\CategoryItemCollection as ItemCollection;
use MetaFox\Photo\Http\Resources\v1\Category\Admin\DestroyCategoryForm;
use MetaFox\Photo\Http\Resources\v1\Category\Admin\StoreCategoryForm;
use MetaFox\Photo\Models\Category;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Traits\Http\Controllers\OrderCategoryTrait;
use Prettus\Validator\Exceptions\ValidatorException;

class CategoryAdminController extends ApiController
{
    use OrderCategoryTrait;

    public CategoryRepositoryInterface $repository;

    /**
     * @param CategoryRepositoryInterface $repository
     */
    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): JsonResource
    {
        $params = $request->validated();
        $data   = $this->repository->viewForAdmin(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        /** @var Category $data */
        $data = $this->repository->createCategory(user(), $request->validated());

        $this->navigate($data->admin_browse_url, true);

        Artisan::call('cache:reset');
        return $this->success(new Detail($data), [], __p('core::phrase.resource_create_success', [
            'resource_name' => __p('core::phrase.category'),
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function show(int $id): Detail
    {
        $category = $this->repository->viewCategory(user(), $id);

        return new Detail($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        /** @var Category $category */
        $category = $this->repository->updateCategory(user(), $id, $request->validated());

        $this->navigate($category->admin_browse_url, true);

        Artisan::call('cache:reset');
        return $this->success(new Detail($category), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function destroy(DeleteRequest $request, int $id): JsonResponse
    {
        $this->repository->deleteCategory(user(), $id, $request->validated());

        return $this->success([
            'id' => $id,
        ], [], __p('core::phrase.deleted_the_category_successfully'));
    }

    /**
     * View creation form.
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        $form = new StoreCategoryForm();

        return $this->success($form);
    }

    /**
     * View creation form.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $form = new DestroyCategoryForm();

        app()->call([$form, 'boot'], ['id' => $id]);

        return $this->success($form);
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

    public function default(int $id): JsonResponse
    {
        $item = $this->repository->find($id);
        $data = [
            'photo.default_category' => $id,
        ];
        Settings::save($data);

        Artisan::call('cache:reset');

        return $this->success([new Detail($item)], [], __p('core::phrase.updated_successfully'));
    }
}
