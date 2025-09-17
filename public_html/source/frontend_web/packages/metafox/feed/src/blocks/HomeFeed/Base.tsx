import React from 'react';
import { useGlobal } from '@metafox/framework';
import { BlockContent, Block } from '@metafox/layout';

export default function HomeFeed(props) {
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
