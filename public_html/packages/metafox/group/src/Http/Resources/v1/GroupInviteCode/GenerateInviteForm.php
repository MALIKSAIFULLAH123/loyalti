<?php

namespace MetaFox\Group\Http\Resources\v1\GroupInviteCode;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Group\Http\Requests\v1\GroupInviteCode\StoreRequest;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupInviteCodeRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;

/**
 * Class CreateForm.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenerateInviteForm extends AbstractForm
{
    /**
     * @var int
     */
    protected int                                $groupId;
    protected GroupInviteCodeRepositoryInterface $codeRepository;
    protected GroupRepositoryInterface           $repository;

    public function boot(
        StoreRequest                       $request,
        GroupInviteCodeRepositoryInterface $codeRepository,
        GroupRepositoryInterface           $repository
    ): void
    {
        $params = $request->validated();

        $this->groupId = Arr::get($params, 'group_id');

        $this->codeRepository = $codeRepository;
        $this->repository     = $repository;
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $this->asPost()
            ->title(__p('core::phrase.generate_link_code'))
            ->action('group/invite-code')
            ->setValue([
                'code' => $this->getCode(),
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::hidden('group_id'),
            Builder::text('code')
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('core::phrase.copy')),
            );
    }

    /**
     * @throws AuthenticationException
     */
    protected function getCode(): ?string
    {
        $group      = $this->repository->find($this->groupId);
        $context    = user();
        $inviteCode = $this->codeRepository->getCode($context, $group);

        return $inviteCode?->code;
    }
}
