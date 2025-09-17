<?php

namespace MetaFox\Ban\Http\Resources\v1\BanRule\Admin;

use MetaFox\Ban\Contracts\TypeHandlerInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Section;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreBanRuleForm.
 * @ignore
 * @codeCoverageIgnore
 */
class StoreBanRuleForm extends AbstractForm
{
    protected TypeHandlerInterface $handler;

    protected function prepare(): void
    {
        $this->title($this->handler->getFormTitle())
            ->action(apiUrl('admin.ban.ban-rule.store'))
            ->asPost()
            ->setValue([
                'type' => $this->handler->getType(),
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->addFields(...$this->handler->getFilterFields());

        $this->addBanUserFields($basic);

        $this->addDefaultFooter();
    }

    protected function isSupportBanUser(): bool
    {
        return $this->handler->isSupportBanUser();
    }

    protected function addBanUserFields(Section $basic): void
    {
        if (!$this->isSupportBanUser()) {
            return;
        }

        $basic->addFields(
            ...$this->handler->getBanUserFields()
        );
    }

    public function setHandler(TypeHandlerInterface $handler): self
    {
        $this->handler = $handler;

        return $this;
    }
}
