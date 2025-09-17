<?php

namespace MetaFox\Report\Http\Resources\v1\ReportOwner;

use MetaFox\Report\Http\Resources\v1\ReportItem\StoreReportItemForm;
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
class StoreReportItemReportOwnerForm extends StoreReportItemForm
{
    /** @var bool */
    protected $isEdit = false;

    protected function prepare(): void
    {
        parent::prepare();
        $this->action(url_utility()->makeApiUrl('report-owner'));
    }
}
