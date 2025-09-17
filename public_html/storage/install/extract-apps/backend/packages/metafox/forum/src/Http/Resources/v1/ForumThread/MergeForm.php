<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewScope;
use MetaFox\Yup\Yup;

class MergeForm extends AbstractForm
{
    protected function prepare(): void
    {
        $resource = $this->resource;

        $this->asPost()
            ->title(__p('forum::menu.merge_thread'))
            ->action('forum-thread/merge')
            ->secondAction('forum/mergedItem')
            ->setValue([
                'forum_id'          => $resource->forum_id,
                'current_thread_id' => $resource->entityId(),
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $searchApiUrl = url_utility()->makeApiUrl('forum-thread/suggestion-search');

        $basic->addFields(
            Builder::hidden('current_thread_id'),
            Builder::choice('forum_id')
                ->required()
                ->label(__p('forum::phrase.forum'))
                ->options($this->getForums())
                ->yup(
                    Yup::number()
                        ->required()
                        ->setError('typeError', __p('forum::validation.forum_id.required'))
                ),
            Builder::autocomplete('merged_thread_id')
                ->required()
                ->label(__p('forum::phrase.merged_thread'))
                ->searchEndpoint($searchApiUrl)
                ->placeholder(__p('forum::phrase.searching_threads_for_merging'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->setError('typeError', __p('forum::validation.please_choose_a_thread'))
                )
                ->searchParams([
                    'forum_id'           => ':forum_id',
                    'view'               => ThreadViewScope::VIEW_MERGED,
                    'exclude_thread_ids' => $this->resource->entityId(),
                ])
        );

        $this->addFooter()->addFields(
            Builder::submit()->label(__p('forum::menu.merge')),
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
