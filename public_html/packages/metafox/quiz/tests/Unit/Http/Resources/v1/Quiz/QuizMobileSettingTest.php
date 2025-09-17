<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Quiz\Tests\Unit\Http\Resources\v1\Quiz;

use MetaFox\Quiz\Http\Resources\v1\Quiz\MobileSetting as Setting;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------
 *  Quiz Mobile Resource Config Test
 * --------------------------------------------------------------------------
 *  stub: /packages/resources/resource_setting_test.stub.
 */

/**
 * Class QuizMobileSettingTest.
 */
class QuizMobileSettingTest extends TestCase
{
    public function testResourceSettingToArray()
    {
        $data = (new Setting(null))->toArray(null);
        $this->assertIsArray($data);
    }
}
