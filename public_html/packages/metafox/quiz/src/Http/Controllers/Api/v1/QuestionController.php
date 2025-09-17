<?php

namespace MetaFox\Quiz\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Quiz\Http\Requests\v1\Quiz\ViewPlayRequest;
use MetaFox\Quiz\Http\Resources\v1\Quiz\QuestionSummary;
use MetaFox\Quiz\Repositories\QuestionRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Quiz\Http\Controllers\Api\QuestionController::$controllers;
 */

/**
 * Class QuestionController.
 */
class QuestionController extends ApiController
{
    /**
     * @var QuestionRepositoryInterface
     */
    public QuestionRepositoryInterface $repository;

    public function __construct(QuestionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function viewPlays(ViewPlayRequest $request): JsonResponse
    {
        $params = $request->validated();

        $playInfo = $this->repository->viewQuestion(user(), $params);

        return $this->success(new QuestionSummary($playInfo));
    }
}
