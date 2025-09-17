<?php

namespace MetaFox\Search\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use MetaFox\Search\Http\Requests\v1\Search\TrendingRequest;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Search\Http\Requests\v1\Search\GroupRequest;
use MetaFox\Search\Http\Requests\v1\Search\IndexRequest;
use MetaFox\Search\Http\Requests\v1\Search\SuggestionRequest;
use MetaFox\Search\Http\Resources\v1\Search\SearchGroupItemCollection;
use MetaFox\Search\Http\Resources\v1\Search\SearchItemCollection as ItemCollection;
use MetaFox\Search\Http\Resources\v1\Search\SuggestionItemCollection;
use MetaFox\Search\Http\Resources\v1\Search\TrendingHashtagCollection;
use MetaFox\Search\Repositories\SearchRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Search\Http\Controllers\Api\SearchController::$controllers.
 */

/**
 * Class SearchController.
 * @ingore
 * @codeCoverageIgnore
 * @group search
 */
class SearchController extends ApiController
{
    /**
     * @var SearchRepositoryInterface
     */
    public $repository;

    public function __construct(SearchRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return array<string,           mixed>|JsonResponse
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();

        $view   = Arr::get($params, 'view', 'all');

        [$data, $meta] = $this->repository->searchItems(user(), $params);

        try {
            return [
                'data'       => new ItemCollection($data),
                'pagination' => $meta,
                'no_result'  => [
                    'title' => sprintf('global_search_%s_no_result', $view),
                ],
            ];
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    public function group(GroupRequest $request): ResourceCollection
    {
        $attributes = $request->validated();

        $context = user();

        $collection = $this->repository->getGroups($context, $attributes);

        return new SearchGroupItemCollection($collection);
    }

    /**
     * View Suggestions.
     *
     * @param SuggestionRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function suggestion(SuggestionRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->getSuggestion($context, $params);

        return $this->success(new SuggestionItemCollection($data));
    }

    /**
     * Display a listing of the resource.
     *
     * @param TrendingRequest $request
     *
     * @return ResourceCollection
     * @throws AuthenticationException
     */
    public function getTrendingHashtags(TrendingRequest $request): ResourceCollection
    {
        $data = $this->repository->getTrendingHashtags($request->validated());

        return new TrendingHashtagCollection($data);
    }
}
