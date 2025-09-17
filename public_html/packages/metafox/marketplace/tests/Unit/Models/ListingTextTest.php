<?php

namespace MetaFox\Marketplace\Tests\Unit\Models;

use MetaFox\Marketplace\Models\Text;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class ListingTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return Text::class;
    }
}
