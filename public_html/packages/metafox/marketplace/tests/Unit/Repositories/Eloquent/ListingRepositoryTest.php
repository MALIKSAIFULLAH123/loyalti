<?php

namespace MetaFox\Marketplace\Tests\Unit\Repositories\Eloquent;

use MetaFox\Marketplace\Repositories\Eloquent\ListingRepository;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use Tests\TestCase;

class ListingRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(ListingRepositoryInterface::class);
        $this->assertInstanceOf(ListingRepository::class, $repository);
    }
}

// end
