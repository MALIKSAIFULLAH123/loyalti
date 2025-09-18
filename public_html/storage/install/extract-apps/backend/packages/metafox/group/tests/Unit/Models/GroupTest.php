<?php

namespace MetaFox\Group\Tests\Unit\Models;

use MetaFox\Group\Models\Group;
use Tests\TestCases\TestContentModel;

/**
 * @group resource.content
 */
class GroupTest extends TestContentModel
{
    public function modelName(): string
    {
        return Group::class;
    }
}

// end
