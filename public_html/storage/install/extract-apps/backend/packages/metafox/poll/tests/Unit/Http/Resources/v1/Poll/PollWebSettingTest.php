<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Poll\Tests\Unit\Http\Resources\v1\Poll;

use MetaFox\Poll\Http\Resources\v1\Poll\WebSetting as Setting;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------
 *  Poll Web Resource Config Test
 * --------------------------------------------------------------------------
 *  stub: /packages/resources/resource_setting_test.stub.
 */

/**
 * Class PollWebSettingTest.
 */
class PollWebSettingTest extends TestCase
{
    public function testResourceSettingToArray()
    {
        $data = (new Setting(null))->toArray(null);
        $this->assertIsArray($data);
    }
}
