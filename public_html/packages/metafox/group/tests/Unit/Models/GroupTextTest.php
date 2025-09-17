<?php

namespace MetaFox\Group\Tests\Unit\Models;

use MetaFox\Group\Models\GroupText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class GroupTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return GroupText::class;
    }
}

// end
