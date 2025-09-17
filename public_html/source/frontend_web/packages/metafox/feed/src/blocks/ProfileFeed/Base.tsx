import { useGlobal } from '@metafox/framework';
import React from 'react';
import { BlockContent, Block } from '@metafox/layout';

export default function ProfileFeed(props) {
  const { jsxBackend } = useGlobal();
  const { item } = props;

  if (item?.profile_settings?.profile_view_profile === false) return null;

  const FeedContainer = jsxBackend.get('feed.ui.FeedContainer');

  return (
    <Block>
      <BlockContent>
        <FeedContainer {...props} />
      </BlockContent>
    </Block>
  );
}
