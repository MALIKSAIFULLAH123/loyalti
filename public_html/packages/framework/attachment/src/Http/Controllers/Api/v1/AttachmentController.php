<?php

namespace MetaFox\Attachment\Http\Controllers\Api\v1;

use Facebook\Exception\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Attachment\Http\Requests\v1\Attachment\StoreRequest;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Attachment\Http\Resources\v1\Attachment\AttachmentItem;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Platform\MetaFoxConstant;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Attachment\Http\Controllers\Api\AttachmentController::$controllers;.
 */

/**
 * Class AttachmentController.
 * @codeCoverageIgnore
 * @ignore
 */
class AttachmentController extends ApiController
{
    /**
     * @var AttachmentRepositoryInterface
     */
    private AttachmentRepositoryInterface $repository;

    /**
     * AttachmentController Constructor.
     *
     * @param AttachmentRepositoryInterface $repository
     */
    public function __construct(AttachmentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Upload attachment.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group upload
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();

        // Get files from request.
        $file = $params['file'];

        // Get item types.
        $itemType = $params['item_type'];

        $errorMessage = app('events')->dispatch('attachment.verify_file_type', [$context, $file, $params], true);

        if (is_string($errorMessage)) {
            return $this->error($errorMessage, 422);
        }

        // Get upload type: public, s3.
        $uploadType = $params['upload_type'] ?? null;

        if (null === $uploadType) {
            $uploadType = Arr::get($params, 'storage_id');
        }

        // Upload files.
        $attachment = upload()
            ->setStorage($uploadType)
            ->setThumbSizes(ResizeImage::SIZE)
            ->setItemType($itemType)
            ->setUser($context)
            ->storeAttachment($file);

        return $this->success(new AttachmentItem($attachment));
    }

    /**
     * Allow downloading resource.
     *
     * @param int $id
     *
     * @return BinaryFileResponse
     */
    public function download(int $id): BinaryFileResponse
    {
        //TODO: Who can download an attachment??
        $attachment = $this->repository->find($id);

        $downloadUrl  = app('storage')->getAs($attachment->file_id);
        $originalName = $attachment->file?->original_name ?? 'attachmentFile';

        $cleanedName = preg_replace(['/[\s"]+/'], '_', $originalName);
        $cleanedName = trim($cleanedName, '_');

        return response()->download($downloadUrl, basename($cleanedName))
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $cleanedName)
            ->deleteFileAfterSend();
    }
}
