<?php

namespace MetaFox\Blog\Tests\Unit\Http\Resources\v1\Blog;

use MetaFox\Blog\Http\Resources\v1\Blog\SearchBlogForm as Form;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Blog\Http\Resources\v1\Blog\SearchBlogForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class SearchFormTest.
 */
class SearchFormTest extends TestCase
{
    public function testSearchForm()
    {
        $form = new Form(null);
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
