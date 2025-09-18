<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Form\Section;
use MetaFox\LiveStreaming\Form\Html\WebcamPlayer;
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
class StoreLiveVideoWebcamForm extends StoreLiveVideoForm
{
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
                Builder::hidden('stream_key')
                    ->label(__p('livestreaming::phrase.stream_key'))
                    ->readOnly(),
                Builder::hidden('server_url')
                    ->label(__p('livestreaming::phrase.server_url'))
                    ->readOnly(),
                (new WebcamPlayer())
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
                $this->isEdit() ? $builderSubmitBtn : $builderSubmitBtn->enableWhen(['exists', 'webcam_player']),
                Builder::cancelButton()->sizeMedium(),
            );
    }
}
