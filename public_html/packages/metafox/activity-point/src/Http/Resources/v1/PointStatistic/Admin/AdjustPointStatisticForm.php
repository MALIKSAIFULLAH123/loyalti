<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointStatistic\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PointStatistic as Model;
use MetaFox\ActivityPoint\Repositories\PointStatisticRepositoryInterface;
use MetaFox\ActivityPoint\Support\ActivityPoint;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

/**
 * Class AdjustPointStatisticForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName activitypoint_statistic.adjust
 * @driverType form
 */
class AdjustPointStatisticForm extends AbstractForm
{
    protected int $type;
    protected array  $userIds;

    public function boot(Request $request, PointStatisticRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
        $params         = $request->all();
        $this->type     = (int) Arr::get($params, 'type', ActivityPoint::TYPE_RETRIEVED);
        $this->userIds  = [$id];
    }

    protected function prepare(): void
    {
        $this->title(__p('activitypoint::phrase.adjust_point'))
            ->action(apiUrl('admin.activitypoint.statistic.adjust'))
            ->asPut()
            ->setValue($this->getValues());
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            $this->buildTypeField(),
            $this->buildTargetField(),
            $this->buildAmountField(),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('activitypoint::phrase.adjust')),
                Builder::cancelButton(),
            );
    }

    /**
     * @return array<int, mixed>
     */
    private function getTypeOptions(): array
    {
        return [
            [
                'label' => __p('activitypoint::phrase.send_points'),
                'value' => ActivityPoint::TYPE_RECEIVED,
            ],
            [
                'label' => __p('activitypoint::phrase.reduce_points'),
                'value' => ActivityPoint::TYPE_RETRIEVED,
            ],
        ];
    }

    protected function getTargetLabelFromType(string $type): string
    {
        $labels = [
            ActivityPoint::TYPE_RETRIEVED => __p('activitypoint::phrase.reduce_from'),
            ActivityPoint::TYPE_RECEIVED  => __p('activitypoint::phrase.sent_to'),
        ];

        return Arr::get($labels, $type, '');
    }

    protected function buildAmountField(): AbstractField
    {
        $context = user();

        $field   = Builder::text('amount')
            ->required()
            ->maxLength(10) //limit is two billion
            ->label(__p('activitypoint::phrase.points'));

        $yup = Yup::number()
            ->int(__p('core::validation.integer', ['attribute' => '${path}']))
            ->min(1)
            ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']));

        $whenYup = Yup::when('type')
            ->is(ActivityPoint::TYPE_RETRIEVED);

        $thenYup = Yup::number()
            ->int(__p('core::validation.integer', ['attribute' => '${path}']))
            ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))
            ->min(1);

        if (!$context->hasSuperAdminRole()) {
            $maxPoint     = (int) $context->getPermissionValue('activitypoint.maximum_activity_points_admin_can_adjust');
            $currentPoint = $this->resource?->current_points;

            $yup->max($maxPoint, __p('activitypoint::validation.maximum_points_for_sending'));
            $warning = __p('activitypoint::phrase.maximum_points_for_sending', ['point' => $maxPoint]);

            if ($currentPoint > 0) {
                $thenYup->max($currentPoint, __p('activitypoint::validation.maximum_points_for_reducing'));
                $warning = $warning . __p('activitypoint::phrase.maximum_points_for_reducing', ['point' => $currentPoint]);
            }

            $field->warning($warning);
        }

        return $field->yup($yup->when($whenYup->then($thenYup)));
    }

    protected function getValues(): array
    {
        return [
            'type'        => $this->type,
            'amount'      => '1',
            'target'      => $this->resource->userEntity?->display_name,
            'user_ids'    => $this->userIds,
        ];
    }

    protected function buildTargetField(): AbstractField
    {
        return Builder::text('target')
            ->disabled()
            ->label($this->getTargetLabelFromType($this->type));
    }

    protected function buildTypeField(): AbstractField
    {
        return Builder::dropdown('type')
            ->options($this->getTypeOptions())
            ->label(__p('activitypoint::phrase.action'))
            ->yup(
                Yup::number()
                    ->required()
                    ->setError('typeError', __p('validation.field_is_a_required_field', [
                        'field' => __p('activitypoint::phrase.action'),
                    ])),
            );
    }
}
