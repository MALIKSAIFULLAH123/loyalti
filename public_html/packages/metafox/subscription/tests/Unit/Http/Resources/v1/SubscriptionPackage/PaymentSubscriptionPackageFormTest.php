<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionPackage;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\PaymentSubscriptionPackageForm as Form;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\PaymentForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class PaymentSubscriptionPackageFormTest.
 */
class PaymentSubscriptionPackageFormTest extends TestCase
{
    public function testPaymentSubscriptionPackageForm()
    {
        $this->markTestIncomplete();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
