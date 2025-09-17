<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Marketplace\Tests\Unit\Http\Resources\v1\Listing;

use MetaFox\Marketplace\Http\Resources\v1\Listing\WebSetting as Setting;
use MetaFox\Marketplace\Models\Listing as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------
 *  Listing Web Resource Config Test
 * --------------------------------------------------------------------------
 *  stub: /packages/resources/resource_setting_test.stub.
 */

/**
 * Class ListingWebSettingTest.
 */
class ListingWebSettingTest extends TestCase
{
    public function testResourceSettingToArray()
    {
        $data = (new Setting(Model::class, 'marketplace', 'marketplace', Model::ENTITY_TYPE, true))->toArray(null);
        $this->assertIsArray($data);
    }
}
