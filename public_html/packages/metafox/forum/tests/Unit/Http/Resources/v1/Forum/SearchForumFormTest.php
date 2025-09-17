<?php

namespace MetaFox\Forum\Tests\Unit\Http\Resources\v1\Forum;

use MetaFox\Forum\Http\Resources\v1\Forum\SearchForumForm as Form;
use MetaFox\Forum\Models\Forum as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Forum\Http\Resources\v1\Forum\SearchForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class SearchForumFormTest.
 */
class SearchForumFormTest extends TestCase
{
    public function testSearchForumForm()
    {
        $this->asAdminUser();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
