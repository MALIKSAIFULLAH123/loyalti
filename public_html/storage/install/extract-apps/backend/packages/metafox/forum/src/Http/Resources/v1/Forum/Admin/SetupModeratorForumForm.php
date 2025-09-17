<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum\Admin;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Forum\Http\Resources\v1\Moderator\Admin\ModeratorItemCollection;
use MetaFox\Forum\Models\Moderator;
use MetaFox\Forum\Repositories\ModeratorRepositoryInterface;
use MetaFox\Forum\Repositories\PermissionConfigRepositoryInterface;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class SetupModeratorForumForm extends AbstractForm
{
    /**
     * @var int
     */
    private int $id;

    /**
     * @var array
     */
    protected array $moderatorIds = [];

    /**
     * @var array
     */
    protected array $permissions;

    protected function prepare(): void
    {
        $moderatorIds = $this->getModeratorIds();

        if (!count($moderatorIds)) {
            $moderatorIds = null;
        }

        $values = array_merge(['moderator_ids' => $moderatorIds], $this->getPermissionValues());

        $this->title(__p('forum::phrase.manage_moderators'))
            ->asPost()
            ->action('admincp/forum/forum/setup-moderator/:id')
            ->setValue($values);
    }

    public function boot(?int $id = null)
    {
        $this->id = $id;

        $this->moderatorIds = $this->getModeratorIds();

        $this->permissions = $this->getPermissions();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addField(
            Builder::friendPicker('moderator_ids')
                ->label(__p('forum::phrase.moderators'))
                ->multiple()
                ->endpoint('admincp/forum/forum/moderator')
                ->setAttribute('apiParams', [
                    'forum_id'     => $this->id,
                ])
                ->setAttribute('noOptionsText', __p('core::phrase.no_user_found'))
        );

        foreach ($this->permissions as $aPerm) {
            $basic->addField(
                Builder::switch($aPerm['var_name'])
                    ->label($aPerm['name']),
            );
        }

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.submit')),
                Builder::cancelButton(),
            );
    }

    protected function getModeratorIds(): array
    {
        /**
         * @var Collection $moderators
         */
        $moderators = resolve(ModeratorRepositoryInterface::class)->getForumModerators($this->id);

        if (!$moderators->count()) {
            return [];
        }

        $moderators = $moderators->map(function (Moderator $moderator) {
            return $moderator->user;
        })
        ->filter(function (?User $user) {
            return $user instanceof User;
        })
        ->values();

        return (new ModeratorItemCollection($moderators))->toArray(request());
    }

    protected function getPermissions(): array
    {
        return resolve(ModeratorRepositoryInterface::class)->getPerms();
    }

    protected function getPermissionValues(): array
    {
        $configs = resolve(PermissionConfigRepositoryInterface::class)->getConfigs($this->id);

        if (!count($configs)) {
            return [];
        }

        return collect($configs)
            ->map(function ($value) {
                return (int) $value;
            })
            ->toArray();
    }

    protected function getUserDropDown(): array
    {
        $aOptions = [];

        $params = [
            'limit' => null,
            'sort' => 'full_name',
            'sort_type' => 'asc',
            'view' => 'all',
        ];
        $aUsers = resolve(UserRepositoryInterface::class)->viewUsers(user(), $params);

        foreach ($aUsers as $aUser) {
            $aOptions[] = [
                'label' => $aUser['user_name'],
                'value' => $aUser['id'],
            ];
        }
        return $aOptions;
    }
}
