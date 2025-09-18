<?php

namespace MetaFox\Marketplace\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Marketplace\Http\Requests\v1\Image\UpdateRequest;
use MetaFox\Marketplace\Http\Resources\v1\Image\UploadImageForm;
use MetaFox\Marketplace\Repositories\ImageRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class ImageController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group marketplace
 */
class ImageController extends ApiController
{
    /**
     * @var ListingRepositoryInterface
     */
    public ListingRepositoryInterface $listingRepository;

    /**
     * @var ImageRepositoryInterface
     */
    public ImageRepositoryInterface  $imageRepository;

    public function __construct(ListingRepositoryInterface $listingRepository, ImageRepositoryInterface $imageRepository)
    {
        $this->listingRepository = $listingRepository;
        $this->imageRepository   = $imageRepository;
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function form(int $id): JsonResponse
    {
        $data = $this->listingRepository->find($id);

        return $this->success(new UploadImageForm($data), [], '');
    }

    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $this->imageRepository->updateImages(user(), $id, $params);

        return $this->success([], [], ''); //todo: fix response if need
    }
}
