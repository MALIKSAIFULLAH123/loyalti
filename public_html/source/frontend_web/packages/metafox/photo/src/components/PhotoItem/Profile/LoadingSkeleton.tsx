/**
 * @type: skeleton
 * name: photo.itemView.profileCard.skeleton
 */
import { ImageSkeleton, ItemMedia, ItemView } from '@metafox/ui';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <ImageSkeleton ratio="169" />
      </ItemMedia>
    </ItemView>
  );
}
