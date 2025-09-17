<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointSetting\Admin;

use MetaFox\ActivityPoint\Models\PointSetting as Model;
use MetaFox\ActivityPoint\Repositories\PointSettingRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Yup\Yup;

/**
 * Class UpdatePointSettingForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverType form
 * @driverName activitypoint_setting.update
 */
class UpdatePointSettingForm extends AbstractForm
{
    public function boot(PointSettingRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('activitypoint::phrase.change_point_setting'))
            ->action('/admincp/activitypoint/setting/' . $this->resource->entityId())
            ->asPut()
            ->setValue([
                'package'     => $this->resource->package_id,
                'role'        => $this->resource->role?->name,
                'description' => $this->resource->description,
                'points'      => $this->resource->points,
                'max_earned'  => $this->resource->max_earned,
                'period'      => $this->resource->period,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('package')
                ->label(__p('core::phrase.package_name'))
                ->disabled(),
            Builder::text('description')
                ->disabled()
                ->label(__p('core::phrase.action')),
            Builder::text('role')
                ->disabled()
                ->label(__p('core::phrase.role')),
            Builder::text('points')
                ->label(__p('activitypoint::phrase.earn_point'))
                ->yup(
                    Yup::number()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->min(0)
                        ->when(
                            Yup::when('max_earned')
                                ->is('$exists')
                                ->then(Yup::number()
                                    ->max(['ref' => 'max_earned'])
                                    ->setError('typeError', __p('validation.numeric', ['attribute' => '${path}'])))
                        )
                        ->setError('typeError', __p('validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('max_earned')
                ->label(__p('activitypoint::phrase.max_earn_point'))
                ->disabled(in_array('max_earned', $this->resource->disabledFields))
                ->yup(
                    Yup::number()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->min(0)
                        ->setError('typeError', __p('validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('period')
                ->label(__p('activitypoint::phrase.period_in_day'))
                ->description(__p('activitypoint::phrase.point_setting_period_description'))
                ->disabled(in_array('period', $this->resource->disabledFields))
                ->yup(
                    Yup::number()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->min(0)
                        ->setError('typeError', __p('validation.numeric', ['attribute' => '${path}']))
                ),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.save_changes')),
                Builder::cancelButton(),
            );
    }
}
