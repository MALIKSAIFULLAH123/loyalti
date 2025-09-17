<?php

namespace MetaFox\Featured\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Featured\Http\Resources\v1\Package\PackageItemCollection as ItemCollection;
use MetaFox\Featured\Repositories\PackageRepositoryInterface;
use MetaFox\Featured\Http\Requests\v1\Package\IndexRequest;
use MetaFox\Platform\Support\Browse\Browse;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Featured\Http\Controllers\Api\PackageController::$controllers;
 */

/**
 * Class PackageController
 * @codeCoverageIgnore
 * @ignore
 */
class PackageController extends ApiController
{
    /**
     * @var PackageRepositoryInterface
     */
    private PackageRepositoryInterface $repository;

    /**
     * PackageController Constructor
     *
     * @param  PackageRepositoryInterface $repository
     */
    public function __construct(PackageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $context = user();

        if ($context->isGuest()) {
            throw new AuthorizationException();
        }

        $params = $request->validated();

        $view = Arr::get($params, 'view');

        $data = match ($view) {
            Browse::VIEW_SEARCH => $this->repository->viewPackagesForSearch($context, $params),
            default             => null,
        };

        if (null === $data) {
            throw new AuthorizationException();
        }

        return new ItemCollection($data);
    }
}
