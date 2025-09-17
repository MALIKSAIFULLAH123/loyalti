<?php

namespace MetaFox\Blog\Tests\Unit\Policies\Blog;

use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Policies\BlogPolicy;
use Tests\Traits\TestResourcePolicy;

trait TestBlogPolicy
{
    use TestResourcePolicy;

    public function policyName(): string
    {
        return BlogPolicy::class;
    }

    public function resourceName(): string
    {
        return Blog::class;
    }
}
