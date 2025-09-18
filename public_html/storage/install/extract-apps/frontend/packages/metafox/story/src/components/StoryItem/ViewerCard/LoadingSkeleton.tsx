/**
 * @type: skeleton
 * name: story.itemView.storyViewer.skeleton
 * chunkName: story
 */

import { ItemMedia, ItemText, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <ItemMedia>
        <Skeleton variant="circular" width={48} height={48} />
      </ItemMedia>
      <ItemText>
        <Skeleton variant="text" width={120} />
      </ItemText>
    </ItemView>
  );
}
