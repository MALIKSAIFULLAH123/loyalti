<?php

namespace MetaFox\Like\Http\Resources\v1\Reaction\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Like\Models\Reaction as Model;
use MetaFox\Like\Repositories\ReactionAdminRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateReactionForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateReactionForm extends AbstractForm
{
    protected const MAX_LENGTH_TITLE = 32;

    protected const MIN_LENGTH_TITLE = 3;
    public const    PHOTO_MINE_TYPES = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'];

    public function boot(ReactionAdminRepositoryInterface $repository, ?int $id = null): void
    {
    }

    protected function prepare(): void
    {
        $this->action(apiUrl('admin.like.reaction.store'))
            ->asPost()
            ->setValue([
                'is_active' => 0,
                'icon_font' => Model::ICON_FONT_DEFAULT,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::translatableText('title')
                ->required()
                ->maxLength(self::MAX_LENGTH_TITLE)
                ->minLength(self::MIN_LENGTH_TITLE)
                ->forward('maxLength', self::MAX_LENGTH_TITLE)
                ->label(__p('like::phrase.reaction_label'))
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(self::MIN_LENGTH_TITLE)
                        ->maxLength(self::MAX_LENGTH_TITLE)
                )
                ->buildFields(),
            Builder::singlePhoto('image')
                ->required()
                ->label(__p('like::phrase.add_image'))
                ->accepts(implode(',', self::PHOTO_MINE_TYPES))
                ->acceptFail(__p('like::phrase.reaction_accept_type_fail'))
                ->itemType('reaction')
                ->uploadUrl('file')
                ->previewUrl($this->resource?->image)
                ->yup(Yup::object()
                    ->required()
                    ->addProperty('id', Yup::number()
                        ->required(__p('like::validation.icon_is_a_required_field')))),
            Builder::colorPicker('color')
                ->width(200)
                ->label(__p('core::web.text_color')),
            Builder::text('icon_font')
                ->component('IconPicker')
                ->required(false)
                ->label(__p('app::phrase.icon'))
                ->width(200)
                ->maxLength(255),
            Builder::checkbox('is_active')
                ->label(__p('core::phrase.is_active'))
                ->disabled($this->isDisableActiveField()),
        );

        if ($this->hasActivatedThanLimit()) {
            $basic->addField(
                Builder::typography()
                    ->plainText(__p('like::phrase.you_can_only_enable_numbers_reaction', ['numbers' => Model::LIMIT_ACTIVE_REACTION]))
                    ->sx(['mt' => 0])
                    ->setAttributes(['fontSize' => 13])
                    ->color('warning.main'),
            );
        }

        $this->addDefaultFooter();
    }

    public function hasActivatedThanLimit(): bool
    {
        /** @var ReactionAdminRepositoryInterface $repository */
        $repository     = resolve(ReactionAdminRepositoryInterface::class);
        $totalActivated = $repository->getTotalReactionActive();

        return $totalActivated >= Model::LIMIT_ACTIVE_REACTION;
    }

    public function isDisableActiveField(): bool
    {
        if (!$this->resource) {
            return $this->hasActivatedThanLimit();
        }

        if ($this->resource->is_default) {
            return true;
        }

        if (!$this->hasActivatedThanLimit()) {
            return false;
        }

        /*
         * In case a bug exists with over total limit, we only allow to deactivate if this resource is already active.
         */
        if ($this->resource->is_active) {
            return false;
        }

        return true;
    }
}
