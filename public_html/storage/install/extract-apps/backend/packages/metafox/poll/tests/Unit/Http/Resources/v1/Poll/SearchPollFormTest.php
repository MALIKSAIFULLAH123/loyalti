<?php

namespace MetaFox\Poll\Tests\Unit\Http\Resources\v1\Poll;

use MetaFox\Poll\Http\Resources\v1\Poll\SearchPollForm as Form;
use MetaFox\Poll\Models\Poll as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Poll\Http\Resources\v1\Poll\SearchForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class SearchPollFormTest.
 */
class SearchPollFormTest extends TestCase
{
    public function testSearchPollForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
