<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Http\Resources\v1\ExampleRule\ExampleRuleItemCollection as ItemCollection;
use MetaFox\Group\Repositories\ExampleRuleRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Group\Http\Controllers\Api\ExampleRuleController::$controllers.
 */

/**
 * Class ExampleRuleController.
 * @ignore
 * @codeCoverageIgnore
 * @group group
 * @authenticated
 */
class ExampleRuleController extends ApiController
{
    /**
     * @var ExampleRuleRepositoryInterface
     */
    private ExampleRuleRepositoryInterface $repository;

    /**
     * ExampleRuleController constructor.
     *
     * @param ExampleRuleRepositoryInterface $repository
     */
    public function __construct(ExampleRuleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse example rules.
     *
     * @return JsonResource
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index()
    {
        $data = $this->repository->getAllActiveRuleExamples(user());

        return new ItemCollection($data);
    }
}
