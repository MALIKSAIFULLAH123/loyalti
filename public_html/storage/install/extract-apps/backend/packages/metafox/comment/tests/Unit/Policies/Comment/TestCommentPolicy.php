<?php

namespace MetaFox\Comment\Tests\Unit\Policies\Comment;

use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Policies\CommentPolicy;
use Tests\Traits\TestResourcePolicy;

trait TestCommentPolicy
{
    use TestResourcePolicy;

    public function policyName(): string
    {
        return CommentPolicy::class;
    }

    public function resourceName(): string
    {
        return Comment::class;
    }
}
