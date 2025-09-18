<?php

namespace MetaFox\Invite\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Invite\Http\Requests\v1\Invite\BatchDeletedRequest;
use MetaFox\Invite\Http\Requests\v1\Invite\BatchResendRequest;
use MetaFox\Invite\Http\Requests\v1\Invite\IndexRequest;
use MetaFox\Invite\Http\Requests\v1\Invite\StoreRequest;
use MetaFox\Invite\Http\Resources\v1\Invite\InviteDetail as Detail;
use MetaFox\Invite\Http\Resources\v1\Invite\InviteItemCollection as ItemCollection;
use MetaFox\Invite\Models\Invite;
use MetaFox\Invite\Policies\InvitePolicy;
use MetaFox\Invite\Repositories\InviteRepositoryInterface;
use MetaFox\Invite\Support\Facades\Invite as InviteFacade;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Invite\Http\Controllers\Api\InviteController::$controllers;
 */

/**
 * Class InviteController.
 * @codeCoverageIgnore
 * @ignore
 */
class InviteController extends ApiController
{
    /**
     * @var InviteRepositoryInterface
     */
    private InviteRepositoryInterface $repository;

    /**
     * InviteController Constructor.
     *
     * @param InviteRepositoryInterface $repository
     */
    public function __construct(InviteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $context = $owner = user();
        if ($params['user_id'] > 0) {
            $owner = UserEntity::getById($params['user_id'])->detail;
        }
        $data = $this->repository->viewInvites($context, $owner, $params ?? []);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     * @bodyParam recipients string[] Multiple emails or phone numbers.
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        policy_authorize(InvitePolicy::class, 'create', $context);

        $result  = $this->repository->createInvites($context, $params);
        $message = null;

        if ($result['success']) {
            $message = $this->repository->getMessageForInviteSuccess($result['success']);
        }

        return $this->success($result, [], $message);
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteInvite(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('invite::phrase.deleted_invited_successfully'));
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function resend(int $id): JsonResponse
    {
        $context = user();
        policy_authorize(InvitePolicy::class, 'create', $context);
        $result = $this->repository->resend($context, $id);

        if (!$result instanceof Invite) {
            return $this->error();
        }

        InviteFacade::send($result);

        return $this->success(
            new Detail($result),
            [],
            __p('invite::phrase.resend_invited_recipient_successfully', [
                'recipient' => $result->email ?? $result->phone_number,
            ])
        );
    }

    /**
     * @param BatchDeletedRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function batchDeleted(BatchDeletedRequest $request): JsonResponse
    {
        $params = $request->validated();

        $this->repository->batchDeleted(user(), $params);

        return $this->success([], [], __p('invite::phrase.deleted_invited_successfully'));
    }

    /**
     * @throws AuthenticationException
     */
    public function batchResend(BatchResendRequest $request): JsonResponse
    {
        $params = $request->validated();

        $this->repository->batchResend(user(), $params);

        return $this->success([], [], __p('invite::phrase.resend_invited_successfully'));
    }
}
