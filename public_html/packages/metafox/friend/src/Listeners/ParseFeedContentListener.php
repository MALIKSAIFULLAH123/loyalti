<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Friend\Support\Facades\Friend as FriendFacades;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\ContentParser;
use MetaFox\User\Support\Facades\User as UserFacades;

/**
 * Class ParseFeedContentListener.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class ParseFeedContentListener
{
    public const CONTENT_PARSING_REGEX = '/\[user=(\d+)\](.+?)\[\/user\]/u';

    public function __construct(protected TagFriendRepositoryInterface $tagFriendRepository) { }

    /**
     * @param Entity $item
     * @param string $content
     * @param array  $attributeParser
     *
     * @return void
     */
    public function handle(Entity $item, string &$content, array $attributeParser = []): void
    {
        $userIds = UserFacades::getMentions($content);

        if (!count($userIds)) {
            return;
        }

        $users = FriendFacades::getUsersForMention($userIds);

        $collection = $this->tagFriendRepository->getUsersForMention($item, $userIds, 'user');

        Arr::set($attributeParser, 'user_ids_allowed_mention', $collection->keys()->toArray());

        /** @var ContentParser $parserContent */
        $parserContent = resolve(ContentParser::class);

        $parserContent->setUserCollection($users);
        $parserContent->setContent($content);
        $parserContent->setPattern(self::CONTENT_PARSING_REGEX);
        $parserContent->attributeParser($attributeParser);

        $content = $parserContent->parse();
    }
}
