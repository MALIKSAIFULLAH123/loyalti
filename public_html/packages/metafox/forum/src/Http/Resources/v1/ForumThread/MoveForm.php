<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;

class MoveForm extends AbstractForm
{
    protected function prepare(): void
    {
        $resource = $this->resource;

        $this
            ->title(__p('forum::menu.move_thread'))
            ->action('forum-thread/move/' . $resource->entityId())
            ->asPatch()
            ->setValue([
                'forum_id' => $resource->getForumId(),
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addField(
            Builder::choice('forum_id')
                ->required()
                ->label(__p('forum::phrase.forum'))
                ->options($this->getForums())
        );

        $this->addFooter()->addFields(
            Builder::submit()
                ->label(__p('forum::menu.move')),
            Builder::cancelButton(),
        );
    }

    /**
     * @return array
     * @throws AuthenticationException
     */
    protected function getForums(): array
    {
        $context = user();

        return resolve(ForumRepositoryInterface::class)->getForumsForForm($context);
    }
}
