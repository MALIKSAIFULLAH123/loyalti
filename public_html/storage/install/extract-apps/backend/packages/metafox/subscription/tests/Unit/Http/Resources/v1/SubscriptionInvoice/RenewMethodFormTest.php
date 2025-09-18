<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionInvoice;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\RenewMethodForm as Form;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\RenewMethodForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class RenewMethodFormTest.
 */
class RenewMethodFormTest extends TestCase
{
    public function testRenewMethodSubscriptionInvoiceForm()
    {
        $this->markTestIncomplete();

        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
