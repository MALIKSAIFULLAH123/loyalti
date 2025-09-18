/**
 * @type: skeleton
 * name: video.itemView.profileCard.skeleton
 */
import { ImageSkeleton, ItemView, ItemMedia, ItemText } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import * as React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <ImageSkeleton ratio="169" borderRadius={0} />
      </ItemMedia>
      <ItemText>
        <Skeleton width={'100%'} />
        <Skeleton width={'100%'} />
        <Skeleton width={160} />
      </ItemText>
    </ItemView>
  );
}
