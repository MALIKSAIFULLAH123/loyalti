import { useGlobal } from '@metafox/framework';
import React from 'react';
import { BlockContent, Block } from '@metafox/layout';

export default function GroupFeed(props) {
  const { jsxBackend } = useGlobal();
  const FeedContainer = jsxBackend.get('feed.ui.FeedContainer');

  return (
    <Block>
      <BlockContent>
        <FeedContainer {...props} />
      </BlockContent>
    </Block>
  );
}
