<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent;

use MetaFox\Saved\Repositories\Eloquent\SavedListRepository;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;
use Tests\TestCase;

class SavedListRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(SavedListRepositoryInterface::class);
        $this->assertInstanceOf(SavedListRepository::class, $repository);
        $this->markTestIncomplete('coming soon!');
    }
}

// end
