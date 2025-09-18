<?php

namespace MetaFox\Marketplace\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Marketplace\Http\Requests\v1\Invite\InvitedPeopleRequest;
use MetaFox\Marketplace\Http\Requests\v1\Invite\InviteRequest;
use MetaFox\Marketplace\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityItemCollection;

/**
 * Class InviteController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @group marketplace
 * @authenticated
 */
class InviteController extends ApiController
{
    /**
     * @var InviteRepositoryInterface
     */
    private InviteRepositoryInterface $repository;

    /**
     * @param InviteRepositoryInterface $repository
     */
    public function __construct(InviteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function index(): JsonResponse
    {
        //todo: add request IndexRequest $request params
        //todo: add $params = $request->validated();
        //todo: add func in repository to get list visited/invited guest
        return $this->success([], [], []); //todo: handle later
    }

    /**
     * @param  InviteRequest           $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(InviteRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $this->repository->inviteToListing($context, $params);

        return $this->success([], [], __p('marketplace::phrase.invitation_s_successfully_sent'));
    }

    public function getInvitedPeople(InvitedPeopleRequest $request): UserEntityItemCollection
    {
        $data = $request->validated();

        $context = user();

        $people = $this->repository->viewInvitedPeople($context, $data);

        return new UserEntityItemCollection($people);
    }
}
