<?php

namespace MetaFox\Video\Http\Resources\v1\Video;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
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
 * Class ShareVideoComposerMobileForm.
 * @property ?Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ShareVideoComposerMobileForm extends AbstractForm
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
                'video_url' => request()->get('video_url'),
                'privacy'   => $privacy,
            ]);
    }

    protected function initialize(): void
    {
        $minVideoNameLength = Settings::get('video.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxVideoNameLength = Settings::get('video.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
        $providers          = VideoSupport::SUPPORTED_PLATFORMS;
        $lastProvider       = array_pop($providers);

        $basic = $this->addBasic();

        $basic->addFields(
            Builder::singleVideo('video_url')
                ->required()
                ->fileType('link')
                ->fetchEndpoint(apiUrl('video.fetch'))
                ->label(__p('video::phrase.video_url'))
                ->description(__p('video::phrase.share_video_url_description', [
                    'providers' => implode(', ', $providers),
                    'last'      => $lastProvider,
                ]))
                ->autoFillValue([
                    'title' => ':title',
                ])
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->format('url', __p('video::phrase.this_field_must_be_valid_url', ['attribute' => __p('video::phrase.video_url')]))
                ),
            Builder::text('title')
                ->required()
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('video::phrase.fill_in_a_title_for_your_video'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxVideoNameLength]))
                ->maxLength($maxVideoNameLength)
                ->showWhen([
                    'and',
                    ['$exists', 'title'],
                ])
                ->yup(
                    Yup::string()
                        ->minLength($minVideoNameLength)
                        ->maxLength($maxVideoNameLength)
                        ->required(__p('validation.this_field_is_a_required_field'))
                ),
        );
    }
}
