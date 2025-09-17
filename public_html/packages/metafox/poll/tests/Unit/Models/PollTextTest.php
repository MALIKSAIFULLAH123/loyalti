<?php
/**
 * @author developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Poll\Tests\Unit\Models;

use MetaFox\Poll\Models\PollText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class PollTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return PollText::class;
    }
}
