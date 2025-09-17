<?php

namespace MetaFox\Poll\Tests\Unit\Http\Resources\v1\Poll\Admin;

use MetaFox\Poll\Http\Resources\v1\Poll\Admin\SiteSettingForm as Form;
use MetaFox\Poll\Models\Poll as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Poll\Http\Resources\v1\Poll\Admin\SiteSettingForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class PollSettingFormTest.
 */
class PollSettingFormTest extends TestCase
{
    public function testPollSettingPollForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
