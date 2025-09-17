/**
 * @type: skeleton
 * name: shortcut.itemView.mainCard.skeleton
 * chunkName: sidebarHome
 */
import { ItemMedia, ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

const LoadingSkeleton = ({ wrapAs, wrapProps }) => {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Skeleton variant="avatar" width={40} height={40} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width={'80%'} />
        </ItemTitle>
      </ItemText>
    </ItemView>
  );
};

export default LoadingSkeleton;
