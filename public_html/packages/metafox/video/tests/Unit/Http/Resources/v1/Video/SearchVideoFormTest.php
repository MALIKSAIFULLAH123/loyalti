<?php

namespace MetaFox\Video\Tests\Unit\Http\Resources\v1\Video;

use MetaFox\Video\Http\Resources\v1\Video\SearchVideoForm as Form;
use MetaFox\Video\Models\Video as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Video\Http\Resources\v1\Video\SearchForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class SearchVideoFormTest.
 */
class SearchVideoFormTest extends TestCase
{
    public function testSearchVideoForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
