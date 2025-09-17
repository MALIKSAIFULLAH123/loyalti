<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class ContentParser.
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class ContentParser
{
    public const LINK_CONTENT       = "<a href='%s' target='%s' id='%d' type='%s'>%s</a>";
    public const IGNORE_TAG_CONTENT = '<span class="ignore-tag">%s</span>';
    protected string $pattern;
    protected string $content;
    protected string $target;
    protected bool   $parseUrl;
    protected bool   $parseUserFullLink;
    protected array  $allowedMention;

    public function getAllowedMention(): array
    {
        return $this->allowedMention;
    }

    public function setAllowedMention(array $allowedMention): void
    {
        $this->allowedMention = $allowedMention;
    }

    /**
     * @return bool
     */
    public function isParseUrl(): bool
    {
        return $this->parseUrl;
    }

    /**
     * @param bool $parseUrl
     */
    public function setParseUrl(bool $parseUrl = true): void
    {
        $this->parseUrl = $parseUrl;
    }

    /**
     * @return bool
     */
    public function isParseUserFullLink(): bool
    {
        return $this->parseUserFullLink;
    }

    /**
     * @param bool $parseUserFullLink
     */
    public function setParseUserFullLink(bool $parseUserFullLink = false): void
    {
        $this->parseUserFullLink = $parseUserFullLink;
    }

    protected Collection $userCollection;

    /**
     * @return Collection
     */
    public function getUserCollection(): Collection
    {
        return $this->userCollection;
    }

    /**
     * @param Collection $userCollection
     */
    public function setUserCollection(Collection $userCollection): void
    {
        $this->userCollection = $userCollection;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target = '_self'): void
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     */
    public function setPattern(string $pattern): void
    {
        $this->pattern = $pattern;
    }

    public function attributeParser(array $attribute): void
    {
        $this->setTarget(Arr::get($attribute, 'target', '_self'));
        $this->setParseUrl(Arr::get($attribute, 'parse_url', true));
        $this->setParseUserFullLink(Arr::get($attribute, 'parse_full_link', false));
        $this->setAllowedMention($this->getUserCollection()->keys()->toArray());

        if (Arr::has($attribute, 'user_ids_allowed_mention')) {
            $this->setAllowedMention(Arr::get($attribute, 'user_ids_allowed_mention'));
        }
    }

    public function parse(): ?string
    {
        return preg_replace_callback($this->getPattern(), function ($params) {
            [, $userId, $oldName] = $params;

            if (!$this->isParseUrl()) {
                return $oldName;
            }

            $model = $this->getUserCollection()->get($userId);

            if (!$model instanceof User) {
                return sprintf(self::IGNORE_TAG_CONTENT, $oldName);
            }

            $name = $oldName;

            if (!is_string($oldName) || MetaFoxConstant::EMPTY_STRING === $oldName) {
                $name = $model->toTitle();
            }

            if (!in_array($model->entityId(), $this->getAllowedMention())) {
                return sprintf(self::IGNORE_TAG_CONTENT, $name);
            }

            $href = $this->isParseUserFullLink() ? $model->toUrl() : $model->toLink();

            if ($href && $model->isTaggingAllowed()) {
                return sprintf(self::LINK_CONTENT, $href, $this->getTarget(), $model->entityId(), $model->entityType(), $name);
            }

            return sprintf(self::IGNORE_TAG_CONTENT, $name);
        }, $this->getContent());
    }
}
