<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Models\User;

class ModelDeletingListener
{
    public function __construct(protected PageMemberRepositoryInterface $pageMemberRepository)
    {
    }

    /**
     * @param       $model
     * @return void
     */
    public function handle($model): void
    {
        if ($model instanceof User) {
            return;
        }

        $pageIds = Settings::get('page.auto_follow_pages_on_signup');

        if (!is_array($pageIds)) {
            return;
        }

        if (!in_array($model->entityId(), $pageIds)) {
            return;
        }

        $kept = array_filter($pageIds, function ($pageId) use ($model) {
            return $pageId != $model->entityId();
        });

        $kept = array_values($kept);

        Settings::save([
            'page.auto_follow_pages_on_signup' => $kept,
        ]);
    }
}
