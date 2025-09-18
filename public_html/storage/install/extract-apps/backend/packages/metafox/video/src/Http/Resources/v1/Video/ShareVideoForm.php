<?php

namespace MetaFox\Video\Http\Resources\v1\Video;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Video\Http\Requests\v1\Video\CreateFormRequest;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Policies\VideoPolicy;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;
use MetaFox\Video\Support\VideoSupport;
use MetaFox\Video\Traits\MatureFieldTrait;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ShareVideoForm.
 * @property ?Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ShareVideoForm extends AbstractForm
{
    use PrivacyFieldTrait;
    use MatureFieldTrait;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(CreateFormRequest $request): void
    {
        $context = user();
        $params  = $request->validated();

        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }

        app('quota')->checkQuotaControlWhenCreateItem($context, Model::ENTITY_TYPE, 1, ['messageFormat' => 'text']);
        policy_authorize(VideoPolicy::class, 'create', $context);
        $this->resource = new Model($params);
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

        $defaultCategory = Settings::get('video.default_category');

        $this->title(__p('video::phrase.share_a_video'))
            ->action(url_utility()->makeApiUrl('video'))
            ->setBackProps(__p('video::phrase.videos'))
            ->asPost()
            ->setValue([
                'module_id'  => 'video',
                'privacy'    => $privacy,
                'owner_id'   => $this->resource?->owner_id ?? 0,
                'categories' => $defaultCategory ? [$defaultCategory] : [],
            ]);
    }

    protected function initialize(): void
    {
        $minVideoNameLength = Settings::get('video.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxVideoNameLength = Settings::get('video.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
        $providers          = VideoSupport::SUPPORTED_PLATFORMS;
        $lastProvider       = array_pop($providers);

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
                    'text'  => ':description',
                ])
                ->fetchEndpoint(apiUrl('video.fetch'))
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->format('url', __p('video::phrase.this_field_must_be_valid_url', ['attribute' => $videoUrl->getLabel()]))
                ),
            Builder::text('title')
                ->required()
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('video::phrase.fill_in_a_title_for_your_video'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxVideoNameLength]))
                ->maxLength($maxVideoNameLength)
                ->yup(
                    Yup::string()
                        ->minLength($minVideoNameLength)
                        ->maxLength($maxVideoNameLength)
                        ->required(__p('validation.this_field_is_a_required_field'))
                ),
            $this->buildTextField(),
            Builder::category('categories')
                ->multiple(true)
                ->sizeLarge()
                ->fullWidth()
                ->minWidth(275)
                ->setRepository(CategoryRepositoryInterface::class),
            $this->buildMatureField(true),
            $this->buildPrivacyField()
                ->fullWidth(false)
                ->minWidth(275)
                ->description(__p('video::phrase.control_who_can_see_this_video')),
            Builder::hidden('module_id')
                ->setValue('video'),
            Builder::hidden('owner_id'),
        );

        $submitLabel = __p('core::phrase.upload');

        if (null !== $this->resource && $this->resource->id) {
            $submitLabel = __p('core::phrase.save_changes');
        }

        $this->addFooter()
            ->addFields(
                Builder::submit('__submit')->label($submitLabel),
                Builder::cancelButton()->sizeMedium(),
            );
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->required(false)
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('video::phrase.add_some_content_to_your_video'));
        }

        return Builder::textArea('text')
            ->required(false)
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('video::phrase.add_some_content_to_your_video'));
    }
}
