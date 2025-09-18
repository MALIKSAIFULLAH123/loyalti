<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Resources\v1\Listing;

use MetaFox\Form\AbstractForm;
use MetaFox\Marketplace\Http\Resources\v1\Listing\UpdateListingForm as Form;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Marketplace\Http\Resources\v1\Listing\EditForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class EditListingFormTest.
 */
class EditListingFormTest extends TestCase
{
    public function testEditListingForm()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->be($user);
        $listing = Model::factory()->setUser($user)->setOwner($user)->create();
        $form    = new Form($listing);
        $this->assertInstanceOf(AbstractForm::class, $form);
    }
}
