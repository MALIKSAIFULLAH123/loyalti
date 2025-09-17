<?php

namespace MetaFox\Forum\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;

class RequiredForumRule implements
    Rule,
    DataAwareRule,
    ValidatorAwareRule,
    ImplicitRule
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var Validator
     */
    protected $validator;

    public function passes($attribute, $value): bool
    {
        $data = $this->data;

        $isWiki = Arr::has($data, 'is_wiki') && $data['is_wiki'] == 1;

        if (!$isWiki) {
            return $this->hasExists($attribute, $value);
        }

        return true;
    }

    protected function isValidNumeric($attribute, $value): bool
    {
        return $this->validator->validateNumeric($attribute, $value) && $value > 0;
    }

    protected function hasExists($attribute, $value): bool
    {
        if (!$this->isValidNumeric($attribute, $value)) {
            return false;
        }

        if (!$this->validator->validateExists($attribute, $value, ['forums', 'id'])) {
            return false;
        }

        $thread = null;

        if (is_numeric($id = Arr::get($this->data, 'id'))) {
            $thread = resolve(ForumThreadRepositoryInterface::class)->find($id);
        }

        if ($thread instanceof ForumThread && $thread->forum_id == $value) {
            return true;
        }

        return $this->isActiveForum($value);
    }

    protected function isActiveForum(int $forumId): bool
    {
        $activeForumIds = resolve(ForumRepositoryInterface::class)->getActiveForumIds();

        return in_array($forumId, $activeForumIds);
    }

    public function message()
    {
        return __p('forum::validation.forum_id.required');
    }

    public function setData($data): void
    {
        $this->data = $data;
    }

    public function setValidator($validator): void
    {
        $this->validator = $validator;
    }
}
