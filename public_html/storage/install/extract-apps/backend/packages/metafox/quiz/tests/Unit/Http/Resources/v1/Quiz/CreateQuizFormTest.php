<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Resources\v1\Quiz;

use MetaFox\Quiz\Http\Resources\v1\Quiz\CreateQuizForm as Form;
use MetaFox\Quiz\Models\Quiz as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Quiz\Http\Resources\v1\Quiz\CreateForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class CreateQuizFormTest.
 */
class CreateQuizFormTest extends TestCase
{
    public function testCreateQuizForm()
    {
        $this->asAdminUser();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
