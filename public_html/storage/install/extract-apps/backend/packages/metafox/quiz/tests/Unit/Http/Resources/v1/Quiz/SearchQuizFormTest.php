<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Resources\v1\Quiz;

use MetaFox\Quiz\Http\Resources\v1\Quiz\SearchQuizForm as Form;
use MetaFox\Quiz\Models\Quiz as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Quiz\Http\Resources\v1\Quiz\SearchForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class SearchQuizFormTest.
 */
class SearchQuizFormTest extends TestCase
{
    public function testSearchQuizForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
