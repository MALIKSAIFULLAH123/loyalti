<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionPackage\Admin;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin\EditSubscriptionPackageForm as Form;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin\EditForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class EditSubscriptionPackageFormTest.
 */
class EditSubscriptionPackageFormTest extends TestCase
{
    public function testEditSubscriptionPackageForm()
    {
        $this->asAdminUser();
        $form = new Form(SubscriptionPackage::factory()->makeOne());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
