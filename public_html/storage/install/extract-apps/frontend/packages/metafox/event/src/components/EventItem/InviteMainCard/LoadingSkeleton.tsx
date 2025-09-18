/**
 * @type: skeleton
 * name: event.itemView.inviteMainCard.skeleton
 */

import { ImageSkeleton, ItemMedia, ItemText, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="">
      <ItemMedia>
        <ImageSkeleton ratio="169" />
      </ItemMedia>
      <ItemText>
        <Skeleton width={'100%'} />
        <Skeleton width={'100%'} />
        <Skeleton width={160} />
        <Skeleton width={160} />
      </ItemText>
    </ItemView>
  );
}
