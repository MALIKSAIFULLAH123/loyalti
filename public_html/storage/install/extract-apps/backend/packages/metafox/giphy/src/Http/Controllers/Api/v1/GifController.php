<?php

namespace MetaFox\Giphy\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Giphy\Http\Requests\v1\Gif\SearchRequest;
use MetaFox\Giphy\Http\Requests\v1\Gif\TrendingRequest;
use MetaFox\Giphy\Http\Resources\v1\Gif\GifItemCollection;
use MetaFox\Giphy\Repositories\GifRepositoryInterface;
use MetaFox\Giphy\Supports\Helpers;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class GifController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class GifController extends ApiController
{
    public function __construct(protected GifRepositoryInterface $repository)
    {
    }

    /**
     * @param SearchRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $context = user();

        $params = $request->validated();

        $responseData = $this->repository->search($context, $params);

        if (empty($responseData)) {
            return $this->success(new GifItemCollection([]));
        }

        $data       = $responseData['data'] ?? [];
        $pagination = [
            'limit' => $responseData['pagination']['limit'] ?? Helpers::DEFAULT_LIMIT,
        ];

        $response = [
            'data'       => new GifItemCollection($data),
            'pagination' => $pagination,
        ];

        return response()->json($response);
    }

    /**
     * @param TrendingRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function trending(TrendingRequest $request): JsonResponse
    {
        $context = user();

        $params = $request->validated();

        $responseData = $this->repository->trending($context, $params);

        if (empty($responseData)) {
            return $this->success(new GifItemCollection([]));
        }

        $data       = $responseData['data'] ?? [];
        $pagination = [
            'limit' => $responseData['pagination']['limit'] ?? Helpers::DEFAULT_LIMIT,
        ];

        $response = [
            'data'       => new GifItemCollection($data),
            'pagination' => $pagination,
        ];

        return response()->json($response);
    }
}
