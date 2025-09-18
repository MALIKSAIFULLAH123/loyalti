<?php

namespace MetaFox\Saved\Tests\Unit\Support\Facade;

use MetaFox\Saved\Contracts\Support\SavedTypeContract;
use MetaFox\Saved\Support\Facade\SavedType;
use Tests\TestCase;

class SavedTypeTest extends TestCase
{
    public function testInstance()
    {
        $service = SavedType::getFacadeRoot();
        $this->assertInstanceOf(SavedTypeContract::class, $service);
    }
}
