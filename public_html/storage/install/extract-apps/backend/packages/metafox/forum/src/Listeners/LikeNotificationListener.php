<?php

namespace MetaFox\Forum\Listeners;

use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserEntity;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UndefinedVariable)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class LikeNotificationListener
{
    public function handle(?User $context, ?UserEntity $user = null, ?Content $model = null): ?string
    {
        if (!$user instanceof UserEntity) {
            return null;
        }

        if (!$model instanceof ForumPost && !$model instanceof ForumThread) {
            return null;
        }

        $entityType = $model->entityType();
        $locale     = $context?->preferredLocale();

        $message = null;

        $fullName = '<b>' . $user->name . '</b>';

        switch ($entityType) {
            case ForumPost::ENTITY_TYPE:
                $thread = $model->thread;

                if (null !== $thread) {
                    $message = __p('forum::notification.full_name_reacted_your_reply_in_the_thread_title', [
                        'full_name' => $fullName,
                        'title'     => '<b>' . $thread->toTitle() . '</b>',
                    ], $locale);
                }

                break;

            default:
                $message = __p('forum::notification.full_name_reacted_your_thread_title', [
                    'full_name' => $fullName,
                    'title'     => '<b>' . $model->toTitle() . '</b>',
                ], $locale);

                break;
        }

        return $message;
    }
}
