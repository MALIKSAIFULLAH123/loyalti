<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Forum\Http\Requests\v1\ForumThread\CreateFormRequest;
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
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\Yup\Yup;

/**
 * Class CreateForm.
 *
 * @property ForumThread $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CreateForm extends AbstractForm
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
     * @var int
     */
    protected int $forumId = 0;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
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

    /**
     * @param int $forumId
     *
     * @return self
     */
    public function setForumId(int $forumId): self
    {
        $this->forumId = $forumId;

        return $this;
    }

    /**
     * @return int
     */
    public function getForumId(): int
    {
        return $this->forumId;
    }

    public function __construct($resource = null)
    {
        parent::__construct($resource);

        $this->user = $this->owner = user();
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, ForumThreadRepositoryInterface $repository, ?int $id = null): void
    {
        $params = $request->validated();

        if (Arr::get($params, 'forum_id', 0)) {
            $this->setForumId(Arr::get($params, 'forum_id'));
        }

        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }

        $message = __p('quota::phrase.quota_control_invalid', ['entity_type' => __p('forum::phrase.forum_thread')]);

        app('quota')->checkQuotaControlWhenCreateItem($this->user, ForumThread::ENTITY_TYPE, 1, [
            'messageFormat' => 'text',
            'message'       => $message,
        ]);

        $this->resource = new ForumThread($params);

        if ($this->getForumsTopLevel()->isEmpty() && (!ForumThreadFacade::checkCreateThreads($this->user) || !$this->canUsingWiki())) {
            abort(403, __p('forum::phrase.all_forums_are_closed_you_are_currently_unable_post_new_forum_thread'));
        }

        if (0 === $this->forumId) {
            policy_authorize(ForumThreadPolicy::class, 'createGenerate', $this->user, $this->owner);
        } else {
            policy_authorize(ForumThreadPolicy::class, 'createOnForum', $this->user, $this->owner, $this->forumId);
        }
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $this
            ->title(__p('forum::phrase.forum_phrase_menu_create_new_thread'))
            ->action('forum-thread')
            ->asPost()
            ->setBackProps(__p('forum::phrase.forums'))
            ->setValue([
                'is_subscribed' => (int) $this->canSubscribe(),
                'is_wiki'       => 0,
                'owner_id'      => $this->owner?->entityId() ?? 0,
            ]);
    }

    /**
     * @param Section $basic
     *
     * @return void
     * @throws AuthenticationException
     */
    protected function handleForumField(Section $basic): void
    {
        if ($this->forumId > 0) {
            $basic->addField(
                Builder::hidden('forum_id')
                    ->setValue($this->getForumId())
            );

            return;
        }

        if ($this->canUsingWiki()) {
            $basic->addField(
                Builder::switch('is_wiki')
                    ->label(__p('forum::phrase.display_on_wiki'))
                    ->description(__p('forum::phrase.display_on_wiki_description')),
            );

            if ($this->getForumsTopLevel()->isEmpty()) {
                $basic->addField(
                    Builder::alert('is_wiki_warning')
                        ->showWhen(['falsy', 'is_wiki'])
                        ->asWarning()
                        ->message(__p('forum::phrase.all_forums_are_closed_you_are_currently_unable_post_new_forum_thread'))
                );
            }
        }

        if ($this->getForumsTopLevel()->isEmpty()) {
            return;
        }

        $basic->addField(
            Builder::choice('forum_id')
                ->required()
                ->label(__p('forum::phrase.forum'))
                ->options($this->forumRepository()->getForumOptions())
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

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic          = $this->addBasic();
        $maxTitleLength = Settings::get('forum.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);

        $minTitleLength = Settings::get('forum.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);

        if (!policy_check(ForumThreadPolicy::class, 'hasCreationPermission', $this->user, $this->forumId)) {
            $basic->addField(
                Builder::alert('creation_permission_error')
                    ->asError()
                    ->message(__p('forum::phrase.you_dont_have_permission_to_create_new_thread'))
            );

            return;
        }

        $this->handleForumField($basic);

        $section = $this->addSection(['name' => 'body']);
        $footer  = $this->addFooter();

        if ($this->getForumsTopLevel()->isEmpty()) {
            $section->showWhen(['truthy', 'is_wiki']);
            $footer->showWhen(['truthy', 'is_wiki']);
        }

        $footer->addFields(
            Builder::submit()->label(__p('core::phrase.create')),
            Builder::cancelButton(),
        );

        $section->addFields(
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

        $this->attachItem($section);

        $canSubscribe = $this->canSubscribe();

        $subscribeField = match ($canSubscribe) {
            true => Builder::switch('is_subscribed')
                ->label(__p('forum::menu.subscribe')),
            false => Builder::hidden('is_subscribed'),
        };

        $section->addFields(
            $this->buildTagField(),
            $subscribeField
        );

        $section->addField(
            Captcha::getFormField('forum.' . ForumSupport::CAPTCHA_RULE_CREATE_THREAD)
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
        $context = $this->user;

        if ($this->resource instanceof ForumThread && $this->resource?->id) {
            return policy_check(ForumThreadPolicy::class, 'subscribe', $context, $this->resource);
        }

        return $context->hasPermissionTo('forum_thread.auto_approved') && $context->hasPermissionTo('forum_thread.subscribe');
    }

    protected function attachItem(Section $basic): void
    {
        $context = $this->user;

        if (policy_check(ForumThreadPolicy::class, 'attachPoll', $context)) {
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
        $entity   = null;
        $resource = $this->resource;

        if (null !== $resource && null !== $resource->item_type) {
            $entity = $resource->item;
        }

        return ForumThreadFacade::getIntegratedItem($this->user, $this->owner, $entity);
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

    protected function getForumsTopLevel(): Collection
    {
        return $this->forumRepository()->builderQueryForums(['level' => 1])->get();
    }

    protected function forumRepository(): ForumRepositoryInterface
    {
        return resolve(ForumRepositoryInterface::class);
    }

    protected function canUsingWiki(): bool
    {
        return policy_check(ForumThreadPolicy::class, 'createWiki', $this->user, $this->owner, $this->resource);
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
