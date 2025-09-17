<?php

namespace MetaFox\Announcement\Http\Resources\v1\Announcement\Admin;

use MetaFox\Announcement\Models\Announcement as Model;
use MetaFox\Announcement\Support\Facade\Announcement;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchAnnouncementForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchAnnouncementForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action('/announcement')
            ->acceptPageParams(['q', 'style', 'start_from', 'start_to', 'created_from', 'created_to', 'role_id'])
            ->setValue([
                'start_from'   => null,
                'start_to'     => null,
                'created_from' => null,
                'created_to'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal()->sxContainer(['alignItems' => 'unset']);
        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm()
                ->placeholder(__p('localize::phrase.search_dot')),
            Builder::choice('role_id')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.role'))
                ->options(Announcement::getAllowedRoleOptions()),
            Builder::choice('style')
                ->forAdminSearchForm()
                ->label(__p('announcement::phrase.announcement_style'))
                ->options(Announcement::getStyleOptions()),
            Builder::date('start_from')
                ->forAdminSearchForm()
                ->label(__p('announcement::phrase.start_from'))
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('announcement::phrase.start_from')]))
                ),
            Builder::date('start_to')
                ->forAdminSearchForm()
                ->label(__p('announcement::phrase.start_to'))
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'start_from'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('announcement::phrase.start_to')]))
                        ->setError('min', __p('announcement::phrase.the_end_time_should_be_greater_than_the_start_time', [
                            'start_time' => __p('announcement::phrase.start_from'),
                            'end_time'   => __p('announcement::phrase.start_to'),
                        ]))
                ),
            Builder::date('created_from')
                ->forAdminSearchForm()
                ->label(__p('announcement::phrase.created_from'))
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('announcement::phrase.created_from')]))
                ),
            Builder::date('created_to')
                ->forAdminSearchForm()
                ->label(__p('announcement::phrase.created_to'))
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'created_from'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('announcement::phrase.created_to')]))
                        ->setError('min', __p('announcement::phrase.the_end_time_should_be_greater_than_the_start_time', [
                            'start_time' => __p('announcement::phrase.created_from'),
                            'end_time'   => __p('announcement::phrase.created_to'),
                        ]))
                ),
            Builder::submit()->forAdminSearchForm(),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->marginDense()
                ->align('right')
                ->excludeFields(['view_more'])
                ->sxFieldWrapper([
                    'p' => 1,
                ]),
        );
    }
}
