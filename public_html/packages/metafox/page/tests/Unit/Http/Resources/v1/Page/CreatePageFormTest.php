<?php

namespace MetaFox\Page\Tests\Unit\Http\Resources\v1\Page;

use MetaFox\Page\Http\Resources\v1\Page\CreatePageForm as Form;
use MetaFox\Page\Models\Page as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Page\Http\Resources\v1\Page\CreateForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class CreatePageFormTest.
 */
class CreatePageFormTest extends TestCase
{
    public function testCreatePageForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
