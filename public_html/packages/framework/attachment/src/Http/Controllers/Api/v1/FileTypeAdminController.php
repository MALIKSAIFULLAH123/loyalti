<?php

namespace MetaFox\Attachment\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Attachment\Http\Resources\v1\FileType\Admin\FileTypeItemCollection as ItemCollection;
use MetaFox\Attachment\Http\Resources\v1\FileType\Admin\FileTypeDetail as Detail;
use MetaFox\Core\Repositories\AttachmentFileTypeRepositoryInterface as FileTypeRepositoryInterface;
use MetaFox\Attachment\Http\Requests\v1\FileType\Admin\IndexRequest;
use MetaFox\Attachment\Http\Requests\v1\FileType\Admin\StoreRequest;
use MetaFox\Attachment\Http\Requests\v1\FileType\Admin\UpdateRequest;
use MetaFox\Attachment\Http\Resources\v1\FileType\Admin\EditFileTypeForm;
use MetaFox\Attachment\Http\Resources\v1\FileType\Admin\StoreFileTypeForm;
use MetaFox\Core\Support\Facades\AttachmentFileType;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Attachment\Http\Controllers\Api\FileTypeAdminController::$controllers;
 */

/**
 * Class FileTypeAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class FileTypeAdminController extends ApiController
{
    /**
     * @var FileTypeRepositoryInterface
     */
    private FileTypeRepositoryInterface $repository;

    /**
     * FileTypeAdminController Constructor.
     *
     * @param FileTypeRepositoryInterface $repository
     */
    public function __construct(FileTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->viewFileTypes($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->createFileType($context, $params);

        $this->navigate($data->admin_browse_url, true);

        return $this->success(new Detail($data), [], __p('attachment::phrase.type_created_successfully'));
    }

    /**
     * Get Store Form.
     *
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        return $this->success(resolve(StoreFileTypeForm::class));
    }

    /**
     * Get Edit Form.
     *
     * @return JsonResponse
     */
    public function edit(Request $request): JsonResponse
    {
        $form = resolve(EditFileTypeForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->updateFileType($context, $id, $params);

        return $this->success(new Detail($data), [], __p('attachment::phrase.type_updated_successfully'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteFileType(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('attachment::phrase.file_type_successfully_deleted'));
    }

    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $fileType = $this->repository->find($id);

        $active = $params['active'] ? true : false;

        $fileType->update(['is_active' => $active]);
        $fileType->refresh();

        Artisan::call('cache:reset');

        $message = match ($active) {
            true  => __p('attachment::phrase.file_type_successfully_activated'),
            false => __p('attachment::phrase.file_type_successfully_deactived')
        };

        return $this->success(new Detail($fileType), [], $message);
    }
}
