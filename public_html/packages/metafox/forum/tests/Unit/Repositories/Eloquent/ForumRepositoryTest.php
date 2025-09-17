<?php

namespace MetaFox\Forum\Tests\Unit\Repositories\Eloquent;

use MetaFox\Forum\Repositories\Eloquent\ForumRepository;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use Tests\TestCase;

class ForumRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(ForumRepositoryInterface::class);
        $this->assertInstanceOf(ForumRepository::class, $repository);
    }
}

// end
