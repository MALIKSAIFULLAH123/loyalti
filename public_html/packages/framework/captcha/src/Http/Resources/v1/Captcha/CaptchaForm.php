<?php

namespace MetaFox\Captcha\Http\Resources\v1\Captcha;

use Illuminate\Http\Request;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

class CaptchaForm extends AbstractForm
{
    protected ?string $action = null;

    protected bool $autoFocus = false;

    protected string $resolution = 'web';

    public function boot(Request $request): void
    {
        $this->action = $request->get('action_name');

        $this->autoFocus = $request->get('auto_focus', false);

        $this->resolution = $request->get('resolution', 'web');
    }

    protected function prepare(): void
    {
        $this->title(__p('captcha::phrase.captcha'))
            ->asPost()
            ->action('')
            ->setValue([
                'action_name' => $this->action,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $captchaField = Captcha::getFormField($this->action, $this->resolution, false, 'captcha', false);

        $captchaField->autoFocus($this->autoFocus);

        $basic->addFields(
            $captchaField,
            Builder::hidden('action_name'),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.submit')),
                Builder::cancelButton()
                    ->noConfirmation()
            );
    }
}
