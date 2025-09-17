<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionPackage\Admin;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin\CreateSubscriptionPackageForm as Form;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin\CreateForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class CreateSubscriptionPackageFormTest.
 */
class CreateSubscriptionPackageFormTest extends TestCase
{
    public function testCreateSubscriptionPackageForm()
    {
        $this->asAdminUser();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
