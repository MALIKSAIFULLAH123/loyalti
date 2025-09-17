<?php

namespace MetaFox\Page\Observers;

use Illuminate\Support\Carbon;
use MetaFox\Page\Models\PageInvite as Model;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;

/**
 * Class PageInviteObserver.
 */
class PageInviteObserver
{
    public function __construct(protected PageInviteRepositoryInterface $inviteRepository)
    {
    }
    public function creating(Model $invite): void
    {
        $invite->expired_at = Carbon::now()->addDays(Model::EXPIRE_DAY);
    }
    public function created(Model $invite): void
    {
    }

    public function updating(Model $invite): void
    {
        if ($invite->status_id == Model::STATUS_PENDING) {
            $invite->expired_at = Carbon::now()->addDays(Model::EXPIRE_DAY);
        }
    }

    public function deleted(Model $invite): void
    {
        $this->inviteRepository->deleteNotification($invite);

        if ($invite->invite_type == Model::INVITE_MEMBER) {
            $invite->page->decrementAmount('total_invite');
        }
    }
}
