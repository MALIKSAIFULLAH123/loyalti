<?php

namespace MetaFox\Page\Http\Resources\v1\PageMember;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class ReassignOwnerMobileForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class ReassignOwnerMobileForm extends AbstractForm
{
    public function boot(PageRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('page::phrase.select_an_admin_to_reassign'))
            ->action('/page-member/reassign-owner')
            ->asPut()
            ->setValue([
                'page_id' => $this->resource->entityId(),
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::friendPicker('users')
                    ->setComponent('InviteFriendPicker')
                    ->endpoint('page-member')
                    ->params([
                        'page_id'          => $this->resource->entityId(),
                        'view'             => 'admin',
                        'excluded_user_id' => ':owner_id',
                    ])
                    ->required()
                    ->multiple(false)
                    ->placeholder(__p('page::phrase.search_for_a_admin'))
                    ->yup(Yup::array()->required()),
                Builder::hidden('page_id'),
            );
    }
}
