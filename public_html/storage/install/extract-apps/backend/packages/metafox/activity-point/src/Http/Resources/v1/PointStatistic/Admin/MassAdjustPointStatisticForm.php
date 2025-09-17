<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointStatistic\Admin;

use Illuminate\Http\Request;
use MetaFox\ActivityPoint\Models\PointStatistic as Model;
use MetaFox\ActivityPoint\Repositories\PointStatisticRepositoryInterface;
use MetaFox\ActivityPoint\Support\ActivityPoint;
use MetaFox\ActivityPoint\Support\Facade\ActivityPoint as ActivityPointFacade;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class MassAdjustPointStatisticForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName activitypoint_statistic.mass_adjust
 * @driverType form
 */
class MassAdjustPointStatisticForm extends AdjustPointStatisticForm
{
    private array $name;

    public function boot(Request $request, PointStatisticRepositoryInterface $repository, ?int $id = null): void
    {
        $this->userIds = json_decode($request->get('user_ids', []));
        $this->type    = $request->get('type', ActivityPoint::TYPE_RETRIEVED);
        $this->name    = [];
        if (!empty($this->userIds)) {
            $user       = UserEntity::getByIds($this->userIds);
            $this->name = $user->pluck('name')->toArray();
        }
    }

    protected function buildTargetField(): AbstractField
    {
        return Builder::tags('target')
            ->disabled()
            ->multiple(true)
            ->label($this->getTargetLabelFromType($this->type));
    }

    protected function buildTypeField(): AbstractField
    {
        $currentPoint = ActivityPointFacade::getMinPointByIds($this->userIds);
        return Builder::choice('type')
            ->options($this->getTypeOptions($currentPoint))
            ->label(__p('activitypoint::phrase.action'));
    }

    /**
     * @return array<int, mixed>
     */
    private function getTypeOptions(int $currentPoint): array
    {
        $data = [
            [
                'label' => __p('activitypoint::phrase.send_points'),
                'value' => ActivityPoint::TYPE_RECEIVED,
            ],
        ];

        if ($currentPoint) {
            $data[] = [
                'label' => __p('activitypoint::phrase.reduce_points'),
                'value' => ActivityPoint::TYPE_RETRIEVED,
            ];
        }

        return $data;
    }

    protected function getValues(): array
    {
        return [
            'type'        => $this->type,
            'amount'      => '1',
            'target'      => $this->name,
            'user_ids'    => $this->userIds,
        ];
    }
}
