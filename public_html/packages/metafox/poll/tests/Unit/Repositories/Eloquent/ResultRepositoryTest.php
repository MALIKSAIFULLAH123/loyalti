<?php

namespace MetaFox\Poll\Tests\Unit\Repositories\Eloquent;

use MetaFox\Poll\Repositories\Eloquent\ResultRepository;
use MetaFox\Poll\Repositories\ResultRepositoryInterface;
use Tests\TestCase;

class ResultRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(ResultRepositoryInterface::class);
        $this->assertInstanceOf(ResultRepository::class, $repository);
    }
}

// end
