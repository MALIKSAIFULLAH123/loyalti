<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent;

use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use Tests\TestCase;

class PageRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(PageRepositoryInterface::class);
        $this->assertInstanceOf(PageRepository::class, $repository);
        $this->markTestIncomplete('coming soon!');
    }
}

// end
