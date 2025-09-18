<?php

namespace MetaFox\Page\Http\Resources\v1\Block;

use Exception;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Page\Http\Requests\v1\Block\StoreRequest;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;

class BlockMemberForm extends AbstractForm
{
    /**
     * @var int
     */
    protected int $pageId;

    /**
     * @var int
     */
    protected int    $userId;
    protected string $pageName;
    protected string $userName;

    protected PageRepositoryInterface $pageRepository;

    public function boot(StoreRequest $request, PageRepositoryInterface $pageRepository): void
    {
        $this->pageRepository = $pageRepository;
        $params               = $request->validated();

        $this->pageId    = Arr::get($params, 'page_id');
        $this->userId    = Arr::get($params, 'user_id');
        $this->pageName = $this->pageRepository->find($this->pageId)->toTitle();
        $userEntity      = UserEntity::getById($this->userId);
        $this->userName  = $userEntity->detail->full_name;
    }

    protected function prepare(): void
    {
        $this->action('page-block')
            ->asPost()
            ->title(__p('page::phrase.block_member_confirm_title'))
            ->setValue([
                'page_id'          => $this->pageId,
                'user_id'           => $this->userId,
                'delete_activities' => 0,
            ]);
    }

    /**
     * @throws Exception
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::typography()
                ->color('text.hint')
                ->plainText(__p('page::phrase.block_member_confirm_desc', [
                    'user_name'  => $this->userName,
                    'page_name' => $this->pageName,
                ])),
            Builder::hidden('page_id'),
            Builder::hidden('user_id'),
            Builder::checkbox('delete_activities')
                ->multiple(false)
                ->label(__p('page::phrase.delete_recent_activity_title'))
                ->description(__p('page::phrase.delete_recent_activity_desc', [
                    'user_name'  => $this->userName,
                    'page_name' => $this->pageName,
                ]))
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()->sizeSmall()->label(__p('core::phrase.submit')),
                Builder::cancelButton()->sizeSmall(),
            );
    }
}
