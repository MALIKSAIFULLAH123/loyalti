<?php
namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Forum\Repositories\Eloquent\ForumThreadRepository;

class MoveMobileForm extends MoveForm
{
    public function boot(ForumThreadRepository $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id ?? 0);
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
    }
}
