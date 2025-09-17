<?php

namespace MetaFox\Storage\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Storage\Http\Requests\v1\Disk\Admin\StoreRequest;
use MetaFox\Storage\Http\Requests\v1\Disk\Admin\UpdateRequest;
use MetaFox\Storage\Http\Resources\v1\Disk\Admin\DiskItem;
use MetaFox\Storage\Http\Resources\v1\Disk\Admin\DiskItemCollection as ItemCollection;
use MetaFox\Storage\Http\Resources\v1\Disk\Admin\StoreDiskForm;
use MetaFox\Storage\Http\Resources\v1\Disk\Admin\UpdateDiskForm;
use MetaFox\Storage\Models\Disk;
use MetaFox\Storage\Repositories\DiskRepositoryInterface;
use MetaFox\Storage\Support\StorageDiskValidator;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Storage\Http\Controllers\Api\DiskAdminController::$controllers.
 */

/**
 * Class DiskAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class DiskAdminController extends ApiController
{
    private DiskRepositoryInterface $diskRepository;

    /**
     * @param DiskRepositoryInterface $diskRepository
     */
    public function __construct(DiskRepositoryInterface $diskRepository)
    {
        $this->diskRepository = $diskRepository;
    }

    /**
     * Browse item.
     *
     * @return mixed
     */
    public function index(): JsonResponse
    {
        $result = $this->diskRepository->get();

        return $this->success(new ItemCollection($result));
    }

    /**
     * Delete item.
     *
     * @param int $disk
     *
     * @return JsonResponse
     */
    public function destroy(int $disk): JsonResponse
    {
        $this->diskRepository->delete($disk);

        // try to destroy disk
        return $this->success([
            'id' => $disk,
        ]);
    }

    public function create(): JsonResponse
    {
        $form = new StoreDiskForm();

        return $this->success($form);
    }

    public function edit(mixed $disk): JsonResponse
    {
        $item = $this->diskRepository->find($disk);

        $form = new UpdateDiskForm($item);

        return $this->success($form);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $disk = $this->diskRepository->create($data);

        return $this->success(new DiskItem($disk));
    }

    public function update(int $disk, UpdateRequest $request): JsonResponse
    {
        /** @var Disk $item */
        $item   = $this->diskRepository->find($disk);
        $data   = $request->validated();
        $target = Arr::get($data, 'target', '');

        try {
            StorageDiskValidator::isValid(config(sprintf('filesystems.disks.%s', $target)));
        } catch (\Throwable) {
            //Silent the errors and just return.
            return $this->error(__p('storage::phrase.invalid_configuration'));
        }

        $item->fill($data);

        $item->save();

        Artisan::call('cache:reset');

        return $this->success(new DiskItem($item), [], __p('core::phrase.updated_successfully'));
    }
}
