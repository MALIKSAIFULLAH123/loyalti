/**
 * @type: skeleton
 * name: forum.itemView.sliderCard.skeleton
 */
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return <Skeleton height={200} width="100%" />;
}
