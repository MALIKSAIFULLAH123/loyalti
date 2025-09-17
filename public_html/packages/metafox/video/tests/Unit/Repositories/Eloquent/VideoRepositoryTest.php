<?php

namespace MetaFox\Video\Tests\Unit\Repositories\Eloquent;

use MetaFox\Video\Repositories\Eloquent\VideoRepository;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use Tests\TestCase;

class VideoRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(VideoRepositoryInterface::class);
        $this->assertInstanceOf(VideoRepository::class, $repository);
        $this->markTestIncomplete('coming soon!');
    }
}

// end
