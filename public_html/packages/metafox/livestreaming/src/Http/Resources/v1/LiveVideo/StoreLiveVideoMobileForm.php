<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\SelectSubForm;
use MetaFox\Form\PrivacyFieldMobileTrait;
use MetaFox\Form\Section;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\CreateFormRequest;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\LiveStreaming\Policies\LiveVideoPolicy;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Yup\Yup;

class StoreLiveVideoMobileForm extends AbstractForm
{
    use RepoTrait;
    use PrivacyFieldMobileTrait;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @driverType form-mobile
     * @driverName video.video.store
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

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'live_video.item_privacy');
        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }

        $streamKey = $this->getUserStreamKey($context);
        $this->title(__p('livestreaming::phrase.go_live'))
            ->action(url_utility()->makeApiUrl('live-video'))
            ->asPost()
            ->setValue([
                'module_id'  => 'livestreaming',
                'privacy'    => $privacy,
                'stream_key' => $streamKey?->stream_key,
                'server_url' => $this->getServerUrl(),
                'owner_id'   => $this->resource->owner_id,
                'post_to'    => 'timeline',
                'to_story'   => 0,
            ]);
    }

    protected function initialize(): void
    {
        $header    = $this->addHeader([]);
        $header
            ->variant('livestream')
            ->addFields(
                ...$this->buildBodyField(['falsy', 'sub_form'])
            );
        $basic = $this->addBasic()->variant('livestream');

        $this->buildBodyFields($basic);

        $basic->addFields(
            Builder::hidden('module_id')
                ->setValue('livestreaming'),
            Builder::hidden('owner_id'),
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

    protected function buildBodyFields(Section $section): void
    {
        $subHeader = $this->addSection('sub_header')->component('Row');
        $body      = $this->addSection('body')->variant('livestream');

        if (!$this->isEdit()) {
            $section->addFields(
                Builder::composerInput('text')
                    ->variant('livestream')
                    ->returnKeyType('default')
                    ->showWhen(['falsy', 'sub_form'])
                    ->placeholder(__p('livestreaming::phrase.add_description_about_your_live_video')),
            );
            $this->buildPostToField($subHeader, $body);

            return;
        }
        $section->addFields(
            Builder::textArea('text')
                ->returnKeyType('default')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('livestreaming::phrase.add_description_about_your_live_video')),
        );
    }

    protected function buildPostToField(Section $subHeader, Section $body): void
    {
        if ($this->owner instanceof HasPrivacyMember) {
            return;
        }

        $options = [
            ['label' => __p('livestreaming::phrase.post_on_timeline'), 'value' => 'timeline'],
        ];

        $fields = [];

        app('events')->dispatch('livestreaming.build_post_to', [&$fields, &$options, ['entity' => Model::ENTITY_TYPE, 'privacy' => 'live_video.share_live_videos'], true]);

        if (count($options) == 1) {
            $this->buildAdditionFields($subHeader, $body, ['and', ['eq', 'post_to', 'timeline'], ['truthy', 'sub_form']], 'standard');

            return;
        }
        $subHeader->addFields(
            Builder::selectSubForm('post_to')
                ->label('SelectSubForm')
                ->placeholder(__p('livestreaming::phrase.post'))
                ->fullWidth(false)
                ->setAttribute('icon', 'gear-o')
                ->variant('livestream')
                ->showWhen(['falsy', 'sub_form'])
                ->options($options)
        );
        $body->addFields(
            Builder::choice('post_to')
                ->label(__p('livestreaming::phrase.choose_where_to_post'))
                ->fullWidth(false)
                ->required()
                ->variant('standard')
                ->showWhen(['truthy', 'sub_form'])
                ->options($options),
            ...$fields,
            ...$this->buildBodyField(['truthy', 'sub_form'], 'standard'),
        );

        $this->buildAdditionFields($subHeader, $body, ['and', ['eq', 'post_to', 'timeline'], ['truthy', 'sub_form']], 'standard');
    }

    private function buildBodyField(array $showWhen = [], string $variant = 'livestream'): array
    {
        return [
            Builder::friendPicker('tagged_friends')
                ->variant($variant)
                ->label(__p('livestreaming::phrase.tag_friends'))
                ->placeholder(__p('livestreaming::phrase.select_friends'))
                ->multiple(true)
                ->endpoint(url_utility()->makeApiUrl('friend/tag-suggestion?owner_id=' . $this->resource?->owner_id))
                ->showWhen($showWhen),
            Builder::location('location')
                ->variant($variant)
                ->placeholder(__p('marketplace::phrase.enter_location'))
                ->yup(
                    Yup::object()
                        ->nullable()
                        ->setError('typeError', __p('marketplace::phrase.location_is_a_required_field'))
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
                )
                ->showWhen($showWhen),
            $this->buildPrivacyField()
                ->variant($variant != 'livestream' ? ($variant . '-inlined') : $variant)
                ->description(__p('livestreaming::phrase.control_who_can_see_this_live_video'))
                ->fullWidth(false)
                ->minWidth(275)
                ->showWhen([
                    'or',
                    ['and', ['falsy', 'post_to'], $showWhen],
                    ['and', ['eq', 'post_to', 'timeline'], $showWhen],
                ]),
        ];
    }

    protected function buildAdditionFields(Section $subHeader, Section $body, array $showWhen, string $variant): void
    {
        if ($this->owner instanceof HasPrivacyMember) {
            return;
        }
        $results = app('events')->dispatch('livestreaming.build_integrate_field', [user(), ['eq', 'post_to', 'timeline'], true]);

        if (!$results) {
            return;
        }
        $fields    = [];
        $subFields = [];
        foreach ($results as $result) {
            if (!is_array($result)) {
                if ($result instanceof SelectSubForm) {
                    $result->showWhen(['falsy', 'sub_form']);
                    $subFields[] = $result;
                    continue;
                }
                if (!$result instanceof AbstractField) {
                    continue;
                }
                $result->variant($variant != 'livestream' ? ($variant . '-inlined') : $variant)
                    ->showWhen($showWhen);
                $fields[] = $result;
                continue;
            }
            foreach ($result as $res) {
                if ($res instanceof SelectSubForm) {
                    $res->showWhen(['falsy', 'sub_form']);
                    $subFields[] = $res;
                    continue;
                }
                if (!$res instanceof AbstractField) {
                    continue;
                }
                $res->variant($variant != 'livestream' ? ($variant . '-inlined') : $variant)
                    ->showWhen($showWhen);
                $fields[] = $res;
            }
        }
        // Add fields
        $subHeader->addFields(...$subFields);
        $body->addFields(...$fields);
    }
}
