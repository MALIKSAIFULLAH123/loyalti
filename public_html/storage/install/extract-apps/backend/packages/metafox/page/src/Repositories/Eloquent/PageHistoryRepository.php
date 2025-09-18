<?php

namespace MetaFox\Page\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageHistory;
use MetaFox\Page\Notifications\UpdateInformationNotification;
use MetaFox\Page\Repositories\PageHistoryRepositoryInterface;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Page\Support\PageSupport;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class PageHistoryRepository
 *
 * @method PageHistory getModel()
 * @method PageHistory find($id, $columns = ['*'])
 */
class PageHistoryRepository extends AbstractRepository implements PageHistoryRepositoryInterface
{
    public function model()
    {
        return PageHistory::class;
    }

    public function memberRepository(): PageMemberRepositoryInterface
    {
        return resolve(PageMemberRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function createHistory(User $context, Page $page, array $attributes): void
    {
        $data = [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'page_id'   => $page->entityId(),
            'type'      => Arr::get($attributes, 'type', PageSupport::UPDATE_PAGE_NAME_TYPE),
            'extra'     => json_encode($this->handleExtra($page, $attributes)),
        ];

        $model = $this->getModel()->fill($data);
        $model->save();

        $this->sentNotification($model);
    }

    /**
     * @inheritDoc
     */
    public function sentNotification(PageHistory $model): void
    {
        if ($model->type != PageSupport::UPDATE_PAGE_NAME_TYPE) {
            return;
        }

        $members = $this->memberRepository()->getPageMembers($model->page_id);

        $notification = new UpdateInformationNotification($model);

        foreach ($members as $member) {
            if ($member->userId() == $model->userId()) {
                continue;
            }

            $notificationParams = [$member->user, $notification];
            Notification::send(...$notificationParams);
        }
    }

    protected function handleExtra(Page $page, array $attributes): array
    {
        $type = Arr::get($attributes, 'type', PageSupport::UPDATE_PAGE_NAME_TYPE);
        $extra = [];

        if ($type == PageSupport::UPDATE_PAGE_NAME_TYPE) {
            $extra = [
                'old' => $page->toTitle(),
                'new' => Arr::get($attributes, 'name'),
            ];
        }

        return $extra;
    }
}
