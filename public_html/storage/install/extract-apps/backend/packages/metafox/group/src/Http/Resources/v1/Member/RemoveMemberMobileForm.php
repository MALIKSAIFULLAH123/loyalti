<?php

namespace MetaFox\Group\Http\Resources\v1\Member;

use Exception;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Http\Requests\v1\Member\RemoveMemberFormRequest;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;

class RemoveMemberMobileForm extends AbstractForm
{
    /**
     * @var int
     */
    protected int $groupId;

    /**
     * @var int
     */
    protected int $userId;
    protected string $groupName;
    protected string $userName;

    protected GroupRepositoryInterface $groupRepository;

    public function boot(RemoveMemberFormRequest $request, GroupRepositoryInterface $groupRepository): void
    {
        $this->groupRepository = $groupRepository;
        $params                = $request->validated();

        $this->groupId   = Arr::get($params, 'group_id');
        $this->userId    = Arr::get($params, 'user_id');
        $this->groupName = $this->groupRepository->find($this->groupId)->toTitle();
        $userEntity      = UserEntity::getById($this->userId);
        $this->userName  = $userEntity->detail->full_name;
    }

    protected function prepare(): void
    {
        $this->action('group-member/remove-group-member')
            ->asDelete()
            ->title(__p('group::phrase.remove_member'))
            ->setValue([
                'group_id'          => $this->groupId,
                'user_id'           => $this->userId,
                'delete_activities' => 0,
            ]);
    }

    /**
     * @throws Exception
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::typography()
                ->color('text.hint')
                ->plainText(__p('group::phrase.remove_member_description', [
                    'user_name'  => $this->userName,
                    'group_name' => $this->groupName,
                ])),
            Builder::hidden('group_id'),
            Builder::hidden('user_id'),
            Builder::checkbox('delete_activities')
                ->multiple(false)
                ->label(__p('group::phrase.delete_recent_activity_title')),
            Builder::typography('description')
                ->color('text.hint')
                ->plainText(__p('group::phrase.delete_recent_activity_desc', [
                    'user_name'  => $this->userName,
                    'group_name' => $this->groupName,
                ])),
        );
    }
}
