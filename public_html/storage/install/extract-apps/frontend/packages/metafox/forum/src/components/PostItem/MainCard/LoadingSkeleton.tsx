/**
 * @type: skeleton
 * name: forum_post.itemView.mainCard.skeleton
 */
import { ItemMedia, ItemText, ItemView, ItemTitle } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <ItemMedia>
        <Skeleton variant="circular" width={48} height={48} />
      </ItemMedia>
      <ItemText>
        <div>
          <Skeleton width={160} />
        </div>
        <ItemTitle>
          <Skeleton width={'100%'} />
        </ItemTitle>
        <div>
          <Skeleton width={160} />
        </div>
      </ItemText>
    </ItemView>
  );
}
