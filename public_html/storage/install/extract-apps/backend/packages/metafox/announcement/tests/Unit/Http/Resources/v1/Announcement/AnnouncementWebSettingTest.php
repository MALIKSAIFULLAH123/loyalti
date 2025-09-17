<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Announcement\Tests\Unit\Http\Resources\v1\Announcement;

use MetaFox\Announcement\Http\Resources\v1\Announcement\WebSetting as Setting;
use MetaFox\Announcement\Models\Announcement as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------
 *  Announcement Web Resource Config Test
 * --------------------------------------------------------------------------
 *  stub: /packages/resources/resource_setting_test.stub.
 */

/**
 * Class AnnouncementWebSettingTest.
 */
class AnnouncementWebSettingTest extends TestCase
{
    public function testResourceSettingToArray()
    {
        $setting = new Setting(
            null,
            Model::ENTITY_TYPE,
            Model::ENTITY_TYPE,
            Model::ENTITY_TYPE,
            false
        );

        $data = $setting->toArray(null);
        $this->assertIsArray($data);
    }
}
