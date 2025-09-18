<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Group\Http\Requests\v1\GroupInviteCode\StoreRequest;
use MetaFox\Group\Http\Resources\v1\GroupInviteCode\GroupInviteCodeItem;
use MetaFox\Group\Repositories\GroupInviteCodeRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Group\Http\Controllers\Api\GroupInviteCodeController::$controllers.
 */

/**
 * Class GroupInviteCodeController.
 * @codeCoverageIgnore
 * @ignore
 */
class GroupInviteCodeController extends ApiController
{
    /**
     * @var GroupInviteCodeRepositoryInterface
     */
    private GroupInviteCodeRepositoryInterface $repository;
    private GroupRepositoryInterface           $groupRepository;

    /**
     * GroupInviteCodeController Constructor.
     *
     * @param GroupInviteCodeRepositoryInterface $repository
     * @param GroupRepositoryInterface           $groupRepository
     */
    public function __construct(
        GroupInviteCodeRepositoryInterface $repository,
        GroupRepositoryInterface           $groupRepository
    )
    {
        $this->repository      = $repository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $group   = $this->groupRepository->find($params['group_id']);

        $code = match ((int)$params['refresh']) {
            1       => $this->repository->refreshCode($context, $group),
            default => $this->repository->generateCode($context, $group),
        };

        return $this->success(new GroupInviteCodeItem($code), [], __p('group::phrase.linked_copied_to_clipboard'));
    }

    public function verify(string $codeValue): JsonResponse
    {
        $code = $this->repository->verifyCodeByValue($codeValue);

        return $this->success(ResourceGate::asResource($code, 'item'));
    }

    /**
     * @throws AuthenticationException
     */
    public function accept(string $codeValue): JsonResponse
    {
        $context = user();
        $member  = $this->repository->acceptCodeByValue($context, $codeValue);

        return $this->success(ResourceGate::asResource($member, 'item'));
    }
}
