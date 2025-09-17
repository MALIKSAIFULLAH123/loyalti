<?php

namespace MetaFox\Event\Http\Resources\v1\InviteCode;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Event\Http\Requests\v1\InviteCode\StoreRequest;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * Class CreateForm.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @driverName event.event_code.generated
 */
class GenerateInviteForm extends AbstractForm
{
    /**
     * @var int
     */
    protected int                           $eventId;
    protected InviteCodeRepositoryInterface $codeRepository;
    protected EventRepositoryInterface      $repository;

    public function boot(
        StoreRequest                  $request,
        InviteCodeRepositoryInterface $codeRepository,
        EventRepositoryInterface      $repository
    ): void
    {
        $params = $request->validated();

        $this->eventId = Arr::get($params, 'event_id');

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
            ->action('event-code')
            ->setValue([
                'code' => $this->getCode(),
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::hidden('event_id'),
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
        $event      = $this->repository->find($this->eventId);
        $context    = user();
        $inviteCode = $this->codeRepository->getCode($context, $event);

        return $inviteCode?->code;
    }
}
