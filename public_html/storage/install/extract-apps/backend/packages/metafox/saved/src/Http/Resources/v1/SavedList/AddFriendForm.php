<?php

namespace MetaFox\Saved\Http\Resources\v1\SavedList;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;

class AddFriendForm extends AbstractForm
{
    protected int $id;

    public function boot(?int $id = null)
    {
        $this->id = $id;
    }

    protected function prepare(): void
    {
        $this->asPost()->title(__p('saved::phrase.add_friends'))
            ->action(url_utility()->makeApiUrl("saveditems-collection/add-friend/{$this->id}"))
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $savedList = resolve(SavedListRepositoryInterface::class)->find($this->id);
        $this->addBasic()
            ->addFields(
                Builder::friendPicker('users')
                    ->endpoint(url_utility()->makeApiUrl('friend/invite-to-item'))
                    ->multiple()
                    ->setAttributes([
                        'apiParams' => [
                            'owner_id'  => $savedList->user_id,
                            'item_type' => 'saved_list',
                            'item_id'   => $savedList->entityId(),
                        ],
                    ])
                    ->placeholder(__p('friend::phrase.search_for_friends'))
            );

        $footer = $this->addFooter();
        $footer->addFields(
            Builder::submit()
                ->label(__p('core::phrase.save_changes'))
                ->enableWhen(['lengthGreaterOrEquals', 'users', 1]),
            Builder::cancelButton(),
        );
    }
}
