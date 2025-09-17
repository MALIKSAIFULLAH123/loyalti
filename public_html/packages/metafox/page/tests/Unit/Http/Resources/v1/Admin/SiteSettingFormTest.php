<?php

namespace MetaFox\Page\Tests\Unit\Http\Resources\v1\Admin;

use MetaFox\Page\Http\Resources\v1\Admin\SiteSettingForm as Form;
use MetaFox\Page\Models\Page as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Page\Http\Resources\v1\Admin\PageSiteSettingForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class PageSiteSettingFormTest.
 */
class SiteSettingFormTest extends TestCase
{
    public function testPageSiteSettingPageForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
