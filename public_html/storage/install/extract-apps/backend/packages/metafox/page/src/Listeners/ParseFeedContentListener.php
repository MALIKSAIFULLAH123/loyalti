<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Support\Facade\Page;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\ContentParser;

/**
 * Class ParseFeedContentListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ParseFeedContentListener
{
    public const CONTENT_PARSING_REGEX = '/\[page=(\d+)\](.+?)\[\/page\]/u';
    /**
     * @param  Entity $item
     * @param  string $content
     * @param  array  $attributeParser
     * @return void
     */
    public function handle(Entity $item, string &$content, array $attributeParser = []): void
    {
        $pageIds = Page::getMentions($content);

        if (!count($pageIds)) {
            return;
        }

        $pages = Page::getPagesForMention($pageIds);

        /** @var ContentParser $parserContent */
        $parserContent = resolve(ContentParser::class);

        $parserContent->setUserCollection($pages);
        $parserContent->setContent($content);
        $parserContent->setPattern(self::CONTENT_PARSING_REGEX);
        $parserContent->attributeParser($attributeParser);

        $content = $parserContent->parse();
    }
}
