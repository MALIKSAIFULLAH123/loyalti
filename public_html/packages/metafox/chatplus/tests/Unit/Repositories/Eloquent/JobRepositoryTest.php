<?php

namespace MetaFox\ChatPlus\Tests\Unit\Repositories\Eloquent;

use MetaFox\ChatPlus\Repositories\Eloquent\JobRepository;
use MetaFox\ChatPlus\Repositories\JobRepositoryInterface;
use Tests\TestCase;

class JobRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(JobRepositoryInterface::class);
        $this->assertInstanceOf(JobRepository::class, $repository);
    }
}

// end
