<?php

namespace MetaFox\Forum\Tests\Unit\Repositories\Eloquent;

use MetaFox\Forum\Repositories\Eloquent\ForumPostRepository;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use Tests\TestCase;

class ForumPostRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(ForumPostRepositoryInterface::class);
        $this->assertInstanceOf(ForumPostRepository::class, $repository);
    }
}

// end
