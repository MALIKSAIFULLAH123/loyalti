<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Observers;

use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;

class PageMemberObserver
{
    public function __construct(protected PageMemberRepositoryInterface $memberRepository)
    {
    }

    public function created(PageMember $model): void
    {
        $model->page->incrementAmount('total_member');
    }

    public function deleted(PageMember $model): void
    {
        $page = $model->page;
        $page->decrementAmount('total_member');

        if ($page->isAdmin($model->user)) {
            $page->decrementAmount('total_admin');
        }

        $this->memberRepository->deleteNotification($model);
    }
}
