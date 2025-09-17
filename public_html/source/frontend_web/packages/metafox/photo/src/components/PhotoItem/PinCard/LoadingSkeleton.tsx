/**
 * @type: skeleton
 * name: photo.itemView.pinCard.skeleton
 */
import { ImageSkeleton, ItemMedia } from '@metafox/ui';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemMedia>
      <ImageSkeleton ratio="169" />
    </ItemMedia>
  );
}
