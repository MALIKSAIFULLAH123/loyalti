<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Resources\v1\Listing;

use MetaFox\Marketplace\Http\Resources\v1\Listing\StoreListingForm as Form;
use MetaFox\Marketplace\Models\Listing as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Marketplace\Http\Resources\v1\Listing\CreateForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class CreateListingFormTest.
 */
class CreateListingFormTest extends TestCase
{
    public function testCreateListingForm()
    {
        $this->markTestIncomplete();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
