<?php

namespace MetaFox\Page\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Page\Http\Requests\v1\SearchMember\IndexRequest;
use MetaFox\Page\Http\Resources\v1\Block\BlockItemCollection;
use MetaFox\Page\Http\Resources\v1\PageInvite\PageInviteItemCollection;
use MetaFox\Page\Http\Resources\v1\PageMember\PageMemberItemCollection;
use MetaFox\Page\Repositories\BlockRepositoryInterface;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Page\Support\Browse\Scopes\SearchMember\ViewScope;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Page\Http\Controllers\Api\SearchMemberController::$controllers;
 */

/**
 * Class SearchMemberController.
 * @codeCoverageIgnore
 * @ignore
 */
class SearchMemberController extends ApiController
{
    /**
     * SearchMemberController Constructor.
     */
    public function __construct(
        protected PageMemberRepositoryInterface $memberRepository,
        protected PageInviteRepositoryInterface $inviteRepository,
        protected BlockRepositoryInterface $blockRepository,
    ) {
    }

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $param   = $request->validated();
        $view    = Arr::get($param, 'view', ViewScope::VIEW_ALL);
        $pageId  = Arr::get($param, 'page_id');
        $context = user();

        Arr::forget($param, 'view');

        switch ($view) {
            case ViewScope::VIEW_BLOCK:
                $data = $this->blockRepository->viewPageBlocks($context, $param);

                return new BlockItemCollection($data);
            case ViewScope::VIEW_INVITE:
                $data = $this->inviteRepository->viewInvites($context, $param);

                return new PageInviteItemCollection($data);
            default:
                Arr::set($param, 'view', $view);
                $data = $this->memberRepository->viewPageMembers($context, $pageId, $param);

                return new PageMemberItemCollection($data);
        }
    }
}
