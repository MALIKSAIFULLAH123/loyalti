<?php

namespace MetaFox\Report\Http\Resources\v1\ReportOwner;

use MetaFox\Report\Http\Resources\v1\ReportItem\StoreReportItemMobileForm;
use MetaFox\Report\Models\ReportOwner as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateReportOwnerForm.
 * @property ?Model $resource
 */
class StoreReportOwnerMobileForm extends StoreReportItemMobileForm
{
    /** @var bool */
    protected $isEdit = false;

    protected function prepare(): void
    {
        parent::prepare();
        $this->action(apiUrl('report-owner.store'));
    }
}
