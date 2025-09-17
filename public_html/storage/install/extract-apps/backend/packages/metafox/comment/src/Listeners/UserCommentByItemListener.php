<?php

namespace MetaFox\Comment\Listeners;

use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserCommentByItemListener
{
    protected function repository()
    {
        return resolve(CommentRepositoryInterface::class);
    }

    public function handle(?User $context, array $attributes)
    {
        $attributes = array_merge($attributes, [
            'limit' => 20,
        ]);

        [, $collection] = $this->repository()->getUsersCommentByItem($context, $attributes);

        return $collection;
    }
}
