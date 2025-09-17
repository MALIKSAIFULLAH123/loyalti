<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Request;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Group\Support\Browse\Scopes\Request\StatusScope;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Yup\Yup;

/**
 * @preload 1
 */
class SearchRequestForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->action('group-request')
            ->acceptPageParams(['q', 'view', 'status', 'start_date', 'end_date'])
            ->navigationConfirmation()
            ->setValue([
                'view'       => Browse::VIEW_ALL,
                'start_date' => null,
                'end_date'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset'])
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm()
                    ->placeholder(__p('core::phrase.search'))
                    ->className('mb2'),
                Builder::choice('status')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.status'))
                    ->options(StatusScope::getStatusOptions()),
                Builder::date('start_date')
                    ->label(__p('core::web.from'))
                    ->startOfDay()
                    ->forAdminSearchForm()
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->yup(
                        Yup::date()->nullable()
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                    ),
                Builder::date('end_date')
                    ->label(__p('core::phrase.to_label'))
                    ->endOfDay()
                    ->forAdminSearchForm()
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->yup(Yup::date()
                        ->nullable()
                        ->min(['ref' => 'start_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                        ->setError('min', __p('group::phrase.the_end_time_should_be_greater_than_the_start_time'))
                        ->setError(
                            'minDateTime',
                            __p('group::phrase.the_end_time_should_be_greater_than_the_current_time')
                        )),
                Builder::submit()
                    ->label(__p('core::phrase.submit'))
                    ->forAdminSearchForm(),
                Builder::clearSearchForm()
                    ->label(__p('core::phrase.reset'))
                    ->forAdminSearchForm()
                    ->excludeFields(['view'])
                    ->align('right'),
            );
    }

    protected function getResponsiveSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '50%',
                'md' => '220px',
            ],
            'width' => [
                'xs' => '100%',
                'sm' => '50%',
            ],
        ];
    }
}
