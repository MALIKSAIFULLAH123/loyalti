<?php

namespace MetaFox\Music\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Music\Http\Requests\v1\Genre\Admin\DeleteRequest;
use MetaFox\Music\Http\Requests\v1\Genre\Admin\IndexRequest;
use MetaFox\Music\Http\Requests\v1\Genre\Admin\StoreRequest;
use MetaFox\Music\Http\Requests\v1\Genre\Admin\UpdateRequest;
use MetaFox\Music\Http\Resources\v1\Genre\Admin\DestroyGenreForm;
use MetaFox\Music\Http\Resources\v1\Genre\Admin\GenreItem as Detail;
use MetaFox\Music\Http\Resources\v1\Genre\Admin\GenreItemCollection as ItemCollection;
use MetaFox\Music\Http\Resources\v1\Genre\Admin\StoreGenreForm;
use MetaFox\Music\Models\Genre;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Traits\Http\Controllers\OrderCategoryTrait;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class GenreAdminController.
 *
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 */
class GenreAdminController extends ApiController
{
    use OrderCategoryTrait;

    /**
     * @var GenreRepositoryInterface
     */
    public GenreRepositoryInterface $repository;

    /**
     * @param GenreRepositoryInterface $repository
     */
    public function __construct(GenreRepositoryInterface $repository)
    {
        $this->repository          = $repository;
        $this->orderSuccessMessage = 'music::phrase.genres_successfully_ordered';
    }

    /**
     * Browse genre.
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

        $data = $this->repository->viewForAdmin(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Create genre.
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
        $data   = $this->repository->createCategory(user(), $params);

        $this->navigate($data->admin_browse_url, true);

        Artisan::call('cache:reset');
        return $this->success(new Detail($data), [], __p('core::phrase.resource_create_success', [
            'resource_name' => __p('music::phrase.genre'),
        ]));
    }

    /**
     * View genre.
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
     * Update genre.
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
        $data   = $this->repository->updateCategory(user(), $id, $params);
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
        $genre = $this->repository->find($id);

        if ($genre->is_default) {
            abort(403, __p('music::phrase.cant_delete_default_genre'));
        }

        $params        = $request->validated();
        $newCategoryId = $params['new_category_id'];
        $this->repository->deleteCategory(user(), $id, $newCategoryId);

        return $this->success([], [], __p('music::phrase.deleted_the_genre_successfully'));
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
     * @return StoreGenreForm
     */
    public function create(): StoreGenreForm
    {
        return new StoreGenreForm();
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

        $form = new DestroyGenreForm($item);

        app()->call([$form, 'boot'], ['id' => $id]);

        return $this->success($form);
    }

    public function toggleDefault(int $id): JsonResponse
    {
        /**
         * @var Genre $item
         */
        $item = $this->repository->find($id);

        Settings::save(['music.music_song.song_default_genre' => $item->entityId()]);

        Artisan::call('cache:reset');

        return $this->success([], [], __p('music::phrase.genre_successfully_marked_as_default'));
    }
}
