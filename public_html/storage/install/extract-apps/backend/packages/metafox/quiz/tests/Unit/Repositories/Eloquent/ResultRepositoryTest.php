<?php

namespace MetaFox\Quiz\Tests\Unit\Repositories\Eloquent;

use MetaFox\Quiz\Repositories\Eloquent\ResultRepository;
use MetaFox\Quiz\Repositories\ResultRepositoryInterface;
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
