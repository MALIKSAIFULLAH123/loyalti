<?php

namespace MetaFox\Core\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Mail\Mailable as BaseMailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;

/**
 * stub: packages/mails/mail.stub.
 */

/**
 * Class Mailable.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class Mailable extends BaseMailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $markdown = 'notifications::email';

    public $level      = 'info';
    public $greeting;
    public $salutation;
    public $actionText;
    public $actionUrl;
    public $introLines = [];
    public $outroLines = [];
    public $user;
    public $variables  = [];

    public function __construct(array $params = [])
    {
        $this->from(Settings::get('mail.from.address'), Settings::get('mail.from.name'));

        $signature   = Settings::get('mail.signature');
        $this->theme = 'metafox';

        if (!empty($signature)) {
            $this->salutation(Str::of(nl2br($signature))->toHtmlString());
        }

        $this->initParams($params);
        $this->initVariables();
    }

    /**
     * @param User|null $user
     *
     * @return $this
     */
    public function setUser(User $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): User|null
    {
        return $this->user;
    }

    /**
     * @param array $variables
     *
     * @return $this
     */
    public function setVariables(array $variables = []): self
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param string $level
     *
     * @return $this
     */
    public function level(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @param string $greeting
     *
     * @return $this
     */
    public function greeting(string $greeting): self
    {
        $this->greeting = $greeting;

        return $this;
    }

    /**
     * @param string $salutation
     *
     * @return $this
     */
    public function salutation(string $salutation): self
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * @param string $line
     *
     * @return $this
     */
    public function line(string $line): self
    {
        $htmlString = Str::of($line)->toHtmlString();

        $this->introLines[] = $this->formatLine($htmlString);

        return $this;
    }

    /**
     * @param string $text
     * @param string $url
     *
     * @return $this
     */
    public function action(string $text, string $url): self
    {
        $this->actionText = $text;
        $this->actionUrl  = $url;

        return $this;
    }

    public function build()
    {
        $this->viewData = $this->getViewData();

        return $this;
    }

    /**
     * @return $this
     */
    public function success(): self
    {
        $this->level = 'success';

        return $this;
    }

    /**
     * @return $this
     */
    public function error(): self
    {
        $this->level = 'error';

        return $this;
    }

    /**
     * @return $this
     */
    public function info(): self
    {
        $this->level = 'info';

        return $this;
    }

    private function getViewData(): array
    {
        return [
            'level'                => $this->level,
            'locale'               => $this->locale,
            'subject'              => $this->subject,
            'greeting'             => $this->greeting,
            'html'                 => $this->html,
            'salutation'           => $this->salutation,
            'introLines'           => $this->introLines,
            'outroLines'           => $this->outroLines,
            'actionText'           => $this->actionText,
            'actionUrl'            => $this->actionUrl,
            'variables'            => $this->variables,
            'displayableActionUrl' => str_replace(['mailto:', 'tel:'], '', $this->actionUrl ?? ''),
        ];
    }

    private function formatLine($line)
    {
        if ($line instanceof Htmlable) {
            return $line;
        }

        if (is_array($line)) {
            return implode(' ', array_map('trim', $line));
        }

        return trim(implode(' ', array_map('trim', preg_split('/\\r\\n|\\r|\\n/', $line ?? ''))));
    }

    private function initParams(array $params): void
    {
        $configs = array_merge(array_keys($this->getViewData()), ['user']);

        foreach ($params as $key => $value) {
            if ($key === 'line') {
                $this->line($value);

                continue;
            }

            if (!in_array($key, $configs)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    private function initVariables(): void
    {
        if (!empty($this->variables)) {
            return;
        }

        $this->setVariables([
            'recipient_full_name'  => $this->user?->full_name,
            'recipient_first_name' => $this->user?->first_name,
            'recipient_last_name'  => $this->user?->last_name,
        ]);
    }
}
