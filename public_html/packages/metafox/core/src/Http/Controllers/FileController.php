<?php

namespace MetaFox\Core\Http\Controllers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use MetaFox\Core\Http\Requests\FileApi\UploadFileMultipleRequest;
use MetaFox\Core\Http\Requests\FileApi\UploadFileRequest;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Storage\Http\Resources\v1\StorageFile\StorageFileCollection;
use MetaFox\Storage\Http\Resources\v1\StorageFile\StorageFileItem;

/**
 * Class FileController.
 * @group file
 * @authenticated
 * @ignore
 * @codeCoverageIgnore
 */
class FileController extends ApiController
{
    /**
     * Upload single.
     *
     * @param UploadFileRequest $request
     * @bodyParam file file
     * @bodyParam storage_id string
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group     upload
     * @authenticated
     */
    public function upload(UploadFileRequest $request): JsonResponse
    {
        $params = $request->validated();

        // Get files from request.
        $file = $params['file'];

        if (!$file instanceof UploadedFile) {
            return $this->error(__p('validation.file', ['attribute' => 'file']));
        }

        if (file_type()->isForbiddenFile($file)) {
            return $this->error(__p('validation.file_contains_invalid_content'));
        }

        // Upload file.
        try {
            $storageFile = upload()->setUser(user())->uploadWithParams($file, $params);

            return $this->success(new StorageFileItem($storageFile));
        } catch (\Throwable $error) {
            return $this->error($error->getMessage());
        }
    }

    /**
     * Upload multiple file.
     *
     * @param UploadFileMultipleRequest $request
     * @bodyParam file file[]
     * @bodyParam storage_id string
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group     upload
     * @authenticated
     */
    public function uploadMultiple(UploadFileMultipleRequest $request): JsonResponse
    {
        $params = $request->validated();

        // Get files from request.
        $files = $params['file'];

        if (empty($files) || !is_array($files)) {
            return $this->error('No file upload');
        }
        // Get item types.
        $itemType = $params['item_type'];

        // Get upload type: public, s3.
        $uploadType = $params['upload_type'] ?? null;

        if (null === $uploadType) {
            $uploadType = Arr::get($params, 'storage_id');
        }

        // Upload files.
        $storageFiles = upload()
            ->setStorage($uploadType)
            ->setThumbSizes($params['thumbnail_sizes'])
            ->setItemType($itemType)
            ->setUser(user())
            ->storeFiles($files);

        return $this->success(new StorageFileCollection($storageFiles));
    }
}
