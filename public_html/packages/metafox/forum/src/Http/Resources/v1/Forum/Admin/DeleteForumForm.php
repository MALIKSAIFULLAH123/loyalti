<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum\Admin;

use Illuminate\Support\Arr;
use MetaFox\Forum\Repositories\ForumAdminRepositoryInterface;
use MetaFox\Forum\Support\ForumSupport as Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Forum\Models\Forum as Model;
use MetaFox\Forum\Policies\ForumPolicy;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
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
class DeleteForumForm extends AbstractForm
{
    protected function prepare(): void
    {
        $values = [
            'id' => $this->resource->entityId(),
        ];

        if ($this->isPermanentlyDelete()) {
            Arr::set($values, 'delete_option', Support::DELETE_PERMANENTLY);
        }

        $this->title(__p('forum::phrase.delete_forum'))
            ->asPost()
            ->action('admincp/forum/forum/delete')
            ->setValue($values);
    }

    protected function isPermanentlyDelete(): bool
    {
        return !$this->resource->subForums()->exists() && !$this->resource->threads()->exists();
    }

    protected function initialize(): void
    {
        $this->addBasicFields();

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.delete')),
                Builder::cancelButton(),
            );
    }

    protected function addBasicFields(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::typography('delete_question')
                ->tagName('strong')
                ->plainText(__p('forum::phrase.delete_forum_confirm', ['name' => $this->resource->toTitle()])),
        );

        if ($this->isPermanentlyDelete()) {
            return;
        }

        $basic->addFields(
            Builder::description('delete_notice')
                ->label(__p('core::phrase.action_cant_be_undone')),
            Builder::radioGroup('delete_option')
                ->label(__p('forum::phrase.delete_option_label'))
                ->required()
                ->options($this->getDeleteOptions())
                ->yup(
                    Yup::string()
                        ->required(__p('authorization::phrase.delete_option_is_a_required_field'))
                        ->setError('typeError', __p('authorization::phrase.delete_option_is_a_required_field'))
                ),
            Builder::choice('alternative_id')
                ->options($this->getForumOptions())
                ->requiredWhen([
                    'eq',
                    'delete_option',
                    Support::DELETE_MIGRATION,
                ])
                ->showWhen([
                    'eq',
                    'delete_option',
                    Support::DELETE_MIGRATION,
                ])
                ->label(__p('forum::phrase.alternative_forum'))
                ->yup(
                    Yup::number()
                        ->when(
                            Yup::when('delete_option')
                                ->is(Support::DELETE_MIGRATION)
                                ->then(
                                    Yup::number()
                                        ->required(__p('forum::phrase.alternative_forum_is_a_required_field'))
                                        ->setError('typeError', __p('forum::phrase.alternative_forum_is_a_required_field'))
                                )
                        )
                ),
        );
    }

    /**
     * @return array<string,mixed>
     */
    protected function getParentOptions(): array
    {
        $context = user();

        return resolve(ForumRepositoryInterface::class)->getForumsForForm($context, $this->resource);
    }

    /**
     * @return array
     */
    protected function getDeleteOptions(): array
    {
        $alternativeForums = $this->getForumOptions();

        $options = [
            [
                'label' => __p('forum::phrase.delete_all_items'),
                'value' => Support::DELETE_PERMANENTLY,
            ],
        ];

        if (count($alternativeForums)) {
            $options[] = [
                'label' => __p('forum::phrase.delete_move_all_items'),
                'value' => Support::DELETE_MIGRATION,
            ];
        }

        return $options;
    }

    protected function getForumOptions(): array
    {
        return resolve(ForumAdminRepositoryInterface::class)->getForumsForDeleteOption($this->resource);
    }

    public function boot(?int $id = null)
    {
        $context = user();

        $this->resource = resolve(ForumRepositoryInterface::class)->find($id);

        policy_authorize(ForumPolicy::class, 'delete', $context, $this->resource);
    }
}
