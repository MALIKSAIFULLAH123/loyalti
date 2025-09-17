/**
 * @type: skeleton
 * name: photo_album.itemView.profileCard.skeleton
 */
import {
  ImageSkeleton,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <ImageSkeleton ratio="169" borderRadius={0} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" />
          <Skeleton variant="text" width={80} />
        </ItemTitle>
        <Skeleton variant="text" />
      </ItemText>
    </ItemView>
  );
}
