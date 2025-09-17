<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Form\Section;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\CreateFormRequest;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\LiveStreaming\Policies\LiveVideoPolicy;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreLiveVideoForm.
 * @property ?Model $resource
 * @property string $stream_key
 * @ignore
 * @codeCoverageIgnore
 */
class StoreLiveVideoForm extends AbstractForm
{
    use RepoTrait;
    use PrivacyFieldTrait;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, LiveVideoRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();

        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }

        policy_authorize(LiveVideoPolicy::class, 'create', $context);
        $this->resource = new Model($params);
    }

    protected function prepare(): void
    {
        $context   = user();
        $privacy   = UserPrivacy::getItemPrivacySetting($context->entityId(), 'live_video.item_privacy');
        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }

        $streamKey        = $this->getUserStreamKey($context);
        $this->stream_key = $streamKey?->stream_key;

        $this->title(__p('livestreaming::phrase.go_live'))
            ->action(url_utility()->makeApiUrl('live-video/go-live'))
            ->asPost()
            ->setValue([
                'module_id'  => 'livestreaming',
                'privacy'    => $privacy,
                'stream_key' => $this->stream_key,
                'server_url' => $this->getServerUrl(),
                'owner_id'   => $this->resource->owner_id,
                'location'   => null,
                'post_to'    => 'timeline',
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('title')
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('livestreaming::phrase.title_optional'))
                ->maxLength(255),
            Builder::textArea('text')
                ->returnKeyType('default')
                ->setAttribute('enableEmoji', true)
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('livestreaming::phrase.add_description_about_your_live_video')),
            Builder::hidden('module_id')
                ->setValue('livestreaming'),
            Builder::hidden('owner_id'),
            Builder::singlePhoto('file')
                ->aspectRatio('16:9')
                ->widthPhoto('250px')
                ->required(false)
                ->itemType('photo')
                ->accept('image/*')
                ->label(__p('livestreaming::phrase.video_thumbnail'))
                ->acceptFail(__p('photo::phrase.photo_accept_type_fail'))
                ->thumbnailSizes($this->resource->getSizes())
                ->previewUrl($this->resource->image),
        );

        if (app_active('metafox/friend')) {
            $basic->addField(
                Builder::friendPicker('tagged_friends')
                    ->label(__p('livestreaming::phrase.tag_friends'))
                    ->placeholder(__p('livestreaming::phrase.select_friends'))
                    ->multiple()
                    ->apiEndpoint(url_utility()->makeApiUrl('friend/tag-suggestion?owner_id=' . $this->resource?->owner_id))
            );
        }

        $basic->addFields(
            Builder::location('location')
                ->hideMap(true)
                ->returnKeyType('next')
                ->yup(
                    Yup::object()
                        ->nullable()
                        ->when(
                            Yup::when('is_online')
                                ->is(0)
                                ->then(
                                    Yup::object()
                                        ->nullable()
                                        ->setError('typeError', __p('livestreaming::phrase.location_is_a_required_field'))
                                        ->addProperty(
                                            'lat',
                                            Yup::number()
                                                ->nullable()
                                        )
                                        ->addProperty(
                                            'lng',
                                            Yup::number()
                                                ->nullable()
                                        )
                                        ->addProperty(
                                            'address',
                                            Yup::string()
                                                ->nullable()
                                        )
                                        ->addProperty(
                                            'short_name',
                                            Yup::string()
                                                ->nullable()
                                        )
                                )
                        )
                        ->addProperty(
                            'lat',
                            Yup::number()
                                ->nullable()
                        )
                        ->addProperty(
                            'lng',
                            Yup::number()
                                ->nullable()
                        )
                        ->addProperty(
                            'address',
                            Yup::string()
                                ->nullable()
                        )
                        ->addProperty(
                            'short_name',
                            Yup::string()
                                ->nullable()
                        )
                ),
        );

        $privacy = $this->buildPrivacyField()->description(__p('livestreaming::phrase.control_who_can_see_this_live_video'));
        if (!$this->isEdit()) {
            $basic->addFields(
                Builder::copyText('stream_key')
                    ->label(__p('livestreaming::phrase.stream_key'))
                    ->readOnly(),
                Builder::copyText('server_url')
                    ->label(__p('livestreaming::phrase.server_url'))
                    ->readOnly(),
                Builder::muxPlayer('mux_player')
                    ->setAttribute('streamKey', $this->stream_key),
            );
            $this->buildPostToField($basic);
            $this->buildAdditionFields($basic);
            $privacy->showWhen([
                'or',
                ['falsy', 'post_to'],
                ['eq', 'post_to', 'timeline'],
            ]);
        }

        $basic->addFields($privacy);

        $submitLabel = __p('livestreaming::phrase.go_live');

        if ($this->isEdit()) {
            $submitLabel = __p('core::phrase.save_changes');
        }

        $builderSubmitBtn = Builder::submit('__submit')
                   ->label($submitLabel);

        $this->addFooter()
            ->addFields(
                $this->isEdit() ? $builderSubmitBtn : $builderSubmitBtn->enableWhen(['eq', 'mux_player', 'waiting']),
                Builder::cancelButton()->sizeMedium(),
            );
    }

    public function isEdit(): bool
    {
        return null !== $this->resource->id;
    }

    protected function getUserStreamKey(ContractUser $user): mixed
    {
        return $this->getUserStreamKeyRepository()->getUserStreamKey($user);
    }

    protected function getServerUrl(): string
    {
        $service       = $this->getServiceManager();
        $serviceDriver = $service->getDefaultServiceProvider(true);

        return $serviceDriver->getLiveServerUrl();
    }
    protected function buildPostToField(Section $section): void
    {
        if ($this->owner instanceof HasPrivacyMember) {
            return;
        }

        $options = [
            ['label' => __p('livestreaming::phrase.post_on_timeline'), 'value' => 'timeline'],
        ];

        $fields = [];

        app('events')->dispatch('livestreaming.build_post_to', [&$fields, &$options, ['entity' => Model::ENTITY_TYPE, 'privacy' => 'live_video.share_live_videos'], false]);

        if (count($options) == 1) {
            return;
        }
        $section->addFields(
            Builder::choice('post_to')
                ->required()
                ->label(__p('livestreaming::phrase.choose_where_to_post'))
                ->options($options),
            ...$fields
        );
    }

    protected function buildAdditionFields(Section $section): void
    {
        if ($this->owner instanceof HasPrivacyMember) {
            return;
        }
        $results = app('events')->dispatch('livestreaming.build_integrate_field', [user(), ['eq', 'post_to', 'timeline'], false]);

        if (!$results) {
            return;
        }
        foreach ($results as $result) {
            if (is_array($result)) {
                $section->addFields(...$result);
            } else {
                $section->addFields($result);
            }
        }
    }
}
