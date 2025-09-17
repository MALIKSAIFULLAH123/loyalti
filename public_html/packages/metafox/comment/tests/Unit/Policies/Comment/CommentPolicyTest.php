<?php

namespace MetaFox\Comment\Tests\Unit\Policies\Comment;

use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Policies\CommentPolicy;
use Tests\TestCase;
use Tests\Traits\TestUserPermissions;

class CommentPolicyTest extends TestCase
{
    use TestUserPermissions;

    public function policyName(): string
    {
        return CommentPolicy::class;
    }

    public function resourceName(): string
    {
        return Comment::class;
    }

    public static function provideUserPermisions()
    {
        return [
            [['comment.comment' => false], 'create', false],
            [['comment.moderate' => true], 'update', true],
            [['comment.moderate' => true], 'delete', true],
            [[], 'share', false],
            [['comment.comment' => false], 'create', false],
        ];
    }
}
