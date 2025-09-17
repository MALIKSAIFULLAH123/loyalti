<?php

namespace MetaFox\Report\Http\Resources\v1\ReportItem\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Report\Models\ReportItem as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchReportItemForm.
 * @property Model $resource
 */
class SearchReportItemForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/report')
            ->acceptPageParams(['q']);
    }

    protected function initialize(): void
    {
        $itemTitle = $this->resource?->item?->toTitle() != null ?
            __p('report::phrase.report_item_header', [
                'item' => $this->resource->item->toTitle(),
            ]) :
            __p('core::phrase.deleted_item');

        if (method_exists($this->resource->item, 'toReportHeader')) {
            $itemTitle = $this->resource->item->toReportHeader();
        }

        $basic = $this->addBasic()->asHorizontal();

        $basic->addFields(
            Builder::typography()
                ->tagName('h3')
                ->plainText($itemTitle),
        );
    }
}
