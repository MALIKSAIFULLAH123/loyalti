<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Support\ForumThreadSupport;
use MetaFox\Yup\Yup;

class CopyForm extends AbstractForm
{
    protected function prepare(): void
    {
        $resource = $this->resource;

        $isWiki = $resource->is_wiki;

        $values = [
            'title'     => ForumThreadSupport::PREFIX_COPY . $resource->toTitle(),
            'thread_id' => $resource->entityId(),
        ];

        if (!$isWiki) {
            Arr::set($values, 'forum_id', $resource->forum_id);
        }

        $this->asPost()
            ->title(__p('forum::menu.copy_thread'))
            ->action('forum-thread/copy')
            ->setValue($values);
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

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('forum::menu.copy')),
                Builder::cancelButton()
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
