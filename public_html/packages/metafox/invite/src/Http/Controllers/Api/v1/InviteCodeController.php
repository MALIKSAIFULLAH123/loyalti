<?php

namespace MetaFox\Invite\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Invite\Http\Requests\v1\InviteCode\StoreRequest;
use MetaFox\Invite\Http\Resources\v1\InviteCode\InviteCodeDetail as Detail;
use MetaFox\Invite\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Invite\Http\Controllers\Api\InviteCodeController::$controllers;
 */

/**
 * Class InviteCodeController
 * @codeCoverageIgnore
 * @ignore
 */
class InviteCodeController extends ApiController
{
    /**
     * @var InviteCodeRepositoryInterface
     */
    private InviteCodeRepositoryInterface $repository;

    /**
     * InviteCodeController Constructor
     *
     * @param InviteCodeRepositoryInterface $repository
     */
    public function __construct(InviteCodeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store item
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();

        $inviteCode = $this->repository->createUserCode($context);

        return $this->success(new Detail($inviteCode), [], __p('invite::phrase.linked_copied_to_clipboard'));

    }

    public function refresh(): JsonResponse
    {
        $context    = user();
        $inviteCode = $this->repository->getUserCode($context);

        $inviteCode->update([
            'code' => $this->repository->generateUniqueCodeValue(),
        ]);

        $inviteCode->refresh();

        return $this->success([
            'invite_code' => $inviteCode->code,
            'link_invite' => $inviteCode->toLinkInvite(),
        ], [], __p('invite::phrase.refresh_invite_code_successfully'));
    }
}
