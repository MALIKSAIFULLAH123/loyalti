<?php

namespace MetaFox\Marketplace\Tests\Unit\Repositories\Eloquent;

use MetaFox\Marketplace\Repositories\Eloquent\ImageRepository;
use MetaFox\Marketplace\Repositories\ImageRepositoryInterface;
use Tests\TestCase;

class ImageRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(ImageRepositoryInterface::class);
        $this->assertInstanceOf(ImageRepository::class, $repository);
    }
}

// end
