<?php

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawRequest\Admin;

use MetaFox\EMoney\Policies\WithdrawRequestPolicy;
use MetaFox\EMoney\Repositories\WithdrawRequestRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\EMoney\Models\WithdrawRequest as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class DenyWithdrawRequestForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class DenyWithdrawRequestForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('ewallet::admin.deny_request'))
            ->action('admincp/emoney/request/deny')
            ->asPost()
            ->setValue([
                'id' => $this->resource->entityId(),
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::textArea('reason')
                    ->required()
                    ->label(__p('ewallet::admin.please_give_the_reason_why_you_deny_this_request'))
                    ->yup(
                        Yup::string()
                            ->required(__p('ewallet::validation.reason_is_required')),
                    ),
            );

        $this->addFooter()->addFields(
            Builder::submit()
                ->disableWhenClean()
                ->label(__p('core::web.ok')),
            Builder::cancelButton(),
        );
    }

    public function boot(int $id): void
    {
        $this->resource = resolve(WithdrawRequestRepositoryInterface::class)->find($id);

        $context = user();

        policy_authorize(WithdrawRequestPolicy::class, 'deny', $context, $this->resource);
    }
}
