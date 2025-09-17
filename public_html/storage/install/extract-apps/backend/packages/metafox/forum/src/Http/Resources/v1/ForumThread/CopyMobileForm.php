<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Forum\Repositories\Eloquent\ForumThreadRepository;
use MetaFox\Yup\Yup;

class CopyMobileForm extends CopyForm
{
    public function boot(ForumThreadRepository $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id ?? 0);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic   = $this->addBasic();

        $basic->addFields(
            Builder::choice('forum_id')
                ->required()
                ->label(__p('forum::phrase.forum'))
                ->options($this->getForums())
                ->yup(Yup::number()->required()),
            Builder::hidden('thread_id'),
            Builder::text('title')
                ->required()
                ->returnKeyType('next')
                ->marginNormal()
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('forum::form.fill_in_a_title_for_your_thread')),
        );
    }
}
