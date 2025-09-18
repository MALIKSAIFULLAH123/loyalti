<?php

namespace MetaFox\Page\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Page\Http\Requests\v1\PageInvite\DeleteRequest;
use MetaFox\Page\Http\Requests\v1\PageInvite\IndexRequest;
use MetaFox\Page\Http\Requests\v1\PageInvite\StoreRequest;
use MetaFox\Page\Http\Requests\v1\PageInvite\UpdateRequest;
use MetaFox\Page\Http\Resources\v1\PageInvite\PageInviteItemCollection as ItemCollection;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\Facade\PageMembership;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Page\Http\Controllers\Api\PageInviteController::$controllers;
 */

/**
 * Class PageInviteController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageInviteController extends ApiController
{
    protected function inviteRepository(): PageInviteRepositoryInterface
    {
        return resolve(PageInviteRepositoryInterface::class);
    }

    protected function pageRepository(): PageRepositoryInterface
    {
        return resolve(PageRepositoryInterface::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  IndexRequest            $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->inviteRepository()->viewInvites(user(), $params);

        return $this->success(new ItemCollection($data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $this->inviteRepository()->inviteFriends(user(), $params['page_id'], $params['ids']);

        return $this->success([], [], __p('page::phrase.member_have_been_invites_to_like_page'));
    }

    /**
     * Used to accept/decline a request to like a page.
     *
     * @param  UpdateRequest           $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params   = $request->validated();
        $page     = $this->pageRepository()->find($id);
        $context  = user();
        $isAccept = (bool) $params['accept'];
        if ($isAccept) {
            return $this->accept($context, $page);
        }

        return $this->decline($context, $page);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  DeleteRequest           $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(DeleteRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $this->inviteRepository()->deleteInvite(user(), $id, $params['user_id']);
        $user = UserEntity::getById($params['user_id'])->detail;

        return $this->success([
            'user' => ResourceGate::asDetail($user),
        ], [], __p('page::phrase.successfully_deleted_the_invite'));
    }

    /**
     * @throws ValidatorException
     */
    protected function accept(User $context, Page $page): JsonResponse
    {
        $message = $this->inviteRepository()->getMessageAcceptInvite($page, $context, PageInvite::INVITE_ADMIN);
        $result  = $this->inviteRepository()->acceptInvite($page, $context);

        if (!$result) {
            return $this->error(__p('page::phrase.something_went_wrong_please_try_again'), 403);
        }

        return $this->success(ResourceGate::asDetail($page->refresh()), [], $message);
    }

    protected function decline(User $context, Page $page): JsonResponse
    {
        $result     = $this->inviteRepository()->declineInvite($page, $context);

        if (!$result) {
            return $this->error(__p('page::phrase.something_went_wrong_please_try_again'), 403);
        }

        return $this->success(ResourceGate::asDetail($page->refresh()), [], __p('page::phrase.denied_successfully'));
    }
}
