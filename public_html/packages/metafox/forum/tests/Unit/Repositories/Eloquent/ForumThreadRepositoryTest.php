<?php

namespace MetaFox\Forum\Tests\Unit\Repositories\Eloquent;

use MetaFox\Forum\Repositories\Eloquent\ForumThreadRepository;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use Tests\TestCase;

class ForumThreadRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(ForumThreadRepositoryInterface::class);
        $this->assertInstanceOf(ForumThreadRepository::class, $repository);
    }
}

// end
