/**
 * @type: skeleton
 * name: advertise.itemView.invoiceMobileRecord.skeleton
 * chunkName: advertise
 */

import React from 'react';
import { Skeleton } from '@mui/material';

const SkeletonLoading = () => {
  return <Skeleton width={'100%'} height={'48px'} variant="text" />;
};

export default SkeletonLoading;
