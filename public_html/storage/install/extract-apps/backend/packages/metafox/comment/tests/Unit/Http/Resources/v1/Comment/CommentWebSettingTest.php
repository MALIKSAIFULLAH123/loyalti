<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Comment\Tests\Unit\Http\Resources\v1\Comment;

use MetaFox\Comment\Http\Resources\v1\Comment\WebSetting as Setting;
use MetaFox\Comment\Models\Comment as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------
 *  Comment Web Resource Config Test
 * --------------------------------------------------------------------------
 *  stub: /packages/resources/resource_setting_test.stub.
 */

/**
 * Class CommentWebSettingTest.
 */
class CommentWebSettingTest extends TestCase
{
    public function testResourceSettingToArray()
    {
        $data = (new Setting(
            Model::ENTITY_TYPE,
            Model::ENTITY_TYPE,
            Model::ENTITY_TYPE,
            Model::ENTITY_TYPE,
            false
        ))
            ->toArray(null);
        $this->assertIsArray($data);
    }
}
