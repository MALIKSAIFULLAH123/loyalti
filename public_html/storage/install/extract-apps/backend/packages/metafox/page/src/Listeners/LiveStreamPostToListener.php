<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Listeners;

use MetaFox\Form\Builder;
use MetaFox\Form\Mobile\Builder as MobileBuilder;
use MetaFox\Yup\Yup;

/**
 * Class LiveStreamPostToListener.
 * @ignore
 */
class LiveStreamPostToListener
{
    /**
     * @param array $fields
     * @param array $options
     * @param bool  $mobileForm
     */
    public function handle(array &$fields, array &$options, array $searchParams = [], bool $mobileForm = false): void
    {
        $options[] = ['label' => __p('page::phrase.post_in_page'), 'value' => 'page'];
        if ($mobileForm) {
            $fields[] = MobileBuilder::autocomplete('page_id')
                ->useOptionContext()
                ->label(__p('page::phrase.choose_a_page'))
                ->searchEndpoint('/page-to-post')
                ->searchParams($searchParams)
                ->showWhen([
                    'and',
                    ['eq', 'post_to', 'page'],
                    ['truthy', 'sub_form'],
                ])
                ->margin('none')
                ->requiredWhen(['eq', 'post_to', 'page'])
                ->yup(
                    Yup::number()
                        ->when(
                            Yup::when('post_to')
                                ->is('page')
                                ->then(
                                    Yup::number()->required()
                                )
                        )
                );
        } else {
            $fields[] = Builder::autocomplete('page_id')
                ->useOptionContext()
                ->label(__p('page::phrase.choose_a_page'))
                ->searchEndpoint('/page-to-post')
                ->searchParams($searchParams)
                ->showWhen(['eq', 'post_to', 'page'])
                ->requiredWhen(['eq', 'post_to', 'page'])
                ->yup(
                    Yup::number()
                        ->when(
                            Yup::when('post_to')
                                ->is('page')
                                ->then(
                                    Yup::number()->required()
                                )
                        )
                );
        }
    }
}
