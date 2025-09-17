<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Forum\Tests\Unit\Http\Resources\v1\Forum;

use MetaFox\Forum\Http\Resources\v1\Forum\ForumMobileSetting as Setting;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------
 *  Forum Mobile Resource Config Test
 * --------------------------------------------------------------------------
 *  stub: /packages/resources/resource_setting_test.stub.
 */

/**
 * Class ForumMobileSettingTest.
 */
class ForumMobileSettingTest extends TestCase
{
    public function testResourceSettingToArray()
    {
        $this->markTestSkipped();
        $data = (new Setting(null))->toArray(null);
        $this->assertIsArray($data);
    }
}
