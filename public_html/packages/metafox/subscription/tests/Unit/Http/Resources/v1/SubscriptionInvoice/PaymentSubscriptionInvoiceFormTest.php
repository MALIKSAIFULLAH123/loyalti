<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionInvoice;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\PaymentSubscriptionInvoiceForm as Form;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\PaymentForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class PaymentSubscriptionInvoiceFormTest.
 */
class PaymentSubscriptionInvoiceFormTest extends TestCase
{
    public function testPaymentSubscriptionInvoiceForm()
    {
        $this->asAdminUser();
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
