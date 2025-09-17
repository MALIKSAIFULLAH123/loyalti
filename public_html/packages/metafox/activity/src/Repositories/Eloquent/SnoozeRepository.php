<?php

namespace MetaFox\Activity\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Activity\Models\Snooze;
use MetaFox\Activity\Repositories\SnoozeRepositoryInterface;
use MetaFox\Activity\Support\Constants;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class SnoozeRepository.
 * @method Snooze find($id, $columns = ['*'])
 * @method Snooze getModel()
 * @method Snooze create($params = [])
 */
class SnoozeRepository extends AbstractRepository implements SnoozeRepositoryInterface
{
    public function model(): string
    {
        return Snooze::class;
    }

    public function deleteExpiredSnoozesNotHavingSubscription(): void
    {
        $this->getModel()
            ->expired()
            ->subscription()
            ->select(['s.*'])
            ->from('activity_snoozes', 's')
            ->whereNull('a.id')
            ->where('s.is_snooze_forever', 0)
            ->delete();
    }

    public function deleteExpiredSnoozesHavingSubscription(): void
    {
        $snoozes = $this->getModel()
            ->expired()
            ->subscription()
            ->select(['s.*'])
            ->from('activity_snoozes', 's')
            ->whereNotNull('a.id')
            ->where('s.is_snooze_forever', 0)
            ->get();
        if (!empty($snoozes)) {
            foreach ($snoozes as $snooze) {
                $snooze->delete();
            }
        }
    }

    public function getSnoozes(User $context, array $params): Paginator
    {
        $search = Arr::get($params, 'q');
        $type   = Arr::get($params, 'type');
        $limit  = Arr::get($params, 'limit') ?: Pagination::DEFAULT_ITEM_PER_PAGE;

        $query = $this->getModel()->newQuery()
            ->where('user_id', $context->entityId());

        if ($search) {
            $searchScope = new SearchScope($search, ['user_entities.name', 'user_entities.user_name']);

            $searchScope->setJoinedTable('user_entities')
                ->setJoinedField('id')
                ->setTableField('owner_id');

            $query = $query->addScope($searchScope);
        }

        if ($type) {
            $query = $query->where('owner_type', '=', $type);
        }

        return $query
            ->orderBy('created_at', 'DESC')
            ->paginate($limit);
    }

    public function snooze(User $user, User $owner, int $snoozeDay = Constants::DEFAULT_SNOOZE_DAYS): Snooze
    {
        $params = ['snooze_until' => Carbon::now()->addDays($snoozeDay)];

        return $this->createOrUpdateSnooze($user, $owner, $params);
    }

    public function snoozeForever(User $user, User $owner): Snooze
    {
        $params = ['is_snooze_forever' => 1];

        return $this->createOrUpdateSnooze($user, $owner, $params);
    }

    protected function createOrUpdateSnooze(User $context, User $owner, array $attributes): Snooze
    {
        $params = [
            'user_id'           => $context->entityId(),
            'user_type'         => $context->entityType(),
            'owner_id'          => $owner->entityId(),
            'owner_type'        => $owner->entityType(),
            'snooze_until'      => Arr::get($attributes, 'snooze_until'),
            'is_snooze_forever' => Arr::get($attributes, 'is_snooze_forever') ?: 0,
        ];

        $snooze = $this->getModel()->firstOrNew([
            'user_id'  => $context->entityId(),
            'owner_id' => $owner->entityId(),
        ]);

        $snooze->fill($params)->save();

        return $snooze;
    }

    public function unSnooze(User $user, User $owner): Snooze
    {
        $snooze = $this->getModel()->where([
            'user_id'  => $user->entityId(),
            'owner_id' => $owner->entityId(),
        ])->first();

        if (!$snooze instanceof Snooze) {
            abort(400, __p('activity::validation.cannot_snooze_user'));
        }

        $snooze->delete();

        return $snooze;
    }
}
