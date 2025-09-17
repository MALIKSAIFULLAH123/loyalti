<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * Class BuiltinSearchForm.
 *
 * Generic search form class.
 * @driverName ignore
 */
class BuiltinSearchForm extends AbstractForm
{
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addField(
            Builder::text('q')
                ->label('Keywords')
                ->placeholder(__p('localize::phrase.search_dot'))
        );

        $basic->addField(
            Builder::choice('sort')
                ->label(__p('web.sort'))
                ->options(
                    [
                        ['value' => 'latest', 'label' => 'Latest'],
                        ['value' => 'most_viewed', 'label' => 'Most Viewed'],
                        ['value' => 'most_liked', 'label' => 'Most Liked'],
                        ['value' => 'most_discussed', 'label' => 'Most Discussed'],
                    ]
                )
        );

        $basic->addField(
            Builder::choice('when')
                ->label('When')
                ->setValue('all-time')
                ->options([
                    [
                        'value' => 'all-time',
                        'label' => 'All Time',
                    ],
                    [
                        'value' => 'today',
                        'label' => 'Today',
                    ],
                    [
                        'value' => 'this-week',
                        'label' => 'This Week',
                    ],
                    [
                        'value' => 'this-month',
                        'label' => 'This Month',
                    ],
                ])
        );

        $basic->addField(
            Builder::submit()
                ->label(__p('core::phrase.search'))
        );
    }
}
