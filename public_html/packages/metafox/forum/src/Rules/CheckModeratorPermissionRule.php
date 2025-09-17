<?php
namespace MetaFox\Forum\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;

class CheckModeratorPermissionRule implements ValidationRule, DataAwareRule
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param string  $attribute
     * @param mixed   $value
     * @param Closure $fail
     * @return void
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($this->data)) {
            return;
        }

        if (!$value) {
            return;
        }

        if (Arr::get($this->data, 'is_wiki') == 1) {
            return;
        }

        $thread = null;

        if (is_numeric($id = Arr::get($this->data, 'id'))) {
            $thread = resolve(ForumThreadRepositoryInterface::class)->find($id);
        }

        $context = user();

        if ($thread instanceof ForumThread) {
            if ($thread->forum_id == $value) {
                return;
            }

            if ($thread->is_wiki) {
                return;
            }

            if (policy_check(ForumThreadPolicy::class, 'move', $context, $thread)) {
                return;
            }

            $fail(__p('forum::validation.you_dont_have_permission_to_move_the_thread_to_this_forum'));

            return;
        }

        if (policy_check(ForumThreadPolicy::class, 'hasCreationPermission', user(), $value)) {
            return;
        }

        $fail(__p('forum::validation.you_dont_have_permission_to_create_thread_on_this_forum'));
    }

    public function setData($data): void
    {
        $this->data = $data;
    }
}
