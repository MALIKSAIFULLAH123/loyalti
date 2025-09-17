/**
 * @type: skeleton
 * name: friend.ui.pickItem.skeleton
 */
import { ItemMedia, ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <ItemMedia>
        <Skeleton width={40} height={40} variant="avatar" />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width="30%" />
        </ItemTitle>
      </ItemText>
    </ItemView>
  );
}

export default LoadingSkeleton;
