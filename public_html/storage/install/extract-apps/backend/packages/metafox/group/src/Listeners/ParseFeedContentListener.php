<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Support\Facades\Group;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\ContentParser;

/**
 * Class ParseFeedContentListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ParseFeedContentListener
{
    public const CONTENT_PARSING_REGEX = '/\[group=(\d+)\](.+?)\[\/group\]/u';
    /**
     * @param  Entity $item
     * @param  string $content
     * @param  array  $attributeParser
     * @return void
     */
    public function handle(Entity $item, string &$content, array $attributeParser = []): void
    {
        $groupIds = Group::getMentions($content);

        if (count($groupIds)) {
            $groups = Group::getGroupsForMention($groupIds);

            /** @var ContentParser $parserContent */
            $parserContent = resolve(ContentParser::class);

            $parserContent->setUserCollection($groups);
            $parserContent->setContent($content);
            $parserContent->setPattern(self::CONTENT_PARSING_REGEX);
            $parserContent->attributeParser($attributeParser);

            $content = $parserContent->parse();
        }
    }
}
