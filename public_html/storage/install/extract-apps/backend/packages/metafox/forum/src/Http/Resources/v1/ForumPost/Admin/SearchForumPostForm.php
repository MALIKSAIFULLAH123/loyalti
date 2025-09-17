<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Forum\Models\ForumPost as Model;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Support\Browse\Scopes\PostViewAdminScope;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class SearchForumPostForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchForumPostForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('forum/forum-post/browse')
            ->acceptPageParams(['q', 'user_name', 'view', 'forum_id', 'thread_name', 'created_from', 'created_to'])
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->setValue([
                'view'         => PostViewAdminScope::VIEW_DEFAULT,
                'created_from' => null,
                'created_to'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal()->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::text('q')
                ->label(__p('core::phrase.content_label'))
                ->placeholder(__p('core::phrase.content_label'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::text('user_name')
                ->label(__p('core::phrase.posted_by'))
                ->placeholder(__p('core::phrase.posted_by'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::text('thread_name')
                ->label(__p('forum::phrase.thread'))
                ->placeholder(__p('forum::phrase.thread'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::choice('view')
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense()
                ->label(__p('core::phrase.view'))
                ->options(PostViewAdminScope::getViewOptions()),
            Builder::category('forum_id')
                ->sxFieldWrapper(['maxWidth' => 220])
                ->sizeSmall()
                ->multiple(false)
                ->marginDense()
                ->label(__p('forum::phrase.forum'))
                ->setAttribute('options', $this->getForumOptions()),
            Builder::date('created_from')
                ->label(__p('core::phrase.created_from'))
                ->startOfDay()
                ->forAdminSearchForm()
                ->yup(Yup::date()->nullable()
                    ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.created_from')]))),
            Builder::date('created_to')
                ->label(__p('core::phrase.created_to'))
                ->endOfDay()
                ->forAdminSearchForm()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'created_from'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.created_to')]))
                        ->setError('min', __p('validation.the_end_time_should_be_greater_than_the_start_time', [
                            'end_time'   => __p('core::phrase.created_to'),
                            'start_time' => __p('core::phrase.created_from'),
                        ]))
                ),
            Builder::submit()
                ->forAdminSearchForm(),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->align('center')
                ->forAdminSearchForm()
                ->sizeMedium(),
        );
    }

    protected function getForumOptions(): array
    {
        /**@var $forumRepository ForumRepositoryInterface */
        $forumRepository = resolve(ForumRepositoryInterface::class);

        return $forumRepository->getForumOptions();
    }
}
