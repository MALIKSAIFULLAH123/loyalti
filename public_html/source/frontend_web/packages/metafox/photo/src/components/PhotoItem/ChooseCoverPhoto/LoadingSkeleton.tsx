/**
 * @type: skeleton
 * name: photo.itemView.choosePhoto.skeleton
 */
import { ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <Skeleton variant="rectangular" sx={{ minHeight: '150px' }} />
    </ItemView>
  );
}
