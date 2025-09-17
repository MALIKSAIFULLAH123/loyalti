<?php

namespace MetaFox\Video\Http\Resources\v1\Video;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Video\Http\Requests\v1\Video\CreateFormRequest;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Policies\VideoPolicy;
use MetaFox\Video\Support\VideoSupport;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ShareVideoComposerForm.
 * @property ?Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ShareVideoComposerForm extends AbstractForm
{
    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(CreateFormRequest $request): void
    {
        $context = user();

        app('quota')->checkQuotaControlWhenCreateItem($context, Model::ENTITY_TYPE, 1, ['messageFormat' => 'text']);
        policy_authorize(VideoPolicy::class, 'create', $context);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'video.item_privacy');
        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }

        $this->title(__p('video::phrase.share_video_url'))
            ->action(url_utility()->makeApiUrl('video'))
            ->setBackProps(__p('video::phrase.videos'))
            ->asPost()
            ->setValue([
                'privacy' => $privacy,
            ]);
    }

    protected function initialize(): void
    {
        $providers          = VideoSupport::SUPPORTED_PLATFORMS;
        $lastProvider       = array_pop($providers);
        $minVideoNameLength = Settings::get('video.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxVideoNameLength = Settings::get('video.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);

        $basic    = $this->addBasic();
        $videoUrl = Builder::videoUrl();
        $basic->addFields(
            $videoUrl->required()
                ->description(__p('video::phrase.share_video_url_description', [
                    'providers' => implode(', ', $providers),
                    'last'      => $lastProvider,
                ]))
                ->autoFillValue([
                    'title' => ':title',
                ])
                ->fetchEndpoint(apiUrl('video.fetch'))
                ->showEmbed()
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->format('url', __p('video::phrase.this_field_must_be_valid_url', ['attribute' => $videoUrl->getLabel()]))
                ),
            Builder::text('title')
                ->required()
                ->label(__p('core::phrase.title'))
                ->showWhen([
                    'and',
                    ['exists', '$.title'],
                ])
                ->placeholder(__p('video::phrase.fill_in_a_title_for_your_video'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxVideoNameLength]))
                ->maxLength($maxVideoNameLength)
                ->yup(
                    Yup::string()
                        ->minLength($minVideoNameLength)
                        ->maxLength($maxVideoNameLength)
                        ->required(__p('validation.this_field_is_a_required_field'))
                ),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit('__submit')
                    ->label(__p('video::phrase.share'))
                    ->enableWhen(['truthy', 'title']),
                Builder::cancelButton()->sizeMedium(),
            );
    }
}
