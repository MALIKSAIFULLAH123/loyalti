<?php

namespace MetaFox\Blog\Http\Resources\v1\Blog;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Blog\Http\Requests\v1\Blog\CreateFormRequest;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Blog\Policies\BlogPolicy;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Blog\Repositories\CategoryRepositoryInterface;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
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
 * Class StoreBlogForm.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreBlogForm extends AbstractForm
{
    use PrivacyFieldTrait;

    public bool $preserveKeys = true;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, BlogRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();

        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }

        app('quota')->checkQuotaControlWhenCreateItem($context, Model::ENTITY_TYPE, 1, ['messageFormat' => 'text']);
        policy_authorize(BlogPolicy::class, 'create', $context, $this->owner);
        $this->resource = new Model($params);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'blog.item_privacy');
        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }
        $defaultCategory = Settings::get('blog.default_category');

        $this->title(__p('blog::phrase.add_new_blog'))
            ->action(url_utility()->makeApiUrl('blog'))
            ->asPost()
            ->setBackProps(__p('core::web.blog'))
            ->setValue([
                'title'       => '',
                'module_id'   => 'blog',
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
        $minBlogTitleLength = Settings::get('blog.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxBlogTitleLength = Settings::get('blog.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
        $privacyField       = $this->buildPrivacyField()
            ->description(__p('blog::phrase.control_who_can_see_this_blog'));

        $basic->addFields(
            Builder::text('title')
                ->required()
                ->marginNormal()
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('blog::phrase.fill_in_a_title_for_your_blog'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxBlogTitleLength]))
                ->maxLength($maxBlogTitleLength)
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(
                            $minBlogTitleLength,
                            __p(
                                'core::validation.title_minimum_length_of_characters',
                                ['number' => $minBlogTitleLength]
                            )
                        )
                        ->maxLength(
                            $maxBlogTitleLength,
                            __p('core::validation.title_maximum_length_of_characters', [
                                'min' => $minBlogTitleLength,
                                'max' => $maxBlogTitleLength,
                            ])
                        )
                ),
            Builder::singlePhoto()
                ->widthPhoto('200px')
                ->aspectRatio('16:9')
                ->itemType('blog')
                ->thumbnailSizes($this->resource->getSizes())
                ->previewUrl($this->resource->image),
            $this->buildTextField(),
            Builder::attachment()
                ->itemType('blog'),
            $this->buildCategoryField(),
            $this->buildTagField(),
            Builder::hidden('module_id'),
            Builder::hidden('owner_id'),
            $privacyField,
            Captcha::getFormField('blog.create_blog')
        );

        $this->addFooter()
            ->addFields(
                $this->buildPublishButton(),
                $this->buildSaveAsDraftButton(),
                Builder::cancelButton()
                    ->sizeMedium(),
            );

        // force returnUrl as string
        $basic->addField(
            Builder::hidden('returnUrl')
        );
    }

    protected function buildPublishButton(): AbstractField
    {
        return Builder::submit()
            ->label(__p('core::phrase.publish'))
            ->flexWidth(true);
    }

    protected function buildSaveAsDraftButton(): AbstractField
    {
        return Builder::submit('draft')
            ->label(__p('core::phrase.save_as_draft'))
            ->color('primary')
            ->setValue(1)
            ->variant('outlined')
            ->showWhen(['falsy', 'published']);
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

    protected function buildCategoryField(): AbstractField
    {
        $field = Builder::category('categories')
            ->multiple()
            ->sizeLarge()
            ->setRepository(CategoryRepositoryInterface::class);

        if ($this->isEdit()) {
            $field->setSelectedCategories($this->resource->categories);
        }

        return $field;
    }

    protected function isEdit(): bool
    {
        return $this->resource && $this->resource->entityId();
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->required()
                ->label(__p('core::phrase.post'))
                ->placeholder(__p('blog::phrase.add_some_content_to_your_blog'))
                ->yup(
                    Yup::string()
                        ->required()
                );
        }

        return Builder::textArea('text')
            ->required()
            ->label(__p('core::phrase.post'))
            ->placeholder(__p('blog::phrase.add_some_content_to_your_blog'))
            ->yup(
                Yup::string()
                    ->required()
            );
    }
}
