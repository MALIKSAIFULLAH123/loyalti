<?php

namespace MetaFox\Group\Http\Resources\v1\Block;

use Exception;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Http\Requests\v1\Block\StoreRequest;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;

class BlockMemberMobileForm extends AbstractForm
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

    public function boot(StoreRequest $request, GroupRepositoryInterface $groupRepository): void
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
        $this->action('group-block')
            ->asPost()
            ->title(__p('group::phrase.block_member'))
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
                ->plainText(__p('group::phrase.block_member_description', [
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
