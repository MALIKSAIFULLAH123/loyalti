<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Section;
use MetaFox\Forum\Http\Requests\v1\ForumThread\CreateFormRequest;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Yup\Yup;

class EditMobileForm extends CreateMobileForm
{
    public function __construct($resource = null)
    {
        /*
         * @var ForumThread $resource
         */

        parent::__construct($resource);

        $this->user = user();
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(CreateFormRequest $request, ForumThreadRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();

        if ($id !== null) {
            $this->resource = $repository->find($id);
            policy_authorize(ForumThreadPolicy::class, 'update', $context, $this->resource);
            $this->owner   = $this->resource->owner;
            $this->ownerId = $this->resource->ownerId();
        }
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $this->handleForumField($basic);

        $this->buildBodySection($basic);
    }

    protected function handleForumField(Section $basic): void
    {
        $canUsingWiki = policy_check(ForumThreadPolicy::class, 'createWiki', $this->user, $this->owner, $this->resource);

        if ($canUsingWiki) {
            $basic->addFields(
                Builder::switch('is_wiki')
                    ->label(__p('forum::phrase.display_on_wiki'))
                    ->description(__p('forum::phrase.display_on_wiki_description')),
            );
        }

        $yup = match ($canUsingWiki) {
            true => !$this->resource->isWiki()
                ? Yup::number()
                    ->when(
                        Yup::when('is_wiki')
                            ->is(0)
                            ->then(
                                Yup::number()
                                    ->required()
                                    ->setError('typeError', __p('forum::validation.forum_id.required'))
                            )
                    )
                : Yup::number()
                    ->when(
                        Yup::when('is_wiki')
                            ->is(0)
                            ->then(
                                Yup::number()
                                    ->setError('typeError', __p('forum::validation.forum_id.required'))
                            )
                    ),
            default => !$this->resource->isWiki()
                ? Yup::number()
                    ->required()
                    ->setError('typeError', __p('forum::validation.forum_id.required'))
                :
                Yup::number()
                    ->setError('typeError', __p('forum::validation.forum_id.required')),
        };

        $basic->addFields(
            Builder::choice('forum_id')
                ->required()
                ->label(__p('forum::phrase.forum'))
                ->options($this->forumRepository()->getForumOptions($this->resource->forum))
                ->yup($yup)
                ->showWhen(['falsy', 'is_wiki']),
        );
    }

    protected function prepare(): void
    {
        $resource = $this->resource;
        $text     = null;
        $tags     = [];

        if (null !== $resource->description) {
            $text = $resource->description->text_parsed;
        }

        if (null !== $resource->tags) {
            $tags = $resource->tags;
        }

        $itemType = $resource->item_type;
        $itemId   = $resource->item_id;

        $isSubscribed = null !== $resource->subscribed;

        $values = [
            'title'         => $resource->title,
            'text'          => $text,
            'is_closed'     => $resource->is_closed,
            'attachments'   => $resource->attachmentsForForm(),
            'tags'          => $tags,
            'item_type'     => $itemType,
            'item_id'       => $itemId,
            'is_subscribed' => (int) $isSubscribed,
            'is_wiki'       => (int) $resource->is_wiki,
            'id'            => $resource->entityId(),
        ];

        if ($resource->forum_id > 0) {
            Arr::set($values, 'forum_id', $resource->forum_id);
        }

        if (null !== $itemType) {
            $integratedItem = app('events')->dispatch(
                'forum.thread.integrated_item.edit_initialize',
                [$this->user, $itemType, $itemId, 'forum_thread.attach_poll'],
                true
            );

            if (is_array($integratedItem) && count($integratedItem) > 0) {
                $values['integrated_item'] = array_merge($integratedItem, [
                    'id' => $itemId,
                ]);
            }
        }

        $this->title(__p('forum::phrase.edit_thread'))
            ->setBackProps(__p('forum::phrase.forums'))
            ->action('forum-thread/' . $resource->entityId())
            ->asPut()
            ->setValue($values);
    }
}
