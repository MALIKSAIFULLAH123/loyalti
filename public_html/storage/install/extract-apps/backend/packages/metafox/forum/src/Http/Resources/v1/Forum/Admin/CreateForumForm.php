<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Forum\Models\Forum as Model;
use MetaFox\Forum\Policies\ForumPolicy;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateForumForm.
 * @property ?Model $resource
 */
class CreateForumForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('forum::phrase.create_forum'))
            ->asPost()
            ->action('admincp/forum/forum')
            ->setValue([
                'is_closed' => 0,
                'parent_id' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::translatableText('title')
                    ->label(__p('core::phrase.title'))
                    ->required()
                    ->maxLength(ForumSupport::MAX_FORUM_TITLE_LEMGTH)
                    ->yup(
                        Yup::string()
                            ->required(__p('core::phrase.title_is_a_required_field'))
                            ->maxLength(
                                ForumSupport::MAX_FORUM_TITLE_LEMGTH,
                                __p('forum::validation.admincp.maximum_name_length', [
                                    'number' => $this->getMaxTitleLength(),
                                ])
                            )
                    )->buildFields(),
                Builder::translatableText('description')
                    ->label(__p('core::phrase.description'))
                    ->buildFields(),
                Builder::choice('parent_id')
                    ->label(__p('forum::phrase.parent_forum'))
                    ->options($this->getParentOptions()),
                Builder::checkbox('is_closed')
                    ->label(__p('core::web.closed')),
            );

        $this->addDefaultFooter($this->isEdit());
    }

    /**
     * @return array<string,mixed>
     */
    protected function getParentOptions(): array
    {
        $context = user();

        return resolve(ForumRepositoryInterface::class)->getForumsForForm($context, $this->resource, false);
    }

    protected function isEdit(): bool
    {
        return false;
    }

    public function boot(?int $id = null)
    {
        $context = user();

        policy_authorize(ForumPolicy::class, 'create', $context);
    }
}
