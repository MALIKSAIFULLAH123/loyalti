<?php

namespace MetaFox\Captcha\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Platform\Facades\Settings;

abstract class AbstractCaptchaField extends AbstractField
{
    /**
     * @return string
     */
    abstract public function toType(): string;

    /**
     * @return array
     */
    abstract public function toConfiguration(): array;

    /**
     * @return string
     */
    abstract public function toTokenAction(): string;

    public function prepare(): void
    {
        $action = $this->getAttribute('actionName');

        $disabled = false;

        if (null !== $action) {
            $ruleKey  = 'captcha.rules.' . $action;

            $disabled =  !Settings::get($ruleKey);
        }

        parent::prepare();

        $this->disabled($disabled);

        if (!$disabled) {
            $configuration = array_merge($this->toConfiguration(), [
                'action_name' => $this->getAttribute('action_name'),
            ]);

            $this->form?->setCaptcha([
                'captcha_type'   => $this->toType(),
                'captcha_action' => [
                    'type'    => $this->toTokenAction(),
                    'payload' => $configuration,
                ],
            ]);
        }
    }

    /**
     * @param  string|null $action
     * @return $this
     */
    public function actionName(?string $action): self
    {
        return $this->setAttribute('action_name', $action);
    }
}
