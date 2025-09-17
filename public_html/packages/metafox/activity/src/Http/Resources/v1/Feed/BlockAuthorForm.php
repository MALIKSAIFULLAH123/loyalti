<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Activity\Models\Feed as Model;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacyMember;

/**
 * Class BlockAuthorForm.
 * @property Model $resource
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class BlockAuthorForm extends AbstractForm
{
    /**
     * @var int
     */
    protected int $groupId;

    /**
     * @var int
     */
    protected int $userId;
    protected string $ownerName;
    protected string $userName;

    protected FeedRepositoryInterface $repository;

    /**
     * @throws AuthorizationException
     */
    public function boot(FeedRepositoryInterface $repository, int $id): void
    {
        $this->repository = $repository;
        $this->resource = $this->repository->find($id);
        if (!$this->resource instanceof Content) {
            throw new AuthorizationException();
        }

        if (!$this->resource->owner instanceof HasPrivacyMember) {
            throw new AuthorizationException();
        }

        $this->userName = $this->resource->userEntity->name;
        $this->ownerName = $this->resource->ownerEntity->name;
    }

    protected function prepare(): void
    {
        $this->action("feed/decline/{$this->resource->entityId()}")
            ->asPatch()
            ->title(__p('group::phrase.block_member'))
            ->setValue([
                'is_block_author'   => 1,
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
                ->plainText(__p('group::phrase.block_member_description', [
                    'user_name'  => $this->userName,
                    'group_name' => $this->ownerName,
                ])),
            Builder::hidden('is_block_author'),
            Builder::checkbox('delete_activities')
                ->multiple(false)
                ->label(__p('group::phrase.delete_recent_activity_title'))
                ->description(__p('group::phrase.delete_recent_activity_desc', [
                    'user_name'  => $this->userName,
                    'group_name' => $this->ownerName,
                ]))
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()->sizeSmall()->label(__p('core::phrase.submit')),
                Builder::cancelButton()->sizeSmall(),
            );
    }
}
