<?php

namespace MetaFox\Sms\Support;

use Illuminate\Support\Arr;

class Message
{
    /**
     * @var string
     */
    private string $content;

    /**
     * @var array<string>
     */
    private array $recipients;

    /**
     * @var string
     */
    private string $url;

    /**
     * getRecipients.
     *
     * @return array<string>
     */
    public function getRecipients(): array
    {
        return Arr::wrap($this->recipients);
    }

    /**
     * setRecipients.
     *
     * @param  string|array<string> $recipients
     * @return self
     */
    public function setRecipients($recipients): self
    {
        $this->recipients = Arr::wrap($recipients);

        return $this;
    }

    /**
     * getContent.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * setContent.
     *
     * @param  string $content
     * @return self
     */
    public function setContent($content): self
    {
        $this->content = strip_tag_content($content);

        return $this;
    }

    /**
     * Get the value of url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the value of url.
     *
     * @param ?string $url
     *
     * @return self
     */
    public function setUrl(?string $url)
    {
        $this->url = $url ?? '';

        return $this;
    }

    public function __toString()
    {
        return sprintf('%s %s', $this->getContent(), $this->getUrl());
    }
}
