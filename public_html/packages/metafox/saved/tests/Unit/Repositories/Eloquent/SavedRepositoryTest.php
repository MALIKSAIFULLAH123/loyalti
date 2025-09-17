<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent;

use MetaFox\Saved\Repositories\Eloquent\SavedRepository;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;
use Tests\TestCase;

class SavedRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(SavedRepositoryInterface::class);
        $this->assertInstanceOf(SavedRepository::class, $repository);
        $this->markTestIncomplete('coming soon!');
    }
}

// end
