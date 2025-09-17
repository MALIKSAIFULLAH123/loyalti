<?php

namespace MetaFox\Mfa\Traits;

/**
 * Trait SetupMobileFormTrait.
 */
trait SetupMobileFormTrait
{
    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type'    => 'formSchema',
                'payload' => [
                    'goBack' => true,
                ],
            ],
        ]);
    }

    protected function prepare(): void
    {
        parent::prepare();

        $this->scrollable();
    }
}
