<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Foxexpert\Sevent\Http\Requests\v1\Sevent\CreateFormRequest;
use Foxexpert\Sevent\Models\Sevent as Model;
use Foxexpert\Sevent\Policies\SeventPolicy;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\PrivacyFieldMobileTrait;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Yup\Yup;

/**
 * class StoreSeventMobileForm.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreSeventMobileForm extends AbstractForm
{
    use PrivacyFieldMobileTrait;

    public bool $preserveKeys = true;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, SeventRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();

        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }
        policy_authorize(SeventPolicy::class, 'create', $context, $this->owner);
        $this->resource = new Model($params);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'sevent.item_privacy');
        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }
        $defaultCategory = Settings::get('sevent.default_category');

        $this->title(__p('sevent::phrase.add_new_sevent'))
            ->action('sevent')
            ->asPost()
            ->setValue([
                'module_id'   => 'sevent',
                'privacy'     => $privacy,
                'draft'       => 0,
                'tags'        => [],
                'owner_id'    => $this->resource->owner_id,
                'attachments' => [],
                'categories'  => [$defaultCategory],
            ]);
    }

    protected function initialize(): void
    {
        $basic              = $this->addBasic();
        $minSeventTitleLength = Settings::get('sevent.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxSeventTitleLength = Settings::get('sevent.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);

        $basic->addFields(
            Builder::text('title')
                ->required()
                ->marginNormal()
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('sevent::phrase.fill_in_a_title_for_your_sevent'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxSeventTitleLength]))
                ->maxLength($maxSeventTitleLength)
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(
                            $minSeventTitleLength,
                            __p(
                                'core::validation.title_minimum_length_of_characters',
                                ['number' => $minSeventTitleLength]
                            )
                        )
                        ->maxLength(
                            $maxSeventTitleLength,
                            __p('core::validation.title_maximum_length_of_characters', [
                                'min' => $minSeventTitleLength,
                                'max' => $maxSeventTitleLength,
                            ])
                        )
                ),
            Builder::singlePhoto('file')
                ->itemType('sevent')
                ->label(__p('photo::phrase.photo'))
                ->previewUrl($this->resource->image),
            Builder::richTextEditor('text')
                ->required()
                ->asMultiLine()
                ->textAlignVertical('top')
                ->label(__p('core::phrase.post'))
                ->placeholder(__p('sevent::phrase.add_some_content_to_your_sevent'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            // Builder::attachment()->itemType('sevent'),
            Builder::category('categories')
                ->multiple(true)
                ->sizeLarge()
                ->setRepository(CategoryRepositoryInterface::class),
            $this->buildTagField(),
            Builder::checkbox('draft')
                ->label(__p('core::phrase.save_as_draft'))
                ->variant('standard-inlined')
                ->showWhen(['falsy', 'published']),
            Builder::hidden('module_id'),
            Builder::hidden('owner_id'),
        );

        // Handle build privacy field with custom criteria
        $basic->addField(
            $this->buildPrivacyField()
                ->description(__p('sevent::phrase.control_who_can_see_this_sevent'))
        );
    }

    protected function buildTagField(): ?AbstractField
    {
        if ($this->owner instanceof HasPrivacyMember) {
            return null;
        }

        return Builder::tags()
            ->label(__p('core::phrase.topics'))
            ->placeholder(__p('core::phrase.keywords'));
    }
}
