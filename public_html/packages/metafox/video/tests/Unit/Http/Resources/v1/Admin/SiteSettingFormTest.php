<?php

namespace MetaFox\Video\Tests\Unit\Http\Resources\v1\Admin;

use MetaFox\Video\Http\Resources\v1\Admin\SiteSettingForm as Form;
use MetaFox\Video\Models\Video as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Video\Http\Resources\v1\Video\SiteSettingForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class SiteSettingFormTest.
 */
class SiteSettingFormTest extends TestCase
{
    public function testVideoSettingVideoForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
