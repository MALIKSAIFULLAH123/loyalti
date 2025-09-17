<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Forum\Support\Facades\ForumThread as ForumThreadFacade;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * Class CreateForm.
 * @property ForumThread $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EditForm extends AbstractForm
{
    /**
     * @var User
     */
    protected User $user;

    /**
     * @var User
     */
    protected User $owner;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param  User $user
     * @return self
     */
    public function setOwner(User $user): self
    {
        $this->owner = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getOwner(): User
    {
        return $this->owner;
    }

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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(ForumThreadRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);

        policy_authorize(ForumThreadPolicy::class, 'update', $this->user, $this->resource);

        $this->owner = $this->resource->owner;
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $resource = $this->resource;

        $text = null;

        if (null !== $resource->description) {
            $text = $resource->description->text_parsed;
        }

        $tags = [];

        if (null !== $resource->tags) {
            $tags = $resource->tags;
        }

        $item     = $resource->getItem();
        $itemType = $item?->entityType();
        $itemId   = $item?->entityId() ?? 0;

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
            'owner_id'      => $resource->owner_id,
        ];

        if ($resource->forum_id > 0) {
            Arr::set($values, 'forum_id', $resource->forum_id);
        }

        if (null !== $itemType) {
            $integratedItem = app('events')->dispatch(
                'forum.thread.integrated_item.edit_initialize',
                [$this->user, $itemType, $resource->item_id, 'forum_thread.attach_poll'],
                true
            );

            if (is_array($integratedItem) && count($integratedItem) > 0) {
                $values['integrated_item'] = array_merge($integratedItem, [
                    'id' => $resource->item_id,
                ]);
            }
        }

        $this->title(__p('forum::phrase.edit_thread'))
            ->setBackProps(__p('forum::phrase.forums'))
            ->action('forum-thread/' . $resource->entityId())
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $maxTitleLength = Settings::get('forum.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);

        $minTitleLength = Settings::get('forum.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);

        $this->buildForumField($basic);

        $basic->addFields(
            Builder::text('title')
                ->required()
                ->returnKeyType('next')
                ->margin('normal')
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('forum::form.fill_in_a_title_for_your_thread'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxTitleLength]))
                ->maxLength($maxTitleLength)
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable(false)
                        ->minLength(
                            $minTitleLength,
                            __p(
                                'core::validation.title_minimum_length_of_characters',
                                ['number' => $minTitleLength]
                            )
                        )
                        ->maxLength(
                            $maxTitleLength,
                            __p('core::validation.title_maximum_length_of_characters', [
                                'min' => $minTitleLength,
                                'max' => $maxTitleLength,
                            ])
                        )
                ),
            $this->buildTextField(),
            Builder::attachment()
                ->placeholder(__p('core::phrase.attach_files'))
                ->itemType(ForumThread::ENTITY_TYPE)
        );

        $this->attachItem($basic);

        $canSubscribe = $this->canSubscribe();

        $subscribeField = match ($canSubscribe) {
            true => Builder::switch('is_subscribed')
                ->label(__p('forum::menu.subscribe')),
            false => Builder::hidden('is_subscribed'),
        };

        $basic->addFields(
            $this->buildTagField(),
            $subscribeField
        );

        $basic->addField(
            Captcha::getFormField('forum.' . ForumSupport::CAPTCHA_RULE_CREATE_THREAD)
        );

        $this->addDefaultFooter();
    }

    protected function buildForumField(Section $basic): void
    {
        if (policy_check(ForumThreadPolicy::class, 'createWiki', $this->user, $this->owner, $this->resource)) {
            $basic->addField(
                Builder::switch('is_wiki')
                    ->label(__p('forum::phrase.display_on_wiki'))
                    ->description(__p('forum::phrase.display_on_wiki_description')),
            );
        }

        $basic->addField(
            Builder::choice('forum_id')
                ->required()
                ->label(__p('forum::phrase.forum'))
                ->options($this->forumRepository()->getForumOptions($this->resource->forum))
                ->showWhen(['falsy', 'is_wiki'])
                ->yup(
                    Yup::number()
                        ->nullable()
                        ->when(
                            Yup::when('is_wiki')
                                ->is(0)
                                ->then(
                                    Yup::number()
                                        ->required()
                                        ->setError('typeError', __p('forum::validation.forum_id.required'))
                                )
                        )
                )
        );
    }

    protected function buildTagField(): ?AbstractField
    {
        $owner = $this->getOwner();
        if ($owner instanceof HasPrivacyMember) {
            return null;
        }

        return Builder::tags('tags')
            ->placeholder(__p('core::phrase.keywords'));
    }

    protected function canSubscribe(): bool
    {
        if ($this->resource instanceof ForumThread && $this->resource?->id) {
            return policy_check(ForumThreadPolicy::class, 'subscribe', $this->user, $this->resource);
        }

        return $this->user->hasPermissionTo('forum_thread.auto_approved') && $this->user->hasPermissionTo('forum_thread.subscribe');
    }

    protected function attachItem(Section $basic): void
    {
        if (policy_check(ForumThreadPolicy::class, 'attachPoll', $this->user)) {
            $this->setItemComponent($basic);

            return;
        }

        if (!$this->resource) {
            return;
        }

        if (null === $this->resource->item_type) {
            return;
        }

        $this->setItemComponent($basic);
    }

    protected function getIntegratedComponent(): ?array
    {
        $user = $this->getUser();

        $owner = $this->getOwner();

        $entity = null;

        $resource = $this->resource;

        if (null !== $resource && null !== $resource->item_type) {
            $entity = $resource->item;
        }

        return ForumThreadFacade::getIntegratedItem($user, $owner, $entity);
    }

    protected function setItemComponent(Section $basic): void
    {
        $item = $this->getIntegratedComponent();

        if (!is_array($item) || 0 === count($item)) {
            return;
        }

        $basic->addFields(
            $item['item_component'],
            Builder::hidden('item_type')
                ->setValue($item['item_type'])
        );

        if ($this->resource instanceof ForumThread) {
            return;
        }

        if (!Arr::has($item, 'values')) {
            return;
        }

        $values = $this->getValue();

        if (!is_array($values)) {
            $values = [];
        }

        $values = array_merge($values, $item['values']);

        $this->setValue($values);
    }

    protected function forumRepository(): ForumRepositoryInterface
    {
        return resolve(ForumRepositoryInterface::class);
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->required()
                ->returnKeyType('default')
                ->label(__p('forum::form.content'))
                ->placeholder(__p('forum::form.add_some_content_to_your_thread'))
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable(false)
                );
        }

        return Builder::textArea('text')
            ->required()
            ->returnKeyType('default')
            ->label(__p('forum::form.content'))
            ->placeholder(__p('forum::form.add_some_content_to_your_thread'))
            ->yup(
                Yup::string()
                    ->required()
                    ->nullable(false)
            );
    }
}
