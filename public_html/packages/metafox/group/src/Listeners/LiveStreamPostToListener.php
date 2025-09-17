<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Form\Builder;
use MetaFox\Form\Mobile\Builder as MobileBuilder;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\Yup\Yup;

class LiveStreamPostToListener
{
    /**
     * @param array $fields
     * @param array $options
     * @param bool  $mobileForm
     */
    public function handle(array &$fields, array &$options, array $searchParams = [], bool $mobileForm = false): void
    {
        $options[] = ['label' => __p('group::phrase.post_in_group'), 'value' => 'group'];
        if ($mobileForm) {
            $fields[] = MobileBuilder::autocomplete('group_id')
                ->useOptionContext()
                ->label(__p('group::phrase.choose_a_group'))
                ->searchEndpoint('/group-to-post')
                ->searchParams($searchParams)
                ->showWhen([
                    'and',
                    ['eq', 'post_to', 'group'],
                    ['truthy', 'sub_form'],
                ])
                ->margin('none')
                ->requiredWhen(['eq', 'post_to', 'group'])
                ->yup(
                    Yup::number()
                        ->when(
                            Yup::when('post_to')
                                ->is('group')
                                ->then(
                                    Yup::number()->required()
                                )
                        )
                );
        } else {
            $fields[] = Builder::autocomplete('group_id')
                ->useOptionContext()
                ->label(__p('group::phrase.choose_a_group'))
                ->searchEndpoint('/group-to-post')
                ->searchParams($searchParams)
                ->showWhen(['eq', 'post_to', 'group'])
                ->requiredWhen(['eq', 'post_to', 'group'])
                ->yup(
                    Yup::number()
                        ->when(
                            Yup::when('post_to')
                                ->is('group')
                                ->then(
                                    Yup::number()->required()
                                )
                        )
                );
        }
    }
}
