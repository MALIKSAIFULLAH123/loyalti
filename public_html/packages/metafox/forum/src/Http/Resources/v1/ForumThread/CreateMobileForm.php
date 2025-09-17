<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Forum\Http\Requests\v1\ForumThread\CreateFormRequest;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Models\ForumThread as Model;
use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Forum\Support\Facades\ForumThread as ForumThreadFacade;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\Yup\Yup;

/**
 * Class CreateMobileForm.
 *
 * @property Model $resource
 */
class CreateMobileForm extends AbstractForm
{
    /**
     * @var int|null
     */
    protected ?int $ownerId = null;
    /**
     * @var User
     */
    protected User $owner;
    protected User $user;
    protected int  $forumId = 0;

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
     * @param CreateFormRequest              $request
     * @param ForumThreadRepositoryInterface $repository
     * @param int|null                       $id
     *
     * @return void
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function boot(CreateFormRequest $request, ForumThreadRepositoryInterface $repository, ?int $id = null): void
    {
        $data       = $request->validated();
        $this->user = $this->owner = user();
        $ownerId    = Arr::get($data, 'owner_id', 0);

        if ($ownerId > 0) {
            $this->owner = UserEntity::getById($ownerId)->detail;
        }

        if (Arr::get($data, 'forum_id', 0)) {
            $this->setForumId(Arr::get($data, 'forum_id'));
        }

        $this->resource = new Model($data);

        $message = __p('quota::phrase.quota_control_invalid', ['entity_type' => __p('forum::phrase.forum_thread')]);

        app('quota')->checkQuotaControlWhenCreateItem(
            $this->user,
            ForumThread::ENTITY_TYPE,
            1,
            [
                'messageFormat' => 'text',
                'message'       => $message,
            ]
        );

        if ($this->getForumsTopLevel()->isEmpty() && (!ForumThreadFacade::checkCreateThreads($this->user) || !$this->canUsingWiki())) {
            abort(403, __p('forum::phrase.all_forums_are_closed_you_are_currently_unable_post_new_forum_thread'));
        }

        if (0 === $this->forumId) {
            policy_authorize(ForumThreadPolicy::class, 'createGenerate', $this->user, $this->owner);
        } else {
            policy_authorize(ForumThreadPolicy::class, 'createOnForum', $this->user, $this->owner, $this->forumId);
        }

        $this->ownerId = $ownerId;
    }

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
                'tags'          => [],
                'owner_id'      => $this->ownerId ?? 0,
            ]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        if (!policy_check(ForumThreadPolicy::class, 'hasCreationPermission', $this->user)) {
            $basic->addField($this->getCreationPermissionErrorField());

            return;
        }

        $this->handleForumField($basic);

        $section     = $this->addSection(['name' => 'body']);
        $rightHeader = ['showRightHeader' => true];

        if ($this->getForumsTopLevel()->isEmpty()) {
            $section->showWhen(['truthy', 'is_wiki']);
            Arr::set($rightHeader, 'enableWhen', ['and', ['truthy', 'is_wiki']]);
        }

        $this->addHeader($rightHeader)->component('FormHeader');

        $this->buildBodySection($section);
    }

    protected function buildTagField(): ?AbstractField
    {
        $owner = $this->getOwner();
        if ($owner instanceof HasPrivacyMember) {
            return null;
        }

        return Builder::tags('tags')
            ->label(__p('core::phrase.tags'))
            ->placeholder(__p('core::phrase.keywords'));
    }

    /**
     * @return bool
     */
    protected function canSubscribe(): bool
    {
        $context = $this->user;

        if ($this->resource instanceof ForumThread && $this->resource?->id) {
            return policy_check(ForumThreadPolicy::class, 'subscribe', $context, $this->resource);
        }

        return $context->hasPermissionTo('forum_thread.auto_approved') && $context->hasPermissionTo('forum_thread.subscribe');
    }

    /**
     * @param Section $basic
     */
    protected function attachItem(Section $basic): void
    {
        if (policy_check(ForumThreadPolicy::class, 'attachPoll', $this->user)) {
            $this->setItemComponent($basic);

            return;
        }

        if (!$this->resource->id) {
            return;
        }

        if (null === $this->resource->item_type) {
            return;
        }

        $this->setItemComponent($basic);
    }

    /**
     * @return array|null
     */
    protected function getIntegratedComponent(): ?array
    {
        $entity = null;

        $resource = $this->resource;

        if (null !== $resource && null !== $resource->item_type) {
            $entity = $resource->item;
        }

        return ForumThreadFacade::getIntegratedItem($this->user, $this->owner, $entity, 'mobile');
    }

    /**
     * @param Section $basic
     */
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

        $value = $this->getValue();

        if (!is_array($value)) {
            $value = [];
        }

        $value = array_merge($value, ['item_type' => $item['item_type']]);

        if (!$this->resource->id) {
            if (Arr::has($item, 'values')) {
                $value = array_merge($value, $item['values']);
            }
        }

        $this->setValue($value);
    }

    protected function getWikiWarningField(Section $basic): void
    {
        if ($this->getForumsTopLevel()->isNotEmpty()) {
            return;
        }

        if (version_compare(MetaFox::getApiVersion(), 'v1.13', '<=')) {
            $basic->addField(
                Builder::typography('is_wiki_warning')
                    ->showWhen(['falsy', 'is_wiki'])
                    ->plainText(__p('forum::phrase.all_forums_are_closed_you_are_currently_unable_post_new_forum_thread'))
            );

            return;
        }

        $basic->addField(
            Builder::alert('is_wiki_warning')
                ->showWhen(['falsy', 'is_wiki'])
                ->asWarning()
                ->message(__p('forum::phrase.all_forums_are_closed_you_are_currently_unable_post_new_forum_thread'))
        );
    }

    protected function handleForumField(Section $basic): void
    {
        if ($this->canUsingWiki()) {
            $basic->addField(
                Builder::switch('is_wiki')
                    ->label(__p('forum::phrase.display_on_wiki'))
                    ->description(__p('forum::phrase.display_on_wiki_description')),
            );

            $this->getWikiWarningField($basic);
        }

        if ($this->getForumsTopLevel()->isEmpty()) {
            return;
        }

        $yup = match ($this->canUsingWiki()) {
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
                ->options($this->forumRepository()->getForumOptions())
                ->yup($yup)
                ->showWhen(['falsy', 'is_wiki']),
        );
    }

    protected function getForumsTopLevel(): Collection
    {
        return $this->forumRepository()->builderQueryForums(['level' => 1])->get();
    }

    protected function forumRepository(): ForumRepositoryInterface
    {
        return resolve(ForumRepositoryInterface::class);
    }

    protected function getCreationPermissionErrorField(): AbstractField
    {
        if (version_compare(MetaFox::getApiVersion(), 'v1.13', '<')) {
            return Builder::typography('creation_permission_error')
                ->plainText(__p('forum::phrase.you_dont_have_permission_to_create_new_thread'));
        }

        return Builder::alert('creation_permission_error')
            ->asError()
            ->message(__p('forum::phrase.you_dont_have_permission_to_create_new_thread'));
    }

    protected function buildSubscribeField(): AbstractField
    {
        $canSubscribe = $this->canSubscribe();

        return match ($canSubscribe) {
            true => Builder::switch('is_subscribed')
                ->label(__p('forum::menu.subscribe')),
            false => Builder::hidden('is_subscribed'),
        };
    }

    protected function buildBodySection(Section $section): void
    {
        $maxTitleLength = Settings::get('forum.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
        $minTitleLength = Settings::get('forum.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $section->addFields(
            Builder::text('title')
                ->required()
                ->maxLength($maxTitleLength)
                ->returnKeyType('next')
                ->label(__p('core::phrase.title'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxTitleLength]))
                ->placeholder(__p('forum::form.fill_in_a_title_for_your_thread'))
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
        );

        $this->attachItem($section);

        $section->addFields(
            $this->buildTagField(),
            $this->buildSubscribeField(),
        );
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
