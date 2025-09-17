<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\User\Models\CancelReason;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Repositories\CancelReasonAdminRepositoryInterface;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 *
 * @driverType form-mobile
 * @driverName user.account.cancel
 * @property Model $resource
 */
class AccountCancelMobileForm extends AbstractForm
{
    public function boot(UserRepositoryInterface $repository, int $id = 0): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.cancel_account'))
            ->action(apiUrl('user.account.cancel'))
            ->secondAction('cancelAccount/DONE')
            ->confirm([
                'title'   => __p('core::phrase.are_you_sure'),
                'message' => __p('core::phrase.action_cant_be_undone'),
            ])
            ->asPost();
    }

    public function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::choice('reason_id')
                    ->label(__p('user::phrase.reason_for_leave'))
                    ->options($this->getReasonOptions()),
                Builder::textArea('feedback')
                    ->label(__p('user::phrase.please_explain'))
                    ->asMultiLine(),
            );
        $this->handlePasswordField($this->addBasic());
    }

    /**
     * @return array<int, mixed>
     */
    protected function getReasonOptions(): array
    {
        $reasons = resolve(CancelReasonAdminRepositoryInterface::class)->getReasonsForForm($this->resource);

        return $reasons->map(function (CancelReason $reason) {
            return [
                'label' => $reason->title,
                'value' => $reason->entityId(),
            ];
        })->values()->toArray();
    }
    protected function handlePasswordField(Section $basic): void
    {
        if ($this->resource->password) {
            $basic->addField(
                Builder::password('password')
                    ->label(__p('user::phrase.password'))
                    ->required()
                    ->yup(
                        Yup::string()->required()
                    ),
            );
        }
    }
}
