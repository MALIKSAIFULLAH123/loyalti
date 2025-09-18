<?php

namespace MetaFox\Quiz\Tests\Unit\Repositories\Eloquent;

use MetaFox\Quiz\Repositories\Eloquent\QuizRepository;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use Tests\TestCase;

class QuizRepositoryTest extends TestCase
{
    public function testExample()
    {
        $repository = resolve(QuizRepositoryInterface::class);
        $this->assertInstanceOf(QuizRepository::class, $repository);
    }
}

// end
