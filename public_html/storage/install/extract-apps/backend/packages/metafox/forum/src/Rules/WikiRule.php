<?php

namespace MetaFox\Forum\Rules;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Validation\Rule;
use MetaFox\Forum\Support\Facades\ForumThread;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;

/**
 * WikiRule.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class WikiRule implements Rule
{
    /**
     * @var int
     */
    protected $id;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function __construct(protected User $context)
    {
    }

    /**
     * @throws AuthenticationException
     */
    public function passes($attribute, $value)
    {
        if (!in_array($value, [0, 1])) {
            return false;
        }

        if ($value == 0) {
            return true;
        }

        $owner = null;

        if (null !== $this->id) {
            $thread = ForumThread::getThread($this->id);

            $owner = $thread?->user;
        }

        $context = $this->context;

        if (null !== $owner && $owner->entityId() == $context->entityId()) {
            return true;
        }

        if ($context->hasPermissionTo('forum_thread.create_as_wiki')) {
            return true;
        }

        if ($context->hasPermissionTo('forum_thread.moderate')) {
            return true;
        }

        return $context->hasAnyRole([UserRole::ADMIN_USER, UserRole::SUPER_ADMIN_USER]);
    }

    public function message(): string
    {
        return __p('forum::validation.wiki_rule');
    }
}
