<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Resources\v1\Listing;

use MetaFox\Marketplace\Http\Resources\v1\Listing\SearchListingForm as Form;
use MetaFox\Marketplace\Models\Listing as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Marketplace\Http\Resources\v1\Listing\SearchForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class SearchListingFormTest.
 */
class SearchListingFormTest extends TestCase
{
    public function testSearchListingForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
