<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent;

use MetaFox\Saved\Repositories\Eloquent\SavedAggRepository;
use MetaFox\Saved\Repositories\SavedAggRepositoryInterface;
use Tests\TestCase;

class SavedAggRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(SavedAggRepositoryInterface::class);
        $this->assertInstanceOf(SavedAggRepository::class, $repository);
    }
}

// end
