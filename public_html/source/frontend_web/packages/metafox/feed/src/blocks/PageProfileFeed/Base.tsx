import { useGlobal } from '@metafox/framework';
import React from 'react';
import { BlockContent, Block } from '@metafox/layout';

export default function PagesFeed(props) {
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
