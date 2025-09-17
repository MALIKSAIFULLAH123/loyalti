<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointStatistic\Admin;

use MetaFox\ActivityPoint\Models\PointStatistic as Model;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchPointStatisticForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName activitypoint_statistic.search.admin
 * @driverType form
 */
class SearchPointStatisticForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('activitypoint::phrase.point_members'))
            ->action('/activitypoint/statistic')
            ->acceptPageParams(['q', 'sort', 'sort_type', 'page', 'limit'])
            ->setValue(['q' => '']);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal();
        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm()
                ->placeholder(__p('activitypoint::phrase.enter_member_name'))
                ->yup(Yup::string()),
            Builder::submit()
                ->forAdminSearchForm(),
        );
    }
}
