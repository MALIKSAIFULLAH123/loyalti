/**
 * @type: skeleton
 * name: blog.itemView.smallCard.skeleton
 */
import { ImageSkeleton, ItemMedia, ItemText, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <ItemMedia>
        <ImageSkeleton ratio="11" />
      </ItemMedia>
      <ItemText>
        <Skeleton />
        <Skeleton width={80} />
        <Skeleton width={80} />
      </ItemText>
    </ItemView>
  );
}
