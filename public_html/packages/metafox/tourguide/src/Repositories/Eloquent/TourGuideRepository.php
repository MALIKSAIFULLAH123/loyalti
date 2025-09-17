<?php

namespace MetaFox\TourGuide\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\TourGuide\Repositories\HiddenRepositoryInterface;
use MetaFox\TourGuide\Repositories\TourGuideRepositoryInterface;
use MetaFox\TourGuide\Models\TourGuide;
use MetaFox\TourGuide\Supports\Browse\Scopes\PrivacyScope;
use MetaFox\TourGuide\Supports\Constants;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class TourGuideRepository.
 */
class TourGuideRepository extends AbstractRepository implements TourGuideRepositoryInterface
{
    public function model(): string
    {
        return TourGuide::class;
    }

    public function getActions(User $context, array $params): array
    {
        $actions  = [];
        $pageName = Arr::get($params, 'page_name');

        $tourGuide = $this->getLatestTourGuide($context, $pageName);

        if ($tourGuide instanceof TourGuide) {
            $actions[] = [
                'name'   => Constants::START_TOUR_ACTION_NAME,
                'label'  => __p('tourguide::web.tourguide_start_tour'),
                'icon'   => Constants::START_TOUR_ICON,
                'value'  => 'tourguide/startTour',
                'params' => [
                    'payload' => [
                        'id' => $tourGuide->entityId(),
                    ],
                ],
            ];
        }

        return [
            'tourguide_id' => $tourGuide?->entityId(),
            'menu'         => $actions,
        ];
    }

    public function getLatestTourGuide(User $context, string $pageName): ?TourGuide
    {
        return $this->buildTourGuideQuery($context, $pageName)
            ->orderByDesc('tour_guides.created_at')
            ->first();
    }

    protected function buildTourGuideQuery(User $context, string $pageName): Builder
    {
        $query = $this->getModel()
            ->newQuery()
            ->where([
                'page_name' => $pageName,
                'is_active' => true,
            ])
            ->whereHas('steps', function ($q) {
                $q->where('tour_guide_steps.is_active', true);
            });

        $privacyScope = new PrivacyScope();
        $privacyScope->setUser($context);

        $query->addScope($privacyScope);

        if (!$context->isGuest()) {
            $query->whereNotIn('id', $this->completionRepository()->getTourGuideIdBuilder($context));
        }

        return $query;
    }

    public function createTourGuide(User $context, array $params): TourGuide
    {
        $params = array_merge($params, [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'is_auto'   => (bool) Arr::get($params, 'is_auto'),
            'is_active' => (bool) Arr::get($params, 'is_active'),
        ]);

        return $this->getModel()->newQuery()->create($params);
    }

    public function updateTourGuide(int $tourGuideId, array $params): TourGuide
    {
        $tourGuide = $this->find($tourGuideId);

        $tourGuide->fill($params);

        $tourGuide->save();

        return $tourGuide;
    }

    protected function completionRepository(): HiddenRepositoryInterface
    {
        return resolve(HiddenRepositoryInterface::class);
    }
}
