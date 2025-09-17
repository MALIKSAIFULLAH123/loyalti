/**
 * @type: skeleton
 * name: featured.itemView.invoice.skeleton
 * chunkName: featured
 */

import React from 'react';
import { Skeleton } from '@mui/material';

const SkeletonLoading = () => {
  return <Skeleton width={'100%'} height={'48px'} variant="text" />;
};

export default SkeletonLoading;
