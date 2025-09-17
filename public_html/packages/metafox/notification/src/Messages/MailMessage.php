<?php

namespace MetaFox\Notification\Messages;

use Illuminate\Notifications\Messages\MailMessage as BaseMessage;
use Illuminate\Support\Str;
use MetaFox\Platform\Facades\Settings;

class MailMessage extends BaseMessage
{
    public function __construct()
    {
        $this->viewData['locale'] = null;

        $signature = Settings::get('mail.signature');

        if (!empty($signature)) {
            $this->salutation(Str::of(nl2br($signature))->toHtmlString());
        }
    }

    public function line($line)
    {
        $htmlString = Str::of($line)->toHtmlString();

        return parent::line($htmlString);
    }

    public function locale(?string $locale = null): self
    {
        $this->viewData['locale'] = $locale;

        return $this;
    }

    public function variables(array $variables): self
    {
        $this->viewData['variables'] = $variables;

        return $this;
    }
}
