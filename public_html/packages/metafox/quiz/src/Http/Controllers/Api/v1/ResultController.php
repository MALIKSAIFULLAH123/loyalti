<?php

namespace MetaFox\Quiz\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Quiz\Http\Requests\v1\Quiz\ViewIndividualPlay;
use MetaFox\Quiz\Http\Requests\v1\Result\IndexRequest;
use MetaFox\Quiz\Http\Requests\v1\Result\StoreRequest;
use MetaFox\Quiz\Http\Resources\v1\Quiz\QuizDetail as Detail;
use MetaFox\Quiz\Http\Resources\v1\Result\IndividualResultDetail;
use MetaFox\Quiz\Http\Resources\v1\Result\ResultItemCollection as ItemCollection;
use MetaFox\Quiz\Repositories\ResultRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Quiz\Http\Controllers\Api\ResultController::$controllers;
 */

/**
 * Class ResultController.
 */
class ResultController extends ApiController
{
    /**
     * @var ResultRepositoryInterface
     */
    public ResultRepositoryInterface $repository;

    public function __construct(ResultRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  IndexRequest            $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->viewResults(user(), $params);

        return $this->success(new ItemCollection($data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $quiz   = $this->repository->createResult(user(), $params);

        return $this->success(new Detail($quiz));
    }

    /**
     * @param ViewIndividualPlay $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function viewIndividualPlay(ViewIndividualPlay $request): JsonResponse
    {
        $params = $request->validated();

        $playInfo = $this->repository->viewResult(user(), $params);

        if ($playInfo) {
            return $this->success(new IndividualResultDetail($playInfo));
        }

        return $this->error(__p('quiz::phrase.cannot_find_results'));
    }
}
