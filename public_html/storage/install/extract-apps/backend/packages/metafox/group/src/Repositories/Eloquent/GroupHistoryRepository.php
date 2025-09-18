<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupHistory;
use MetaFox\Group\Notifications\UpdateInformationNotification;
use MetaFox\Group\Repositories\GroupHistoryRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\Support;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class GroupHistoryRepository
 *
 * @method GroupHistory find($id, $columns = ['*'])
 * @method GroupHistory getModel()
 */
class GroupHistoryRepository extends AbstractRepository implements GroupHistoryRepositoryInterface
{
    public function model(): string
    {
        return GroupHistory::class;
    }

    /**
     * @return MemberRepositoryInterface
     */
    private function groupMemberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    public function createHistory(User $context, Group $group, array $attributes): void
    {
        $data = [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'group_id'  => $group->entityId(),
            'type'      => Arr::get($attributes, 'type', Support::UPDATE_GROUP_NAME_TYPE),
            'extra'     => json_encode($attributes),
        ];

        $model = $this->getModel()->fill($data);
        $model->save();

        $this->sentNotification($model);
    }


    /**
     * @inheritDoc
     */
    public function sentNotification(GroupHistory $model): void
    {
        if ($model->type != Support::UPDATE_GROUP_NAME_TYPE) {
            return;
        }

        $members = $this->groupMemberRepository()->getGroupMembers($model->group_id);

        $notification = new UpdateInformationNotification($model);

        foreach ($members as $member) {
            if ($member->userId() == $model->userId()) {
                continue;
            }

            if (!$notifiable = $member?->user) {
                continue;
            }

            $notificationParams = [$notifiable, $notification];
            Notification::send(...$notificationParams);
        }
    }
}
