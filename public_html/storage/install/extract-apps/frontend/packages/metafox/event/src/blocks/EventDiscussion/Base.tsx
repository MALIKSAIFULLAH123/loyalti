import { EventDetailViewProps as Props } from '@metafox/event';
import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import React from 'react';

export default function EventDiscussion({ item }: Props) {
  const { jsxBackend } = useGlobal();

  if (!item) return null;

  const Feed = jsxBackend.get<BlockViewProps>('feed.block.eventProfileFeeds');
  const StatusComposer = jsxBackend.get<BlockViewProps>(
    'feed.block.statusComposer'
  );

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        {item.extra.can_create_discussion &&
          StatusComposer &&
          React.createElement(StatusComposer, {
            blockLayout: 'Blocker Discussions Search'
          })}
        {Feed && React.createElement(Feed, {})}
      </BlockContent>
    </Block>
  );
}
