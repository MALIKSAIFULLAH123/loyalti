<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent;

use MetaFox\Page\Repositories\Eloquent\PageMemberRepository;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use Tests\TestCase;

class PageMemberRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(PageMemberRepositoryInterface::class);
        $this->assertInstanceOf(PageMemberRepository::class, $repository);
        $this->markTestIncomplete('coming soon!');
    }
}

// end
