<?php

namespace MetaFox\Poll\Tests\Unit\Repositories\Eloquent;

use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use Tests\TestCase;

class PollRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(PollRepositoryInterface::class);
        $this->assertInstanceOf(PollRepository::class, $repository);
    }
}

// end
