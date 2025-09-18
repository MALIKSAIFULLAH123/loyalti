/**
 * @type: skeleton
 * name: forum_thread.itemView.smallCardPost.skeleton
 */
import { ItemText, ItemView, ItemTitle } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
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
