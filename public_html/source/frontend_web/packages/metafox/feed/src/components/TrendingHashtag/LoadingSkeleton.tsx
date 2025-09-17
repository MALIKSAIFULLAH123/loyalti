/**
 * @type: skeleton
 * name: feed.itemView.trendingHashtag.skeleton
 */

import { ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import * as React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width="60%" />
        </ItemTitle>
        <div>
          <Skeleton width="30%" />
        </div>
      </ItemText>
    </ItemView>
  );
}
