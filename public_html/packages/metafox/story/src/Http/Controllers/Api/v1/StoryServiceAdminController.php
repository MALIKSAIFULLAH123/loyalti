<?php

namespace MetaFox\Story\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Story\Http\Requests\v1\Story\Admin\IndexRequest;
use MetaFox\Story\Http\Resources\v1\Story\Admin\StoryServiceItemCollection as ItemCollection;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Support\Facades\StoryFacades;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Story\Http\Controllers\Api\StoryServiceAdminController::$controllers;
 */

/**
 * Class StoryServiceAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class StoryServiceAdminController extends ApiController
{
    /**
     * StoryServiceAdminController Constructor
     *
     * @param StoryRepositoryInterface $repository
     */
    public function __construct(protected StoryRepositoryInterface $repository)
    {
    }

    /**
     * Browse category.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $context = user();
        $params = $request->validated();

        $data = StoryFacades::viewServices($context, $params);

        return new ItemCollection($data);
    }
}
