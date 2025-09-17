<?php

namespace MetaFox\Subscription\Tests\Unit\Http\Resources\v1\SubscriptionPackage\Admin;

use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin\SearchSubscriptionPackageForm as Form;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin\SearchForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class SearchSubscriptionPackageFormTest.
 */
class SearchSubscriptionPackageFormTest extends TestCase
{
    public function testSearchSubscriptionPackageForm()
    {
        $form = new Form(new Model());
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
