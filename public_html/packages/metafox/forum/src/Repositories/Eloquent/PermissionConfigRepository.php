<?php

namespace MetaFox\Forum\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Repositories\ModeratorRepositoryInterface;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Forum\Repositories\PermissionConfigRepositoryInterface;
use MetaFox\Forum\Models\PermissionConfig;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class PermissionConfigRepository
 *
 */
class PermissionConfigRepository extends AbstractRepository implements PermissionConfigRepositoryInterface
{
    public function model()
    {
        return PermissionConfig::class;
    }

    public function getConfigs(int $forumId): array
    {
        return $this->getModel()->newQuery()
            ->where([
                'forum_id' => $forumId,
            ])
            ->get()
            ->keyBy('permission_name')
            ->map(function () {
                return true;
            })
            ->toArray();
    }

    public function updateConfigs(User $user, Forum $forum, array $configs): bool
    {
        $moderatorIds = Arr::pull($configs, 'moderator_ids', []);

        $params = $this->handleUpdateConfigs($forum, $configs);

        resolve(ModeratorRepositoryInterface::class)->setupModerators($user, $forum, $moderatorIds, $params);

        LoadReduce::flush();

        return true;
    }

    protected function handleUpdateConfigs(Forum $forum, array $configs): array
    {
        $current = array_keys($this->getConfigs($forum->entityId()));
        $submitted = array_keys($configs);
        $inserted = array_diff($submitted, $current);
        $deleted  = array_diff($current, $submitted);
        $kept     = array_intersect($current, $submitted);

        if (count($inserted)) {
            $maps = array_map(function ($value) use ($forum) {
                return [
                    'forum_id' => $forum->entityId(),
                    'permission_name' => $value
                ];
            }, $inserted);

            PermissionConfig::query()->upsert($maps, ['forum_id', 'permission_name']);
        }

        if (count($deleted)) {
            PermissionConfig::query()
                ->where('forum_id', '=', $forum->entityId())
                ->whereIn('permission_name', $deleted)
                ->delete();
        }

        return [
            'inserted' => $inserted,
            'kept'     => $kept,
            'deleted'  => $deleted,
        ];
    }
}
