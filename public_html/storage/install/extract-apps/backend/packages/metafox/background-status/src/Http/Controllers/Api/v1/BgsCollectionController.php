<?php

namespace MetaFox\BackgroundStatus\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;
use MetaFox\BackgroundStatus\Http\Requests\v1\GetBackgroundsRequest;
use MetaFox\BackgroundStatus\Http\Requests\v1\IndexRequest;
use MetaFox\BackgroundStatus\Http\Requests\v1\StoreRequest;
use MetaFox\BackgroundStatus\Http\Requests\v1\UpdateRequest;
use MetaFox\BackgroundStatus\Http\Resources\v1\BgsBackground\BgsBackgroundItemCollection;
use MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection\BgsCollectionDetail as Detail;
use MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection\BgsCollectionItemCollection as ItemCollection;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class BgsCollectionController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group background-status
 */
class BgsCollectionController extends ApiController
{
    /**
     * @var BgsCollectionRepositoryInterface
     */
    private BgsCollectionRepositoryInterface $repository;

    /**
     * @param BgsCollectionRepositoryInterface $repository
     */
    public function __construct(BgsCollectionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse collection.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->viewBgsCollectionsForFE(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Get background.
     *
     * @param GetBackgroundsRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function getBackgrounds(GetBackgroundsRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->getBackgrounds(user(), $params);

        return new BgsBackgroundItemCollection($data);
    }

    /**
     * Create background.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): Detail
    {
        $params = $request->validated();
        $data   = $this->repository->createBgsCollection(user(), $params);

        return new Detail($data);
    }

    /**
     * View background.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewBgsCollection(user(), $id);

        return new Detail($data);
    }
}
