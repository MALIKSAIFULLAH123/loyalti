<?php

namespace MetaFox\Page\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageClaim;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Notifications\ApproveRequestClaimNotification;
use MetaFox\Page\Policies\CategoryPolicy;
use MetaFox\Page\Policies\PageClaimPolicy;
use MetaFox\Page\Repositories\BlockRepositoryInterface;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Page\Repositories\PageClaimRepositoryInterface;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\Browse\Scopes\Page\SortScope;
use MetaFox\Page\Support\Facade\PageClaim as PageClaimFacade;
use MetaFox\Page\Support\PageClaimSupport;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\User\Support\Facades\UserBlocked;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class PageRepository.
 * @method PageClaim getModel()
 * @method PageClaim find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PageClaimRepository extends AbstractRepository implements PageClaimRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    public function model(): string
    {
        return PageClaim::class;
    }

    private function pageRepository(): PageRepositoryInterface
    {
        return resolve(PageRepositoryInterface::class);
    }

    private function memberRepository(): PageMemberRepositoryInterface
    {
        return resolve(PageMemberRepositoryInterface::class);
    }

    private function blockRepository(): BlockRepositoryInterface
    {
        return resolve(BlockRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function viewPageClaims(User $context, User $owner, array $attributes): Paginator
    {
        $query      = $this->getModel()->newQuery();
        $limit      = Arr::get($attributes, 'limit');
        $search     = Arr::get($attributes, 'q');
        $sort       = Arr::get($attributes, 'sort', SortScope::SORT_DEFAULT);
        $sortType   = Arr::get($attributes, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        $when       = Arr::get($attributes, 'when', Browse::WHEN_ALL);
        $categoryId = Arr::get($attributes, 'category_id', 0);
        $status     = Arr::get($attributes, 'status');

        $query->select('page_claims.*')
            ->leftJoin('pages', 'pages.id', '=', 'page_claims.page_id')
            ->where('page_claims.user_id', $context->entityId());

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType)->setTable('pages');

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        if ($search) {
            $searchScope = new SearchScope();
            $searchScope->setSearchText($search)
                ->setFields(['pages.name', 'page_claims.message']);

            $query->addScope($searchScope);
        }

        if ($categoryId > 0) {
            $category = resolve(PageCategoryRepositoryInterface::class)->find($categoryId);

            policy_authorize(CategoryPolicy::class, 'viewActive', $context, $category);

            $query->where('pages.category_id', $categoryId);
        }

        if ($status) {
            $query->where('status_id', PageClaimFacade::getStatusId($status));
        }

        return $query->addScope($sortScope)
            ->addScope($whenScope)
            ->simplePaginate($limit);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function viewAdminPageClaims(int $limit = Pagination::DEFAULT_ITEM_PER_PAGE): Paginator
    {
        $query = $this->getModel()->newQuery();

        $sortScope = new SortScope();
        $sortScope->setSort(SortScope::SORT_DEFAULT)
            ->setSortType(SortScope::SORT_TYPE_DEFAULT)
            ->setTable('pages');

        $query->where('status_id', PageClaimSupport::STATUS_PENDING)
            ->addScope($sortScope);

        return $query->paginate($limit);
    }

    /**
     * @param int $id
     * @param int $status
     * @return Page
     * @throws ValidatorException
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function updatePageClaim(int $id, int $status): Page
    {
        $context       = user();
        $pageClaim     = $this->find($id);
        $page          = $pageClaim->page;
        $oldUser       = $page->user;
        $userPageClaim = $pageClaim->user;

        $pageClaim->update(['status_id' => $status]);
        $this->deleteNotification($pageClaim);

        if ($status != PageClaimSupport::STATUS_APPROVE) {
            return $page->refresh();
        }

        policy_authorize(PageClaimPolicy::class, 'approve', $context, $pageClaim);

        $notification = new ApproveRequestClaimNotification($pageClaim);
        $notification->setUserId($context->entityId())
            ->setUserType($context->entityType());

        $response = [[$userPageClaim, $oldUser], $notification];

        Notification::send(...$response);

        $page->update(['user_id' => $userPageClaim->entityId()]);

        $this->memberRepository()
            ->addPageMember($page, $userPageClaim->entityId(), PageMember::ADMIN);

        $this->memberRepository()
            ->updatePageMember($context, $page->entityId(), $oldUser->entityId(), PageMember::MEMBER);

        if (UserBlocked::isBlocked($page, $userPageClaim)) {
            $this->blockRepository()->deletePageBlock($userPageClaim, $page->entityId(), ['user_id' => $userPageClaim->entityId()]);
        }

        return $page->refresh();
    }

    /**
     * @throws AuthorizationException
     */
    public function createPageClaim(User $user, int $id, ?string $message = null): bool
    {
        $page = $this->pageRepository()->with(['pageClaim'])->find($id);

        policy_authorize(PageClaimPolicy::class, 'create', $user, $page);

        return (new PageClaim([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'page_id'   => $page->entityId(),
            'message'   => $message,
        ]))->save();
    }

    /**
     * @throws AuthorizationException
     */
    public function resubmitPageClaim(User $user, int $id): PageClaim
    {
        $pageClaim = $this->find($id);

        policy_authorize(PageClaimPolicy::class, 'resubmit', $user, $pageClaim);

        $pageClaim->status_id = PageClaimSupport::STATUS_PENDING;
        $pageClaim->save();

        return $pageClaim->refresh();
    }

    /**
     * @inheritDoc
     */
    public function isPendingRequest(User $user, int $pageId): bool
    {
        return $this->getModel()->newQuery()
            ->where('user_id', $user->entityId())
            ->where('page_id', $pageId)
            ->where('status_id', PageClaimSupport::STATUS_PENDING)
            ->exists();
    }

    public function deleteNotification(PageClaim $claim): void
    {
        app('events')->dispatch('notification.delete_mass_notification_by_item', [$claim], true);
    }

    public function deleteClaimByUser(User $user, int $pageId): void
    {
        $claim = $this->getModel()->newQuery()
            ->where('user_id', $user->entityId())
            ->where('page_id', $pageId)
            ->first();

        if ($claim instanceof PageClaim) {
            $claim->delete();
            $this->deleteNotification($claim);
        }
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function viewPageClaim(User $context, int $id): PageClaim
    {
        $pageClaim = $this->find($id);

        policy_authorize(PageClaimPolicy::class, 'view', $context, $pageClaim);

        $pageClaim->with(['page']);

        return $pageClaim;
    }
}
