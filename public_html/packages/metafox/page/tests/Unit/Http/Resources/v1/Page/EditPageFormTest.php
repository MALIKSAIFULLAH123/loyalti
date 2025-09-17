<?php

namespace MetaFox\Page\Tests\Unit\Http\Resources\v1\Page;

use MetaFox\Page\Http\Resources\v1\Page\EditPageForm as Form;
use MetaFox\Page\Models\Page as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Page\Http\Resources\v1\Page\EditForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class EditPageFormTest.
 */
class EditPageFormTest extends TestCase
{
    public function testEditPageForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
