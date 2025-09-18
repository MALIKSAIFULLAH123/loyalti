<?php

namespace MetaFox\Marketplace\Tests\Unit\Models;

use MetaFox\Marketplace\Models\Listing;
use Tests\TestCases\TestContentModel;

/**
 * @group resource.content
 */
class ListingTest extends TestContentModel
{
    public function modelName(): string
    {
        return Listing::class;
    }
}
