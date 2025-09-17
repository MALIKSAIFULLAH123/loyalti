<?php

namespace MetaFox\User\Http\Resources\v1\User;

use MetaFox\Form\Builder;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Section;
use MetaFox\User\Models\CancelReason;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Policies\UserPolicy;
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
class AccountCancelForm extends AbstractForm
{
    public function boot(UserRepositoryInterface $repository, int $id = 0): void
    {
        $this->resource = $repository->find($id);

        if ($this->resource->hasSuperAdminRole()) {
            abort(401, __p('user::phrase.password_is_not_correct'));
        }

        policy_authorize(UserPolicy::class, 'delete', $this->resource, $this->resource);
    }

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.cancel_account'))
            ->action(apiUrl('user.account.cancel'))
            ->secondAction('user/cancel_account_done')
            ->setValue([
                'password' => '',
            ])
            ->asPost();
    }

    public function initialize(): void
    {
        $this->addBasic()->addFields(
            Builder::choice('reason_id')
                ->label(__p('user::phrase.reason_for_leave'))
                ->options($this->getReasonOptions()),
            Builder::textArea('feedback')
                ->label(__p('user::phrase.please_explain')),
        );
        $this->handlePasswordField($this->addBasic());
        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.submit'))
                    ->color('error')
                    ->confirmation([
                        'title'   => __p('core::phrase.confirm'),
                        'message' => __p('core::phrase.action_cant_be_undone'),
                    ]),
                Builder::cancelButton(),
            );
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
                    ->shrink()
                    ->required()
                    ->autoComplete('new-password')
                    ->yup(
                        Yup::string()->required()
                    ),
            );
        }
    }
}
