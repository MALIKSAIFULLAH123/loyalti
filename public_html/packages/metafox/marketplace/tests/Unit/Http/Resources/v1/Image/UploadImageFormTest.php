<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Resources\v1\Image;

use MetaFox\Marketplace\Http\Resources\v1\Image\UploadImageForm as Form;
use MetaFox\Marketplace\Models\Image as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Marketplace\Http\Resources\v1\Image\UploadForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class UploadImageFormTest.
 */
class UploadImageFormTest extends TestCase
{
    public function testUploadImageForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
