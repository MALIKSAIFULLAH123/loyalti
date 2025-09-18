/**
 * @type: skeleton
 * name: announcement.itemView.mainCard.skeleton
 * chunkName: boot_homepage
 */

import { ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <Skeleton variant="rounded" height={80} />
    </ItemView>
  );
}
