<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Group\Http\Requests\v1\SearchMember\IndexRequest;
use MetaFox\Group\Http\Resources\v1\Block\BlockItemCollection;
use MetaFox\Group\Http\Resources\v1\Invite\InviteItemCollection;
use MetaFox\Group\Http\Resources\v1\Member\MemberItemCollection;
use MetaFox\Group\Http\Resources\v1\Mute\MuteItemCollection;
use MetaFox\Group\Repositories\BlockRepositoryInterface;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Repositories\MuteRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\Invite\StatusScope;
use MetaFox\Group\Support\Browse\Scopes\Invite\ViewScope as InviteViewScope;
use MetaFox\Group\Support\Browse\Scopes\SearchMember\ViewScope;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Group\Http\Controllers\Api\SearchMemberController::$controllers;
 */

/**
 * Class SearchMemberController.
 * @codeCoverageIgnore
 * @ignore
 */
class SearchMemberController extends ApiController
{
    public function __construct(
        protected MemberRepositoryInterface $memberRepository,
        protected InviteRepositoryInterface $inviteRepository,
        protected BlockRepositoryInterface  $blockRepository,
        protected MuteRepositoryInterface   $muteRepository
    ) {}

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request)
    {
        $param   = $request->validated();
        $view    = Arr::get($param, 'view', ViewScope::VIEW_ALL);
        $groupId = Arr::get($param, 'group_id');
        $context = user();

        Arr::forget($param, 'view');

        switch ($view) {
            case ViewScope::VIEW_BLOCK:
                $data = $this->blockRepository->viewGroupBlocks($context, $param);

                return new BlockItemCollection($data);
            case ViewScope::VIEW_INVITE:
                Arr::set($param, 'view', InviteViewScope::VIEW_MEMBERS);
                Arr::set($param, 'status', StatusScope::STATUS_PENDING);
                $data = $this->inviteRepository->viewInvites($context, $param);

                return new InviteItemCollection($data);
            case ViewScope::VIEW_MUTE:
                $data = $this->muteRepository->viewMutedUsersInGroup($context, $groupId, $param);

                return new MuteItemCollection($data);
            default:
                Arr::set($param, 'view', $view);
                $data = $this->memberRepository->viewGroupMembers($context, $groupId, $param);

                return new MemberItemCollection($data);
        }
    }
}
